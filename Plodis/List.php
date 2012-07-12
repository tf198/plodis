<?php
class Plodis_List extends Plodis_Group {
	
	/**
	 * How often in seconds to poll for blocking operations
	 * @var int
	 */
	public static $poll_frequency = 0.1;
	
	protected $sql = array(
		'lpush_index'	=> 'SELECT MIN(list_index) from plodis',
		'llen' 			=> 'SELECT COUNT(id) FROM plodis WHERE key=?',
		'l_forward'		=> 'SELECT id, item FROM plodis WHERE key=? ORDER BY list_index, id LIMIT ? OFFSET ?',
		'l_reverse'		=> 'SELECT id, item FROM plodis WHERE key=? ORDER BY list_index DESC, id DESC LIMIT ? OFFSET ?',
		'l_insert' 		=> 'INSERT INTO plodis (key, item, list_index) VALUES (?, ?, ?)',
		'lset'			=> 'UPDATE plodis SET item=? WHERE id=?',
		'l_key_val'		=> 'SELECT id, list_index FROM plodis WHERE key=? AND item=?',
		'l_shift'		=> 'UPDATE plodis SET list_index = list_index+1 WHERE id>? AND list_index>=?', // creates a space after the target item
		'lrem_forward'	=> 'DELETE FROM plodis WHERE id IN (SELECT id FROM plodis WHERE key=? AND item=? ORDER BY list_index, id LIMIT ?)',
		'lrem_reverse'	=> 'DELETE FROM plodis WHERE id IN (SELECT id FROM plodis WHERE key=? AND item=? ORDER BY list_index DESC, id DESC LIMIT ?)',
		'list_del' 		=> 'DELETE FROM plodis WHERE id=?',
	);
	
	function llen($key) {
		$row = $this->fetchOne('llen', array($key));
		return (int) $row[0];
	}
	
	function lindex($key, $index) {
		$row = $this->_lindex($key, $index);
		return ($row) ? $row[1] : null;
	}
	
	function lset($key, $index, $value) {
		$row = $this->_lindex($key, $index);
		if(!$row) throw new RuntimeException("Index out of range: {$index}");
	
		$c = $this->executeStmt('lset', array($value, $row[0]));
		if ($c != 1) throw new RuntimeException("Failed to update list value");
	}
	
	private function _lindex($key, $index) {
		$s = 'l_forward';
		if($index < 0) {
			$s = 'l_reverse';
			$index = -$index - 1;
		}
		$row = $this->fetchOne($s, array($key, 1, $index));
		return $row;
	}
	
	function ltrim($key, $start, $stop) {
		throw new RuntimeException('Not implemented');
	}
	
	function lrange($key, $start, $stop) {
		$s = 'l_forward';
		$flip = false;
		$slice = false;
	
		if($start < 0 && $stop < 0) {
			$s = 'l_reverse';
			$start = -$start - 2;
			$stop  = -$stop;
			$flip = true;
		}
	
		$offset = $start;
		$limit = ($stop < 0) ? -1 : $stop-$start+1;
	
		//fprintf(STDERR, "%d %d -> LIMIT %d OFFSET %d\n", $start, $stop, $limit, $offset);
	
		$stmt = $this->getStmt($s);
		$stmt->execute(array($key, $limit, $offset));
	
		$data = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
	
		// reverse queries
		if($flip) {
			$data = array_reverse($data);
		}
	
		// need to slice negative stops
		if($stop < -1) {
			$data = array_slice($data, 0, $stop+1);
		}
	
		return $data;
	}
	
	function linsert($key, $pos, $pivot, $value) {
		// make atomic
		$this->proxy->conn->beginTransaction();
	
		$item = $this->fetchOne('l_key_val', array($key, $pivot));
		if(!$item) {
			$this->proxy->conn->commit();
			return -1;
		}
	
		if($pos == 'before') $item[0]--;
	
		$this->fetchOne('l_shift', $item);
		$this->fetchOne('l_insert', array($key, $value, $item[1]));
	
		$this->proxy->conn->commit();
	
		if(self::$strict) {
			return $this->llen($key);
		}
	}
	
	function lrem($key, $count, $value) {
		$s = 'lrem_forward';
		if($count < 0) {
			$count = -$count;
			$s = 'lrem_reverse';
		}
		if($count == 0) $count = -1;
	
		return $this->executeStmt($s, array($key, $value, $count));
	}
	
	function rpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
	
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, 0));
		}
	
		return $this->llen($key);
	}
	
	function lpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
	
		// have to transaction this
		$this->proxy->conn->beginTransaction();
	
		// find the lowest id
		$id = $this->fetchOne('lpush_index');
		$id = ($id) ? $id[0] - 1 : -1;
	
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, $id--));
		}
	
		$this->proxy->conn->commit();
	
		return $this->llen($key);
	}
	
	function rpoplpush($source, $destination) {
		throw new RuntimeException('Not implemented');
	}
	
	function lpop($key) {
		return $this->_pop($key, 'l_forward');
	}
	
	function rpop($key) {
		return $this->_pop($key, 'l_reverse');
	}
	
	function blpop($key, $timeout) {
		return $this->_pop($key, 'l_forward', true);
	}
	
	function brpop($key, $timeout) {
		return $this->_pop($key, 'l_reverse', true);
	}
	
	private function _pop($key, $type, $wait=false) {
		$pop = $this->getStmt($type);
		$del = $this->getStmt('list_del');
	
		// do everything in an optomised lock
		$us = self::$poll_frequency * 1000000;
	
		while(true) {
			$this->proxy->conn->beginTransaction();
			$pop->execute(array($key, 1, 0));
			$result = $pop->fetch(PDO::FETCH_NUM);
			$pop->closeCursor();
			if($result) {
				try {
					$del->execute(array($result[0]));
				} catch(PDOException $e) {
					var_dump($e->getMessage());
					$result = null;
				}
			}
			$this->proxy->conn->commit();
			if(!$result && $wait) {
				usleep($us);
			} else {
				break;
			}
		}
	
		return ($result) ? $result[1] : null;
	}
}
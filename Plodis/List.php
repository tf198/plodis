<?php
require_once PLODIS_BASE . "/interfaces/Redis_List_2_6_0.php";

#define REDIS_1_0_0
#define REDIS_1_2_0
#define REDIS_2_0_0

class Plodis_List extends Plodis_Group implements Redis_List_2_6_0 {
	
	/**
	 * How often in seconds to poll for blocking operations
	 * @var int
	 */
	public static $poll_frequency = 0.1;
	
	/**
	 * Whether to return counts
	 * @var boolean
	 */
	public static $return_counts = false;
	
	protected $sql = array(
		'lpush_index'	=> 'SELECT MIN(list_index) FROM <DB>',
		'llen' 			=> 'SELECT COUNT(id) FROM <DB> WHERE key=?',
		'l_forward'		=> 'SELECT id, item FROM <DB> WHERE key=? ORDER BY list_index, id LIMIT ? OFFSET ?',
		'l_reverse'		=> 'SELECT id, item FROM <DB> WHERE key=? ORDER BY list_index DESC, id DESC LIMIT ? OFFSET ?',
		'l_insert' 		=> 'INSERT INTO <DB> (key, item, list_index) VALUES (?, ?, ?)',
		'lset'			=> 'UPDATE <DB> SET item=? WHERE id=?',
		'l_key_val'		=> 'SELECT id, list_index FROM <DB> WHERE key=? AND item=?',
		'l_shift'		=> 'UPDATE <DB> SET list_index = list_index+1 WHERE id>? AND list_index>=?', // creates a space after the target item
		'lrem_forward'	=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? AND item=? ORDER BY list_index, id LIMIT ?)',
		'lrem_reverse'	=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? AND item=? ORDER BY list_index DESC, id DESC LIMIT ?)',
		'list_del' 		=> 'DELETE FROM <DB> WHERE id=?',
	);
	
	#ifdef REDIS_1_0_0
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
		#$this->proxy->db->lock();
		
		#$id = $this->fetchOne('rpush_index');
		#$id = ($id) ? $id[0] : 0;
		
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, 0));
		}
		
		#$this->proxy->db->unlock();
	
		return self::$return_counts ? $this->llen($key) : -1;
	}
	
	function lpush($key, $values) {
		// have to transaction this
		$this->proxy->db->lock();
	
		// find the lowest id
		$id = $this->fetchOne('lpush_index');
		$id = ($id) ? $id[0] - 1 : -1;
	
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, $id--));
		}
	
		$this->proxy->db->unlock();
	
		return self::$return_counts ? $this->llen($key) : -1;
	}
	
	function lpop($key) {
		return $this->_pop($key, 'l_forward');
	}
	
	function rpop($key) {
		return $this->_pop($key, 'l_reverse');
	}
	
	private $_pop_stmt;
	private $_del_stmt;
	
	private function _pop($key, $type, $timeout=-1) {
	
		$us = self::$poll_frequency * 1000000;
	
		// cache our statments locally as this one is a pig :)
		if(!isset($this->_pop_stmt)) {
			$this->_pop_stmt = $this->proxy->db->cachedStmt($this->sql[$type]);
			$this->_del_stmt = $this->proxy->db->cachedStmt($this->sql['list_del']);
		}
		
		while(true) {
			$this->proxy->db->lock();
			$this->_pop_stmt->execute(array($key, 1, 0));
			$result = $this->_pop_stmt->fetch(PDO::FETCH_NUM);
			if($result) {
				try {
					$this->_del_stmt->execute(array($result[0]));
				} catch(PDOException $e) {
					$this->proxy->log("Unable to remove list item: " . $e->getMessage(), LOG_WARNING);
					$result = null;
				}
			}
			$this->proxy->db->unlock();
			
			if($timeout < 0) break;
			if($result) break;
			
			usleep($us);
			
			if($timeout == 0) continue;
			$timeout -= self::$poll_frequency;
			if($timeout == 0) break; // make sure our descending timer doesn't become indefinate
		}
	
		return ($result) ? $result[1] : null;
	}
	#endif
	
	#ifdef REDIS_1_2_0
	function rpoplpush($source, $destination) {
		throw new RuntimeException('Not implemented');
	}
	#endif
	
	#ifdef REDIS_2_0_0
	function blpop($key, $timeout) {
		return $this->_pop($key, 'l_forward', $timeout);
	}
	
	function brpop($key, $timeout) {
		return $this->_pop($key, 'l_reverse', $timeout);
	}
	#endif
	
	#ifdef REDIS_2_2_0
	function linsert($key, $pos, $pivot, $value) {
		// make atomic
		$this->proxy->db->lock();
	
		$item = $this->fetchOne('l_key_val', array($key, $pivot));
		if(!$item) {
			$this->proxy->db->unlock(true);
			return -1;
		}
	
		if($pos == 'before') $item[0]--;
	
		$this->fetchOne('l_shift', $item);
		$this->fetchOne('l_insert', array($key, $value, $item[1]));
	
		$this->proxy->db->unlock();
	
		return self::$return_counts ? $this->llen($key) : -1;
	}
	
	function brpoplpush($source, $dest, $timeout) {
		throw new PlodisNotImplementedError();
	}
	
	function lpushx($key, $value) {
		throw new PlodisNotImplementedError();
	}
	
	function rpushx($key, $value) {
		throw new PlodisNotImplementedError();
	}
	#endif
}

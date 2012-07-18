<?php
require_once PLODIS_BASE . "/interfaces/Redis_List_2_6_0.php";

#define REDIS_1_0_0
#define REDIS_1_2_0
#define REDIS_2_0_0

class Plodis_List extends Plodis_Group implements Redis_List_2_6_0 {
	
	protected $sql = array(
		'lpush_index'	=> 'SELECT MIN(weight) FROM <DB> WHERE key=?',
		'llen' 			=> 'SELECT COUNT(*) FROM <DB> WHERE key=?',
		'l_forward'		=> 'SELECT id, item, type FROM <DB> WHERE key=? ORDER BY weight, id LIMIT ? OFFSET ?',
		'l_reverse'		=> 'SELECT id, item, type FROM <DB> WHERE key=? ORDER BY weight DESC, id DESC LIMIT ? OFFSET ?',
		'l_insert' 		=> 'INSERT INTO <DB> (key, type, item, weight) VALUES (?, ?, ?, ?)',
		'lset'			=> 'UPDATE <DB> SET item=? WHERE id=?',
		'l_key_val'		=> 'SELECT id, weight, type FROM <DB> WHERE key=? AND item=?',
		'l_shift'		=> 'UPDATE <DB> SET weight = weight-1 WHERE key=? AND id<=? OR weight<?', // creates a space before the target item
		'lrem_forward'	=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? AND item=? ORDER BY weight, id LIMIT ?)',
		'lrem_reverse'	=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? AND item=? ORDER BY weight DESC, id DESC LIMIT ?)',
		'list_del' 		=> 'DELETE FROM <DB> WHERE id=?',
		'ltrim_l'		=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? ORDER BY weight, id LIMIT ?)',
		'ltrim_r'		=> 'DELETE FROM <DB> WHERE id IN (SELECT id FROM <DB> WHERE key=? ORDER BY weight DESC, id DESC LIMIT ?)'
	);
	
	#ifdef REDIS_1_0_0
	function llen($key, $verified=false) {
		$this->proxy->generic->gc();
		// this is called by push ops so cache the verification if possible
		if(!$verified) $this->proxy->generic->verify($key, 'list');
		
		$row = $this->fetchOne('llen', array($key));
		return (int) $row[0];
	}
	
	function lindex($key, $index) {
		$row = $this->_lindex($key, $index);
		return ($row) ? $row[1] : null;
	}
	
	function lset($key, $index, $value) {
		$this->proxy->db->lock();
		$row = $this->_lindex($key, $index);
		if(!$row) {
			$this->proxy->db->unlock();
			throw new RuntimeException("Index out of range: {$index}");
		}
	
		$c = $this->executeStmt('lset', array($value, $row[0]));
		$this->proxy->db->unlock(); // dont have to roll back anyway
		if ($c != 1) throw new RuntimeException("Failed to update list value");
	}
	
	private function _lindex($key, $index) {
		$this->proxy->generic->gc();
		$s = 'l_forward';
		if($index < 0) {
			$s = 'l_reverse';
			$index = -$index - 1;
		}
		$row = $this->fetchOne($s, array($key, 1, $index));
		if($row && $row[2] != Plodis::TYPE_LIST) throw new PlodisIncorrectKeyType;
		return $row;
	}
	
	function ltrim($key, $start, $end) {
		#$this->proxy->log("Starting {$start}, {$end}", LOG_WARNING);
		$this->proxy->generic->gc();
		$this->proxy->db->lock();
		if($start > 0) {
			$c = $this->executeStmt('ltrim_l', array($key, $start));
			#$this->proxy->log("Removed {$c} elements from start", LOG_WARNING);
			if($end > 0) $end -= $start;
			$start = 0;
		} // $start is 0 or negative
		if($end < -1) {
			$c = $this->executeStmt('ltrim_r', array($key, -$end-1));
			#$this->proxy->log("Removed {$c} elements from end", LOG_WARNING);
			if($start < 0) $start -= $end;
			$end = 0;
		} // $end is 0 or positive
		
		if($start == 0 && $end <= 0) {
			#$this->proxy->log("No further removals required", LOG_WARNING);
			$this->proxy->db->unlock();
			return;
		}
		
		$s = 'ltrim_r';
		if($start < 0 && $end <= 0) {
			$end = -$start;
			$start = 0;
			$s = 'ltrim_l';
		}
		if($start != 0) {
			$this->proxy->db->unlock(true);
			throw new RuntimeException("Unabled to proceed");
		}
		$c = $this->llen($key);
		#$this->proxy->log("C: {$c}, end: $end", LOG_WARNING);
		$c = $this->executeStmt($s, array($key, $c-$end));
		$this->proxy->db->unlock();
		#$this->proxy->log("Removed {$c} elements from {$s}", LOG_WARNING);
	}
	
	function lrange($key, $start, $stop) {
		$this->proxy->generic->gc();
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
		$this->proxy->generic->gc();
		$s = 'lrem_forward';
		if($count < 0) {
			$count = -$count;
			$s = 'lrem_reverse';
		}
		if($count == 0) $count = -1;
	
		return $this->executeStmt($s, array($key, $value, $count));
	}
	
	function rpush($key, $values) {
		$this->proxy->db->lock();
		$this->proxy->generic->verify($key, 'list', 1);
		
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, Plodis::TYPE_LIST, $value, 0));
		}
	
		$result = $this->proxy->options['return_counts'] ? $this->llen($key) : -1;
		$this->proxy->db->unlock();
		return $result;
	}
	
	function lpush($key, $values) {
		$this->proxy->db->lock();
		$this->proxy->generic->verify($key, 'list', 1);
		
		// find the lowest id
		$row = $this->fetchOne('lpush_index', array($key));
		
		$id = ($row) ? $row[0] - 1 : -1;
	
		$stmt = $this->getStmt('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, Plodis::TYPE_LIST, $value, --$id));
		}
	
		$result = $this->proxy->options['return_counts'] ? $this->llen($key) : -1;
		$this->proxy->db->unlock();
		return $result;
	}
	
	function lpop($key) {
		return $this->_pop($key, 'l_forward');
	}
	
	function rpop($key) {
		return $this->_pop($key, 'l_reverse');
	}
	
	private function _pop($key, $type, $timeout=-1) {
		$this->proxy->generic->gc();
		$freq = $this->proxy->options['poll_frequency'];
		$us = $freq * 1000000; // microseconds
	
		$pop = $this->proxy->db->cachedStmt($this->sql[$type]);
		$del = $this->proxy->db->cachedStmt($this->sql['list_del']);
		
		while(true) {
			$this->proxy->db->lock();
			$pop->execute(array($key, 1, 0));
			$result = $pop->fetch(PDO::FETCH_NUM);
			if($result) {
				try {
					$del->execute(array($result[0]));
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
			$timeout -= $freq;
			if($timeout == 0) break; // make sure our descending timer doesn't become indefinate
		}
	
		if($result) {
			if($result[2] != Plodis::TYPE_LIST) throw new PlodisIncorrectKeyType;
			return $result[1];
		} else {
			return null;
		}
	}
	#endif
	
	#ifdef REDIS_1_2_0
	function rpoplpush($source, $destination) {
		$this->proxy->db->lock();
		$item = $this->rpop($source);
		if($item !== null) $this->lpush($destination, array($item));
		$this->proxy->db->unlock();
		return $item;
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
	
		$items = $this->fetchAll('l_key_val', array($key, $pivot));
		if(!$items) {
			$this->proxy->db->unlock(true);
			return -1;
		}
		
		if(strtolower($pos) == 'before') {
			$items[0][0]--;
		} else {
			$items = array_reverse($items);
		}
		$this->fetchOne('l_shift', array($key, $items[0][0], $items[0][1]));
		
		// go in at same index as shifted items
		$items[0][1]--;
		$this->fetchOne('l_insert', array($key, Plodis::TYPE_LIST, $value, $items[0][1]));
	
		$this->proxy->db->unlock();
	
		return $this->proxy->options['return_counts'] ? $this->llen($key) : -1;
	}
	
	function brpoplpush($source, $dest, $timeout) {
		// this is slightly more complicated as we don't want to hold the lock while blocking
		throw new PlodisNotImplementedError();
	}
	
	function lpushx($key, $value) {
		$this->proxy->db->lock();
		$current = $this->lindex($key, 0);
		if($current == null) {
			$result = 0;
		} else {
			$result = $this->lpush($key, array($value));
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function rpushx($key, $value) {
		$this->proxy->db->lock();
		$current = $this->lindex($key, 0);
		if($current == null) {
			$result = 0;
		} else {
			$result = $this->rpush($key, array($value));
		}
		$this->proxy->db->unlock();
		return $result;
	}
	#endif
}

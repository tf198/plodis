<?php
require_once "IRedis_Generic_2_4_0.php";

class Plodis_Generic extends Plodis_Group implements IRedis_Generic_2_4_0 {
	
	/**
	 * How often in seconds to purge expired items
	 * @var float
	 */
	public static $purge_frequency = 0.2;
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, expiry FROM <DB> WHERE pkey=?',
		'delete_key'	=> 'DELETE FROM <DB> WHERE pkey=?',
		'set_expiry'	=> 'UPDATE <DB> SET expiry=? WHERE pkey=?',
		'alarm'			=> 'SELECT MIN(expiry) FROM <DB> WHERE expiry IS NOT NULL',
		'expire'		=> 'DELETE FROM <DB> WHERE expiry IS NOT NULL AND expiry < ?',
		'get_keys' 		=> 'SELECT DISTINCT pkey FROM <DB> ORDER BY id',
		'get_fuzzy_keys'=> 'SELECT DISTINCT pkey FROM <DB> WHERE pkey LIKE ? ORDER BY id',
		'type'			=> 'SELECT type FROM <DB> WHERE pkey=? LIMIT 1',
		'rename'		=> 'UPDATE <DB> SET pkey=? WHERE pkey=?',
		'random'		=> 'SELECT DISTINCT pkey FROM <DB> ORDER BY RANDOM() LIMIT 1',
		'random_MYSQL'	=> 'SELECT DISTINCT pkey FROM <DB> ORDER BY RAND() LIMIT 1',
	);
	
	private $alarm = 0;
	
	public $gc_count = 0;
	
	function del($keys) {
		$c = 0;
		foreach($keys as $key) {
			$c += $this->executeStmt('delete_key', array($key));
		}
		return $c;
	}
	
	function exists($key) {
		return ($this->proxy->string->get($key) == null) ? 0 : 1;
	}
	
	function keys($pattern) {
		if($pattern != '*') {
			$stmt = $this->getStmt('get_fuzzy_keys');
			$pattern = str_replace('*', '%', $pattern);
			$stmt->execute(array($pattern));
		} else {
			$stmt = $this->getStmt('get_keys');
			$stmt->execute();
		}
		return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	}
	
	function ttl($key) {
		$result = $this->fetchOne('select_key', array($key));
		if(!$result || !$result[1]) {
			return -1;
		}
		$ts = (int) $result[1] - time();
		return ($ts < 0) ? -1 : $ts;
	}
	
	function expire($key, $seconds) {
		return $this->expireat($key, $seconds + time());
	}
	
	function expireat($key, $timestamp) {
		$items = $this->executeStmt('set_expiry', array($timestamp, $key));
		if($timestamp < $this->alarm) {
			$this->alarm = $timestamp;
		}
		return ($items) ? 1 : 0;
	}
	
	function persist($key) {
		return $this->expireat($key, null);
	}
	
	function pexpire($key, $milliseconds) {
		return $this->expireat($key, microtime(true) + ($milliseconds/1000));
	}
	
	function pexpireat($key, $timestamp) {
		return $this->expireat($key, $timestamp);
	}
	
	function pttl($key) {
		$result = $this->fetchOne('select_key', array($key));
		if(!$result || !$result[1]) {
			return -1;
		}
	
		$ts = (float)$result[1] - microtime(true);
		$ms = (int) round($ts * 1000);
		return ($ms < 0) ? -1 : $ms;
	}
	
	function gc($force=false) {
		$now = microtime(true);
		if($now < $this->alarm && $force == false) return;
		
		$this->gc_count++;
		//fputs(STDERR, "GC\n");
		$this->executeStmt('expire', array($now));
		
		// set a new alarm
		$result = $this->fetchOne('alarm');
		$this->alarm = ($result[0]) ? $result[0] : $now + $this->proxy->options['purge_frequency'];
	}
	
	function dump($key) {
		throw new PlodisNotImplementedError;
	}
	
	function migrate($host, $port, $key, $destination_db, $timeout) {
		throw new PlodisNotImplementedError;
	}
	
	function move($key, $db) {
		$this->proxy->db->lock();
		// TODO: finish this
		$sql = "INSERT INTO plodis_{$db} SELECT * FROM <DB> WHERE pkey=?";
		$stmt = $this->proxy->db->cachedStmt($sql);
		$stmt->execute(array($key));
		$this->del($key);
		$this->proxy->db->unlock();
	}
	
	function object($subcommand, $arguments=null) {
		throw new PlodisNotImplementedError;
	}
	
	function randomkey() {
		$data = $this->fetchOne('random');
		if(!$data) return null;
		
		return $data[0];
	}
	
	function rename($key, $newkey) {
		if($key == $newkey) throw new PlodisError("Old and new keys are the same");
		
		$this->proxy->db->lock();
		
		$type = $this->type($newkey);
		if($type !== null) {
			$this->del(array($newkey));
		}
		$c = $this->executeStmt('rename', array($newkey, $key));
		if(!$c) {
			$this->proxy->db->unlock(true);
			throw new PlodisError("Key does not exist");
		}
		$this->proxy->db->unlock();
	}
	
	function renamenx($key, $newkey) {
		$this->proxy->db->lock();
		$type = $this->type($newkey);
		if($type !== null) {
			$result = 0;
		} else {
			$result = $this->rename($key, $newkey);
		}
		$this->proxy->db->unlock();
		return $result; 
	}
	
	function restore($key, $ttl, $serialized_value) {
		throw new PlodisNotImplementedError;
	}
	
	function sort($key, $by=null, $limit=null, $get=null, $order=null, $sorting=null, $store=null) {
		switch($this->type($key)) {
			case 'set':
			case 'zset':
				$loc = 'field';
				break;
			default:
				$loc = 'item';
		}
		
		$map = array('_' => 't_0');
		
		$fields = array('t_0.' . $loc);
		$from = array('<DB> t_0');
		$params = array();
		if($by) {
			$parts = explode('->', $by);
			if(count($parts) == 1) $parts[] = 'item';
			$k = $this->_pattern_alias($parts[0], $loc, $map, $from, $params);
			$by = "{$k}.{$parts[1]}";
		} else {
			$by = "t_0.{$loc}";
		}
		
		if($sorting != "ALPHA") {
			$by = "CAST({$by} AS FLOAT)";
		}
		
		if($get) {
			$fields = array();
			for($i=0; $i<count($get); $i++) {
				if($get[$i] == '#') {
					$fields[] = 't_0.' . $loc;
				} else {
					$parts = explode('->', $get[$i]);
					if(count($parts) == 1) $parts[] = 'item';
					$k = $this->_pattern_alias($parts[0], $loc, $map, $from, $params);
					$fields[] = "{$k}.{$parts[1]}";
				}
			}
		}
		
		if($store) {
			$fields = array('?', Plodis::TYPE_LIST, 't_0.' . $loc);
			array_unshift($params, $store);
		}
		
		$sql = "SELECT " . implode(', ', $fields) . " FROM " . implode(' ', $from);
		
		$params[] = $key;
		$sql .= " WHERE t_0.pkey=?";
		
		$sql .= " ORDER BY {$by}";
		
		// ASC DESC
		if($order) $by .= " " . $order;
		
		// TODO: SORTING ignored
		
		if($limit) $sql .= " LIMIT {$limit[0]}, {$limit[1]}";
		
		//var_dump($sql);
		
		if($store) {
			$sql = "INSERT INTO <DB> (pkey, type, item) " . $sql;
			$stmt = $this->proxy->db->cachedStmt($sql);
			$stmt->execute($params);
		} else {
			$stmt = $this->proxy->db->cachedStmt($sql);
			$stmt->execute($params);
			$data = $stmt->fetchAll(PDO::FETCH_NUM);
			if(count($fields) == 1) {
				foreach($data as &$row) $row = $row[0];
			}
			return $data;
		}
	}
	
	function _pattern_alias($pattern, $loc, &$map, &$from, &$params) {
		if(!isset($map[$pattern])) {
			$k = 't_' . count($map);
			$map[$pattern] = $k;
			$from[] = "LEFT JOIN <DB> {$k} ON {$k}.pkey=REPLACE(?, '*', t_0.{$loc})";
			$params[] = $pattern;
		} else {
			$k = $map[$pattern];
		}
		return $k;
	}
	
	function type($key) {
		$data = $this->fetchOne('type', array($key));
		
		if(!$data) return null;
		return Plodis::$types[$data[0]];
	}
}
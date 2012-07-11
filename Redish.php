<?php

/**
 * A Redis-type datastore using SQLite.
 * 
 * Implements the most used methods in as efficient a manner as possible given the constraints
 * of PDO/SQL.
 *
 * @see http://flask.pocoo.org/snippets/88/
 */
class Redish {
	
	/**
	 * How often in seconds to purge expired items
	 * @var float
	 */
	public static $purge_frequency = 0.2;
	
	/**
	 * How often in seconds to poll for blocking operations
	 * @var int
	 */
	public static $poll_frequency = 0.1;
	
	/**
	 * Whether to use strict emulation
	 * @var boolean
	 */
	public static $strict = false;
	
	/**
	 * PDO object
	 * @var PDO
	 */
	public $conn;
	
	/**
	 * Cache our PDOStatements
	 * @var multitype:PDOStatement
	 */
	private $stmt_cache = array();
	
	/**
	 * SQL to setup tables
	 * @var multitype:string
	 */
	private static $create_sql = array(
		'create' => 'CREATE TABLE IF NOT EXISTS redish (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, item BLOB, type NUMERIC, expiry NUMERIC)',
		'index' => 'CREATE INDEX IF NOT EXISTS store_key ON redish (key)',
	);
	
	/**
	 * Keep all the SQL in one place
	 * @var multitype:string
	 */
	private static $sql = array(
		'get_lock' 		=> 'BEGIN IMMEDIATE', // not sure we need this
		'release_lock' 	=> 'COMMIT',
		'alarm'			=> 'SELECT MIN(expiry) FROM redish WHERE expiry IS NOT NULL',
		'dump'			=> 'SELECT * FROM redish',
		
		'select_key' 	=> 'SELECT item, expiry FROM redish WHERE key=?',
		'insert_key' 	=> 'INSERT INTO redish (key, item, expiry) VALUES (?, ?, ?)',
		'update_key'	=> 'UPDATE redish SET item=?, expiry=? WHERE key=?',
		'delete_key'	=> 'DELETE FROM redish WHERE key=?',
		'incrby' 		=> 'UPDATE redish SET item=item + ? WHERE key=?',
		'decrby' 		=> 'UPDATE redish SET item=item - ? WHERE key=?',
	
		'get_keys' 		=> 'SELECT key FROM redish',
		'get_fuzzy_keys'=> 'SELECT key FROM redish WHERE key LIKE ?',
	
		'set_expiry'	=> 'UPDATE redish SET expiry=? WHERE key=?',
		'expire'		=> 'DELETE FROM redish WHERE expiry IS NOT NULL AND expiry < ?',	
	
		'lpush_id'		=> 'SELECT MIN(id) from redish',
		'llen' 			=> 'SELECT COUNT(id) FROM redish WHERE key=?',
		'l_forward'		=> 'SELECT id, item FROM redish WHERE key=? ORDER BY id LIMIT ? OFFSET ?',
		'l_reverse'		=> 'SELECT id, item FROM redish WHERE key=? ORDER BY id DESC LIMIT ? OFFSET ?',
		'l_insert' 		=> 'INSERT INTO redish (id, key, item) VALUES (?, ?, ?)',
		'l_key_val'		=> 'SELECT id FROM redish WHERE key=? AND item=?',
		'l_shift'		=> 'UPDATE redish set id = id+1 WHERE id>?',
		'list_del' 		=> 'DELETE FROM redish WHERE id=?',
	);
	
	private $alarm = 0;
	
	function __construct(PDO $pdo, $init=true) {
		$this->conn = $pdo;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		// skip table creation if not required
		if($init) {
			foreach(self::$create_sql as $sql) {
				$this->conn->exec($sql);
			}
		}
		
		// expire old items
		$this->purgeExpired();
	}
	
	private function purgeExpired() {
		$now = microtime(true);
		$this->executeStatement('expire', array($now));
		
		// set a new alarm
		$result = $this->executeQuery('alarm');
		$this->alarm = ($result[0]) ? $result[0] : $now + self::$purge_frequency;
	}
	
	function getStatement($which) {
		if(!isset($this->stmt_cache[$which])) {
			$this->stmt_cache[$which] = $this->conn->prepare(self::$sql[$which]);
			//echo "CREATED {$which}\n";
		}
		return $this->stmt_cache[$which];
	}
	
	private function executeQuery($which, $params=array()) {
		$stmt = $this->getStatement($which);
		$stmt->execute($params);
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		return $result;
	}
	
	private function executeStatement($which, $params=array()) {
		$stmt = $this->getStatement($which);
		$stmt->execute($params);
		return $stmt->rowCount();
	}
	
	public function debug() {
		$stmt = $this->getStatement('dump');
		$stmt->execute();
		fputs(STDERR, "\n\n");
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$time = ($row['expiry']) ? $row['expiry'] - time() : 'inf';
			fprintf(STDERR, "%3d %3s %-10s %s\n", $row['id'], $time, $row['key'], $row['item']);
		}
	}
	
	/*** STORE METHODS ***/
	
	/**
	 * Get the value of key. If the key does not exist the special
	 * value null is returned. An error is returned if the value stored 
	 * at key is not a string, because GET only handles string values.
	 * 
	 * @param string $key
	 */
	function get($key) {
		if(microtime(true) > $this->alarm) {
			$this->purgeExpired();
		}
		$row = $this->executeQuery('select_key', array($key));
		if(!$row) {
			return null;
		}
		return $row[0];
	}
	
	/**
	 * Set key to hold the string value. If key already holds a value, 
	 * it is overwritten, regardless of its type.
	 * 
	 * @param string $key
	 * @param string $value
	 */
	function set($key, $value) {
		return $this->setex($key, $value, null);
	}
	
	/**
	 * Set key to hold the string value and set key to timeout after
	 * a given number of seconds.
	 * 
	 * @param string $key
	 * @param string $value
	 * @param integer $seconds
	 */
	function setex($key, $value, $seconds) {
		if(is_object($value)) throw new RuntimeException("Cannot convert object to string");
		if(is_array($value)) throw new RuntimeException("Cannot convert array to string");
		
		if($seconds) $seconds += time();
		$count = $this->executeStatement('update_key', array($value, $seconds, $key));
		
		if($count==1) {
			return;
		}
		
		if($count > 1) {
			$this->del($key);
		}
		
		$this->executeStatement('insert_key', array($key, $value, $seconds));
	}
	
	function del($key) {
		$keys = func_get_args();
		$c = 0;
		foreach($keys as $key) {
			$c += $this->executeStatement('delete_key', array($key));
		}
		return $c;
	}
	
	function exists($key) {
		return ($this->get($key) == null) ? 0 : 1;
	}
	
	function incr($key) {
		return $this->incrby($key, 1);
	}
	
	function incrby($key, $increment) {
		if($this->executeStatement('incrby', array($increment, $key)) != 1) {
			$this->set($key, $increment);
		}
	}
	
	function decr($key) {
		$this->decrby($key, 1);
	}
	
	function decrby($key, $decrement) {
		if($this->executeStatement('decrby', array($decrement, $key)) != 1) {
			$this->set($key, -$decrement);
		}
	}
	
	function mget($keys) {
		if(!is_array($keys)) $keys = func_get_args();
		return array_map(array($this, 'get'), $keys);
	}
	
	function mset($pairs) {
		foreach($pairs as $key=>$value) {
			$this->set($key, $value);
		}
	}
	
	function keys($pattern=null) {
		if($pattern) {
			$stmt = $this->getStatement('get_fuzzy_keys');
			$pattern = str_replace('*', '%', $pattern);
			$stmt->execute(array($pattern));
		} else {
			$stmt = $this->getStatement('get_keys');
			$stmt->execute();
		}
		return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	}
	
	// TODO: should be able to append in place...
	function append($key, $value) {
		$new = $this->get($key) . $value;
		$this->set($key, $new);
		return strlen($new);
	}
	
	/*** EXPIRY METHODS ***/
	
	function ttl($key) {
		$result = $this->executeQuery('select_key', array($key));
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
		$items = $this->executeStatement('set_expiry', array($timestamp, $key));
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
		$result = $this->executeQuery('select_key', array($key));
		if(!$result || !$result[1]) {
			return -1;
		}
		
		$ts = (float)$result[1] - microtime(true);
		$ms = (int) round($ts * 1000);
		return ($ms < 0) ? -1 : $ms;
	}
	
	/*** LIST METHODS ***/
	
	function llen($key) {
		$row = $this->executeQuery('llen', array($key));
		return (int) $row[0];
	}
	
	function lindex($key, $index) {
		$s = 'l_forward';
		if($index < 0) {
			$s = 'l_reverse';
			$index = -$index - 1;
		}
		$row = $this->executeQuery($s, array($key, 1, $index));
		return $row[1];
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
		
		$stmt = $this->getStatement($s);
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
	
	function rpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
		
		$stmt = $this->getStatement('l_insert');
		foreach($values as $value) {
			$stmt->execute(array(null, $key, $value));
		}
		
		if(self::$strict) {
			return $this->llen($key);
		} else {
			return -1;
		}
	}
	
	function lpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
		
		// have to transaction this
		$this->conn->beginTransaction();
		
		// find the lowest id
		$id = $this->executeQuery('lpush_id');
		$id = ($id) ? $id[0] - 1 : -1;
		
		$stmt = $this->getStatement('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($id--, $key, $value));
		}
		
		$this->conn->commit();
		
		if(self::$strict) {
			return $this->llen($key);
		} else {
			return -1;
		}
	}
	
	function lpop($key) {
		return $this->_pop($key, 'l_forward');
	}
	
	function rpop($key) {
		return $this->_pop($key, 'l_reverse');
	}
	
	function blpop($key) {
		return $this->_pop($key, 'l_forward', true);
	}
	
	function brpop($key) {
		return $this->_pop($key, 'l_reverse', true);
	}
	
	private function _pop($key, $type, $wait=false) {
		$pop = $this->getStatement($type);
		$del = $this->getStatement('list_del');
		
		// do everything in an optomised lock
		$us = self::$poll_frequency * 1000000;
		
		while(true) {
			$this->conn->beginTransaction();
			$pop->execute(array($key, 1, 0));
			$result = $pop->fetch(PDO::FETCH_NUM);
			$pop->closeCursor();
			if($result) {
				$del->execute(array($result[0]));
			}
			$this->conn->commit();
			if(!$result && $wait) {
				usleep($us);
			} else {
				break;
			}
		}
		
		return ($result) ? $result[1] : null;
	}
}

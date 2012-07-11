<?php

/**
 * A Redis-type datastore using SQLite.
 * 
 * Implements the most used methods in as efficient a manner as possible given the constraints
 * of PDO/SQL.
 *
 * @see http://flask.pocoo.org/snippets/88/
 */
class Plodis {
	
	const CHANNEL_PREFIX = '_channel_';
	const SUBSCRIBER_PREFIX = '_subscriber_';
	
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
		'CREATE TABLE IF NOT EXISTS plodis (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, item BLOB, list_index NUMERIC, expiry NUMERIC)',
		'CREATE INDEX IF NOT EXISTS plodis_key ON plodis (key)',
		'CREATE INDEX IF NOT EXISTS plodis_list_index ON plodis (list_index)',
		'CREATE INDEX IF NOT EXISTS plodis_expiry ON plodis (expiry)',
	);
	
	/**
	 * SQL to optomise file based SQLite databases
	 * @var multitype:string
	 */
	private static $opt_sql = array(
		'PRAGMA case_sensitive_like = 1',
		'PRAGMA journal_mode = MEMORY',
		'PRAGMA temp_store = MEMORY',
		'PRAGMA synchronous = OFF',
	);
	
	/**
	 * Keep all the SQL in one place
	 * @var multitype:string
	 */
	private static $sql = array(
		'get_lock' 		=> 'BEGIN IMMEDIATE', // not sure we need this
		'release_lock' 	=> 'COMMIT',
		'alarm'			=> 'SELECT MIN(expiry) FROM plodis WHERE expiry IS NOT NULL',
		'dump'			=> 'SELECT * FROM plodis',
		
		'select_key' 	=> 'SELECT item, expiry FROM plodis WHERE key=?',
		'insert_key' 	=> 'INSERT INTO plodis (key, item, expiry) VALUES (?, ?, ?)',
		'update_key'	=> 'UPDATE plodis SET item=?, expiry=? WHERE key=?',
		'delete_key'	=> 'DELETE FROM plodis WHERE key=?',
		'incrby' 		=> 'UPDATE plodis SET item=item + ? WHERE key=?',
		'decrby' 		=> 'UPDATE plodis SET item=item - ? WHERE key=?',
	
		'get_keys' 		=> 'SELECT key FROM plodis',
		'get_fuzzy_keys'=> 'SELECT key FROM plodis WHERE key LIKE ?',
	
		'set_expiry'	=> 'UPDATE plodis SET expiry=? WHERE key=?',
		'expire'		=> 'DELETE FROM plodis WHERE expiry IS NOT NULL AND expiry < ?',	
	
		'lpush_index'	=> 'SELECT MIN(list_index) from plodis',
		'llen' 			=> 'SELECT COUNT(id) FROM plodis WHERE key=?',
		'l_forward'		=> 'SELECT id, item FROM plodis WHERE key=? ORDER BY list_index, id LIMIT ? OFFSET ?',
		'l_reverse'		=> 'SELECT id, item FROM plodis WHERE key=? ORDER BY list_index DESC, id DESC LIMIT ? OFFSET ?',
		'l_insert' 		=> 'INSERT INTO plodis (key, item, list_index) VALUES (?, ?, ?)',
		'l_key_val'		=> 'SELECT id, list_index FROM plodis WHERE key=? AND item=?',
		'l_shift'		=> 'UPDATE plodis set list_index = list_index+1 WHERE id>? AND list_index>=?', // creates a space after the target item
		'list_del' 		=> 'DELETE FROM plodis WHERE id=?',
	);
	
	/**
	 * An alarm for expiring keys
	 * @var integer
	 */
	private $alarm = 0;
	
	/**
	 * Unique identifier for this client
	 * @var string
	 */
	private $uid;
	
	/**
	 * List of subscribed channels
	 * @var multitype:string
	 */
	private $subscribed = array();
	
	/**
	 * @param PDO $pdo
	 * @param boolean $init create tables if neccesary
	 * @param boolean $opt run SQLite optomisations
	 */
	function __construct(PDO $pdo, $init=true, $opt=true) {
		$this->conn = $pdo;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->uid = uniqid();
		
		// skip table creation if not required
		if($init) {
			
			foreach(self::$create_sql as $sql) {
				$this->conn->exec($sql);
			}
		}
		
		if($opt == true) {
			foreach(self::$opt_sql as $sql) {
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
			fprintf(STDERR, "%3d %3d %3s %-10s %s\n", $row['id'], $row['list_index'], $time, $row['key'], $row['item']);
		}
	}
	
	public function complexity() {
		foreach(self::$sql as $key=>$sql) {
			$stmt = $this->conn->prepare('EXPLAIN QUERY PLAN ' . $sql);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_COLUMN, 3);
			fputs(STDERR, "\n\n=== {$key} ===\n");
			fputs(STDERR, "-- {$sql} --\n");
			foreach($data as $line) {
				fputs(STDERR, $line . PHP_EOL);
			}
		}
	}
	
	/*** STORE METHODS ***/
	
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
	
	function set($key, $value) {
		return $this->setex($key, $value, null);
	}
	
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
	
	function linsert($key, $pos, $pivot, $value) {
		// make atomic
		$this->conn->beginTransaction();
		
		$item = $this->executeQuery('l_key_val', array($key, $pivot));
		if(!$item) {
			$this->commit();
			return -1;
		}
		
		if($pos == 'before') $item[0]--;
		
		$this->executeQuery('l_shift', $item);
		$this->executeQuery('l_insert', array($key, $value, $item[1]));
		
		$this->conn->commit();
		
		if(self::$strict) {
			return $this->llen($key);
		}
	}
	
	function lrem($key, $count, $value) {
		
	}
	
	function rpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
		
		$stmt = $this->getStatement('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, 0));
		}
		
		if(self::$strict) {
			return $this->llen($key);
		}
	}
	
	function lpush($key, $values) {
		if(!is_array($values)) $values = array_slice(func_get_args(), 1);
		
		// have to transaction this
		$this->conn->beginTransaction();
		
		// find the lowest id
		$id = $this->executeQuery('lpush_index');
		$id = ($id) ? $id[0] - 1 : -1;
		
		$stmt = $this->getStatement('l_insert');
		foreach($values as $value) {
			$stmt->execute(array($key, $value, $id--));
		}
		
		$this->conn->commit();
		
		if(self::$strict) {
			return $this->llen($key);
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
	
	/*** PUB / SUB METHODS ***/
	
	private function _channels($channels) {
		foreach($channels as &$channel) {
			$channel = "_channel_" . $channel;
		}
		return $channels;
	}
	
	function subscribe($channels) {
		if(!is_array($channels)) $channels = func_get_args();
		
		foreach($channels as $channel) {
			$this->rpush(self::CHANNEL_PREFIX . $channel, $this->uid);
			//$this->publish($channel, "{$this->uid} joined channel {$channel}");
		}
	}
	
	function unsubscribe($channels) {
		if(!is_array($channels)) $channels = func_get_args();
		
		foreach($channels as $channel) {
			$this->lrem(self::CHANNEL_PREFIX . $channel, 1, $this->uid);
		}
	}
	
	function publish($channel, $message) {
		$subscribers = $this->lrange(self::CHANNEL_PREFIX . $channel, 0, -1);
		foreach($subscribers as $subscriber) {
			$this->rpush(self::SUBSCRIBER_PREFIX . $subscriber, $message);
		}
		//$this->debug();
		return count($subscribers);
	}
	
	function receive() {
		return $this->blpop(self::SUBSCRIBER_PREFIX . $this->uid);
	}
}

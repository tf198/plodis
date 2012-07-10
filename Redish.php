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
	 * PDO object
	 * @var PDO
	 */
	private $conn;
	
	/**
	 * Cache our PDOStatements
	 * @var multitype:PDOStatement
	 */
	private $cache = array();
	
	/**
	 * Keep all the SQL in one place
	 * @var multitype:string
	 */
	private static $sql = array(
		'create_list' => 'CREATE TABLE IF NOT EXISTS list (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, item BLOB)',
		'create_store' => 'CREATE TABLE IF NOT EXISTS store (key TEXT UNIQUE, item BLOB)',
		'write_lock' => 'BEGIN IMMEDIATE',
		
		'get' => 'SELECT item FROM store WHERE key=?',
		'set' => 'INSERT OR REPLACE INTO store (key, item) VALUES (?, ?)',
		'incrby' => 'UPDATE store SET item=item + ? WHERE key=?',
		'decrby' => 'UPDATE store SET item=item - ? WHERE key=?',
		
		'llen' => 'SELECT COUNT(id) FROM list WHERE key=?',
		'rpush' => 'INSERT INTO list (key, item) VALUES (?, ?)',
		'lpop' => 'SELECT id, item FROM list WHERE key=? ORDER BY id LIMIT 1',
		'rpop' => 'SELECT id, item FROM list WHERE key=? ORDER BY id DESC LIMIT 1',
		'lindex' => 'SELECT id, item FROM list WHERE key=? ORDER BY id LIMIT 1 OFFSET ?',
		'list_del' => 'DELETE FROM list WHERE id=?',
	);
	
	function __construct($file) {
		$this->conn = new PDO("sqlite:" . $file);
		//$this->conn = new PDO('sqlite::memory:');
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->conn->exec(self::$sql['create_list']);
		$this->conn->exec(self::$sql['create_store']);
		$this->conn->exec('PRAGMA synchronous = OFF');
		//$this->conn->exec('PRAGMA journal_mode=WAL');
	}
	
	function getStatement($which) {
		if(!isset($this->cache[$which])) {
			$this->cache[$which] = $this->conn->prepare(self::$sql[$which]);
			echo "CREATED {$which}\n";
		}
		return $this->cache[$which];
	}
	
	function executeQuery($which, $params) {
		$stmt = $this->getStatement($which);
		$stmt->execute($params);
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		return $result;
	}
	
	function executeStatement($which, $params) {
		$stmt = $this->getStatement($which);
		$stmt->execute($params);
		return $stmt->rowCount();
	}
	
	/*** STORE METHODS ***/
	
	function get($key) {
		$row = $this->executeQuery('get', array($key));
		return ($row) ? json_decode($row[0]) : null;
	}
	
	function set($key, $value) {
		$stmt = $this->getStatement('set');
		$stmt->execute(array($key, json_encode($value)));
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
	
	function mget() {
		$keys = func_get_args();
		return array_map(array($this, 'get'), $keys);
	}
	
	function mset($pairs) {
		foreach($pairs as $key=>$value) {
			$this->set($key, $value);
		}
	}
	
	// TODO: should be able to append in place...
	function append($key, $value) {
		$current = $this->get($key);
		$this->set($key, $current . $value);
	}
	
	/*** LIST METHODS ***/
	
	function llen($key) {
		$row = $this->executeQuery('llen', array($key));
		return $row[0];
	}
	
	function lindex($key, $index) {
		$row = $this->executeQuery('lindex', array($key, $index));
		return $row[1];
	}
	
	function rpush($key, $obj) {
		$stmt = $this->getStatement('rpush');
		$stmt->execute(array($key, json_encode($obj)));
	}
	
	function lpush($key, $obj) {
		throw new Exception("Not implemented");
	}
	
	function lpop($key) {
		return $this->_pop($key, 'lpop');
	}
	
	function rpop($key) {
		return $this->_pop($key, 'rpop');
	}
	
	function blpop($key) {
		return $this->_pop($key, 'lpop', true);
	}
	
	function brpop($key) {
		return $this->_pop($key, 'rpop', true);
	}
	
	function _pop($key, $type, $wait=false) {
		$pop = $this->getStatement($type);
		$del = $this->getStatement('list_del');
		
		// do everything in an optomised lock
		
		while(true) {
			$this->conn->exec(self::$sql['write_lock']);
			$pop->execute(array($key));
			$result = $pop->fetch(PDO::FETCH_NUM);
			$pop->closeCursor();
			if($result) {
				$del->execute(array($result[0]));
			}
			$this->conn->exec('COMMIT');
			if(!$result && $wait) {
				usleep(250);
			} else {
				break;
			}
		}
		
		return ($result) ? json_decode($result[1]) : null;
	}
}

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
	public $conn;
	
	/**
	 * Cache our PDOStatements
	 * @var multitype:PDOStatement
	 */
	private $cache = array();
	
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
		'get_lock' => 'BEGIN IMMEDIATE',
		'release_lock' => 'COMMIT',
		
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
	
		'llen' 			=> 'SELECT COUNT(id) FROM redish WHERE key=?',
		'rpush' 		=> 'INSERT INTO redish (key, item) VALUES (?, ?)',
		'lpop' 			=> 'SELECT id, item FROM redish WHERE key=? ORDER BY id LIMIT 1',
		'rpop' 			=> 'SELECT id, item FROM redish WHERE key=? ORDER BY id DESC LIMIT 1',
		'lindex' 		=> 'SELECT id, item FROM redish WHERE key=? ORDER BY id LIMIT 1 OFFSET ?',
		'list_del' 		=> 'DELETE FROM redish WHERE id=?',
	);
	
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
		$this->executeStatement('expire', array(time()));
	}
	
	function getStatement($which) {
		if(!isset($this->cache[$which])) {
			$this->cache[$which] = $this->conn->prepare(self::$sql[$which]);
			//echo "CREATED {$which}\n";
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
	
	/**
	 * Get the value of key. If the key does not exist the special
	 * value null is returned. An error is returned if the value stored 
	 * at key is not a string, because GET only handles string values.
	 * 
	 * @param string $key
	 */
	function get($key) {
		$row = $this->executeQuery('select_key', array($key));
		return ($row) ? $row[0] : null;
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
	
	function mget() {
		$keys = func_get_args();
		return array_map(array($this, 'get'), $keys);
	}
	
	function mset($pairs) {
		foreach($pairs as $key=>$value) {
			$this->set($key, $value);
		}
	}
	
	function keys($pattern=null) {
		if($pattern) {
			
		} else {
			$stmt = $this->getStatement('get_keys');
			$stmt->execute();
		}
		return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	}
	
	// TODO: should be able to append in place...
	function append($key, $value) {
		$current = $this->get($key);
		$this->set($key, $current . $value);
	}
	
	/*** EXPIRY METHODS ***/
	
	function ttl($key) {
		$result = $this->executeQuery('select_key', array($key));
		if(!$result) return -1;
		return ($result[1]) ? (int)$result[1] - time() : -1;
	}
	
	function expire($key, $seconds) {
		return $this->expireat($key, $seconds + time());
	}
	
	function expireat($key, $timestamp) {
		$items = $this->executeStatement('set_expiry', array($timestamp, $key));
		return ($items) ? 1 : 0;
	}
	
	function persist($key) {
		return $this->expireat($key, null);
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
			$this->executeStatement('get_lock');
			$pop->execute(array($key));
			$result = $pop->fetch(PDO::FETCH_NUM);
			$pop->closeCursor();
			if($result) {
				$del->execute(array($result[0]));
			}
			$this->executeStatement('release_lock');
			if(!$result && $wait) {
				usleep(250);
			} else {
				break;
			}
		}
		
		return ($result) ? json_decode($result[1]) : null;
	}
}

<?php
require_once "Group.php";

define('PLODIS_BASE', dirname(dirname(__FILE__)));

class Plodis_Proxy {
	
	const TYPE_STRING	= 1;
	const TYPE_LIST		= 2;
	const TYPE_HASH		= 3;
	const TYPE_SET		= 4;
	const TYPE_ZSET		= 5;
	
	public static $types = array(
		self::TYPE_STRING => 'string',
		self::TYPE_LIST	=> 'list',
		self::TYPE_HASH	=> 'hash',
		self::TYPE_SET	=> 'set',
		self::TYPE_ZSET	=> 'zset',
	);
	
	// 32bit `infinities`
	const POS_INF = 4294967295;
	const NEG_INF = -4294967295;
	
	/**
	 * Generic module
	 * @var Plodis_Generic
	 */
	public $generic;
	
	/**
	 * Database manager
	 * @var Plodis_DB
	 */
	public $db;
	
	/**
	 * Minimum LOG_LEVEL to output
	 * @var integer
	 */
	public static $log_level = LOG_WARNING;
	
	public static $log_levels = array(
		LOG_EMERG 	=> 'EMERG',
		LOG_ALERT 	=> 'ALERT',
		LOG_CRIT  	=> 'CRIT',
		LOG_ERR   	=> 'ERROR',
		LOG_WARNING => 'WARN',
		LOG_NOTICE	=> 'NOTICE',
		LOG_INFO	=> 'INFO',
		LOG_DEBUG	=> 'DEBUG',
	);
	
	public static $default_options = array(
		'validation_checks' 	=> true,
		'use_type_cache' 		=> true,
		'return_counts' 		=> true,
		'return_incr_values' 	=> true,
		'poll_frequency'		=> 0.1,
		'purge_frequency'		=> 0.2,
		'table_base'			=> 'plodis_',
	);
	
	public $options;
	
	/**
	 * @param PDO|string $pdo
	 * @param boolean $init create tables if neccesary
	 * @param boolean $opt run SQLite optomisations
	 */
	function __construct($pdo, $options=array()) {
		$this->options = array_merge(self::$default_options, $options);
		
		// allow for sqlite target passed as string
		if(is_string($pdo)) $pdo = new PDO('sqlite:' . $pdo);
		
		$this->db = new Plodis_DB($this, $pdo);
		
		// we know we need it so manually load it
		$this->load('generic');
		
		// expire old items
		$this->generic->gc();
	}
	
	/**
	 * Use this to dynamically load Plodis Groups
	 * @param string $name
	 */
	function __get($name) {
		return $this->load($name);
	}
	
	/**
	 * Load a Module onto the proxy
	 * 
	 * @param string $name
	 * @param string $klass
	 * @throws RuntimeException
	 */
	function load($name, $klass=null) {
		if(isset($this->$name)) return $this->$name;
		
		if($klass === null) {
			$title = str_replace(' ', '_', ucwords(str_replace('_', ' ', $name)));
			$klass = "Plodis_{$title}";
			if(!is_readable(PLODIS_BASE . "/Plodis/{$title}.php")) throw new RuntimeException("Unknown module: {$name}");
			require_once PLODIS_BASE . "/Plodis/{$title}.php";
		}
		
		$this->$name = new $klass($this);
		
		$this->log("Loaded module {$title}", LOG_INFO);
		return $this->$name;
	}
	
	/**
	 * Output a log message
	 * Goes to STDERR if CLI, otherwise php error log
	 * 
	 * @param string $message
	 * @param integer $level one of the 
	 */
	public function log($message, $level=LOG_INFO) {
		if($level > self::$log_level) return;
		
		$message = sprintf("%6s: %s", self::$log_levels[$level], $message);
		
		if(PHP_SAPI == 'cli') {
			fputs(STDERR, $message . PHP_EOL);
		} else {
			error_log($message);
		}
	}
	
	public function getOption($name, $default=null) {
		return (isset($this->options[$name])) ? $this->options[$name] : $default;
	}
	
	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}
	
	public function pipeline($callable) {
		$this->db->lock();
		$callable($this);
		$this->db->unlock();
	}
}

/**
 * Class to keep all the backend PDO stuff hidden
 */
class Plodis_DB {
	
	/**
	 * PDO object
	 * @var PDO
	 */
	private $conn;
	
	/**
	 * SQL to optomise file based SQLite databases
	 * @var multitype:string
	 */
	private static $optomisations = array(
		'SQLITE' => array(
			'PRAGMA case_sensitive_like = 1',
			'PRAGMA journal_mode = MEMORY',
			'PRAGMA temp_store = MEMORY',
			'PRAGMA synchronous = OFF',
		),
	);
	
	/**
	 * SQL to setup tables
	 * @var multitype:string
	 */
	private static $create_sql = array(
		'SQLITE' => array(
			'CREATE TABLE IF NOT EXISTS <DB> (id INTEGER PRIMARY KEY AUTOINCREMENT, type NUMERIC, pkey TEXT, field TEXT, weight NUMERIC, item TEXT, expiry NUMERIC, UNIQUE(pkey, field))',
			'CREATE INDEX IF NOT EXISTS <DB>_weight ON <DB> (pkey, weight)',
		),
		'MYSQL' => array(
			'CREATE TABLE IF NOT EXISTS <DB> (id INTEGER NOT NULL AUTO_INCREMENT, type SMALLINT NOT NULL, pkey VARCHAR(255) NOT NULL, field VARCHAR(255), weight INTEGER, item BLOB, expiry DOUBLE, PRIMARY KEY(id), UNIQUE(pkey, field))',
			'CREATE INDEX <DB>_weight ON <DB> (pkey, weight)',
		),
	);
	
	private $stmt_cache = array();
	
	private $db_table;
	
	private $initialised = array();
	
	private $lock_count = 0;
	
	public $driver;
	
	function __construct($proxy, $pdo) {
		$this->proxy = $proxy;
		$this->conn = $pdo;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->driver = strtoupper($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME));
		$this->optomiseDatabase();
		$this->selectDatabase(0);
	}
	
	function optomiseDatabase() {
		if(!isset(self::$optomisations[$this->driver])) return;
		foreach(self::$optomisations[$this->driver] as $sql) {
			$this->conn->exec($sql);
		}
	}
	
	private function initTable() {
		if(array_search($this->db_table, $this->initialised) !== false) return;
		foreach(self::$create_sql[$this->driver] as $sql) {
			$sql = str_replace('<DB>', $this->db_table, $sql);
			$c = $this->conn->exec($sql);
			if($this->driver == 'MYSQL' && $c == 0) break; // no CREATE INDEX IF NOT EXISTS
		}
		$this->proxy->log("Initialised {$this->db_table}", LOG_INFO);
		$this->initialised[] = $this->db_table;
	}
	
	/**
	 * Get a prepared statement, caching if possible
	 * 
	 * @param string $sql
	 * @return PDOStatement
	 */
	function cachedStmt($sql) {
		$sql = str_replace('<DB>', $this->db_table, $sql);
		
		// dont cache anything with an OFFSET
		if(strpos($sql, 'OFFSET')) return $this->conn->prepare($sql);
		
		if(!isset($this->stmt_cache[$sql])) {
			$this->stmt_cache[$sql] = $this->conn->prepare($sql);
			//fputs(STDERR, "CACHED {$sql}\n");
		}
		return $this->stmt_cache[$sql];
	}
	
	function selectDatabase($id) {
		$this->db_table = $this->proxy->options['table_base'] . $id;
		$this->initTable();
		$this->proxy->log("Selected database {$id}", LOG_INFO);
	}
	
	function getDatabase() {
		return $this->db_table;
	}
	
	function getConnection() {
		return $this->conn;
	}
	
	function getDBTable() {
		return $this->db_table;
	}
	
	public function close() {
		$this->conn = null;
	}
	
	public function lock() {
		if($this->lock_count == 0) {
			$this->conn->beginTransaction();
			//$this->proxy->log("Started transaction", LOG_WARNING);
		} else {
			$savepoint = "LOCK_" . $this->lock_count;
			$this->conn->exec("SAVEPOINT {$savepoint}");
			//$this->proxy->log("Created savepoint {$savepoint}", LOG_WARNING);
		}
		$this->lock_count++;
	}
	
	public function unlock($rollback=false) {
		$this->lock_count--;
		
		if($this->lock_count == 0) {
			if($rollback) {
				$this->proxy->log("Rolling back transaction", LOG_INFO);
				$this->conn->rollBack();
			} else {
				//$this->proxy->log("Commiting transaction", LOG_WARNING);
				$this->conn->commit();
			}
		} else {
			$savepoint = "LOCK_" . $this->lock_count;
		
			if($rollback) {
				$this->proxy->log("Rolling back to {$savepoint}", LOG_INFO);
				$this->conn->exec("ROLLBACK TO {$savepoint}");
			}
	
			$this->conn->exec("RELEASE SAVEPOINT {$savepoint}");
			//$this->proxy->log("Released {$savepoint}", LOG_WARNING);
		}
	}
	
	public function discard() {
		if($this->lock_count) {
			$this->proxy->log("Discarding all actions in current transaction", LOG_WARNING);
			$this->conn->rollBack();
			$this->lock_count = 0;
		}
	}
	
	public function getLockCount() {
		return $this->lock_count;
	}
	
	public function debug($key) {
		$stmt = $this->cachedStmt("SELECT * FROM <DB> WHERE pkey=? ORDER BY field, id");
		$stmt->execute(array($key));
		fputs(STDERR, "\nDEBUG [{$key}]\n");
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$time = ($row['expiry']) ? $row['expiry'] - time() : 'inf';
			fprintf(STDERR, "%5d %-6s %-6s %3s %4d %s\n", $row['id'], $row['pkey'], $row['field'], $time, $row['weight'], $row['item']);
		}
	}
	
	public function explain($sql) {
		$stmt = $this->conn->prepare('EXPLAIN QUERY PLAN ' . str_replace('<DB>', $this->db_table, $sql));
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_COLUMN, 3);
		fputs(STDERR, "\n-- {$sql} --\n");
		foreach($data as $line) {
			fputs(STDERR, $line . PHP_EOL);
		}
	}
}


class PlodisError extends RuntimeException {}

class PlodisNotImplementedError extends PlodisError {}

class PlodisIncorrectKeyType extends PlodisError {
	function __construct($message="Operation against a key holding the wrong kind of value", $code=null) {
		parent::__construct($message, $code);
	}
}

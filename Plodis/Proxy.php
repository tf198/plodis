<?php
require_once "Plodis/Group.php";

class Plodis_Proxy {
	
	/**
	 * PDO object
	 * @var PDO
	 */
	public $conn;
	
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
	
	private $group_cache = array();
	
	private $stmt_cache = array();
	
	private $alarm = 0;
	
	/**
	 * @param PDO $pdo
	 * @param boolean $init create tables if neccesary
	 * @param boolean $opt run SQLite optomisations
	 */
	function __construct(PDO $pdo, $init=true, $opt=true) {
		$this->conn = $pdo;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
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
		$this->group_generic->gc();
	}
	
	function cachedStmt($sql) {
		if(!isset($this->stmt_cache[$sql])) {
			$this->stmt_cache[$sql] = $this->conn->prepare($sql);
			//fputs(STDERR, "CACHED {$sql}\n");
		}
		return $this->stmt_cache[$sql];
	}
	
	/**
	 * Use this to dynamically load Plodis Groups
	 * @param string $name
	 */
	function __get($name) {
		$parts = explode('_', $name);	
		$this->$name = $this->group($parts[1]);
// 		/fputs(STDERR, "LOADED {$name}\n");
		return $this->$name;
	}
	
	/**
	 * Factory for group objects
	 * @param string $name
	 */
	private function group($name) {
		if(!isset($this->group_cache[$name])) {
			$title = ucfirst($name);
			$klass = "Plodis_{$title}";
			require_once "Plodis/{$title}.php";
			$this->group_cache[$name] = new $klass($this);
		}
		
		return $this->group_cache[$name];
	}
	
	public function setAlarm($alarm) {
		$this->alarm = $alarm;
	}
	
	public function checkAlarm() {
		return (microtime(true) > $this->alarm);
	}
}
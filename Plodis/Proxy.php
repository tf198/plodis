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
	
	private $stmt_cache = array();
	
	private static $log_level = LOG_INFO;
	
	private $lock_count;
	
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
		$this->generic->gc();
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
		return $this->load($name);
	}
	
	function load($name, $klass=null) {
		if(isset($this->$name)) return $this->$name;
		
		if($klass === null) {
			$title = ucfirst($name);
			$klass = "Plodis_{$title}";
			$file = "Plodis/{$title}.php";
			if(!is_readable($file)) throw new RuntimeException("Unknown module: {$name}");
			require_once $file;
		}
		
		$this->$name = new $klass($this);
		
		return $this->$name;
	}
	
	public function lock() {
		if($this->lock_count == 0) {
			$this->conn->beginTransaction();
			$this->rollback = false;
		}
		$this->lock_count++;
	}
	
	public function unlock($rollback=false) {
		$this->lock_count--;
		
		if($rollback) {
			$this->conn->rollBack();
			throw new RuntimeException("Multi transaction rollback - unpredictable results possible");
		}
		
		if($this->lock_count == 0) {
			$this->conn->commit();
		}
	}
	
	public function log($message, $level=LOG_INFO) {
		if($level <= self::$log_level) {
			fputs(STDERR, $message . "\n");
		}
	}
	
	public function defineCommand($cmd, $klass) {
		
	}
	
	static function strict() {
		Plodis_String::$return_values = true;
		Plodis_List::$return_counts = true;
	}
}
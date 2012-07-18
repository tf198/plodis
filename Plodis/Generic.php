<?php
require_once PLODIS_BASE . "/interfaces/Redis_Generic_2_6_0.php";

class Plodis_Generic extends Plodis_Group implements Redis_Generic_2_6_0 {
	
	/**
	 * How often in seconds to purge expired items
	 * @var float
	 */
	public static $purge_frequency = 0.2;
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, expiry FROM <DB> WHERE key=?',
		'delete_key'	=> 'DELETE FROM <DB> WHERE key=?',
		'set_expiry'	=> 'UPDATE <DB> SET expiry=? WHERE key=?',
		'alarm'			=> 'SELECT MIN(expiry) FROM <DB> WHERE expiry IS NOT NULL',
		'expire'		=> 'DELETE FROM <DB> WHERE expiry IS NOT NULL AND expiry < ?',
		'get_keys' 		=> 'SELECT DISTINCT key FROM <DB> ORDER BY id',
		'get_fuzzy_keys'=> 'SELECT DISTINCT key FROM <DB> WHERE key LIKE ? ORDER BY id',
		'type'			=> 'SELECT type FROM <DB> WHERE key=? LIMIT 1',
		'rename'		=> 'UPDATE <DB> SET key=? WHERE key=?',
		'random'		=> 'SELECT DISTINCT key FROM <DB> ORDER BY RANDOM() LIMIT 1',
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
	
	function keys($pattern=null) {
		if($pattern) {
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
		throw new PlodisNotImplementedError;
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
		throw new PlodisNotImplementedError;
	}
	
	function type($key) {
		$data = $this->fetchOne('type', array($key));
		
		if(!$data) return null;

		return Plodis::$types[$data[0]];
	}
	
	function verify($key, $expected) {
		if($this->proxy->options['validation_checks'] == false) return;
		
		$type = $this->type($key);
		if($type === null) return;
		if($type != $expected) throw new PlodisIncorrectKeyType;
	}
}
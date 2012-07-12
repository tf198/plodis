<?php
class Plodis_Generic extends Plodis_Group {
	
	/**
	 * How often in seconds to purge expired items
	 * @var float
	 */
	public static $purge_frequency = 0.2;
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, expiry FROM plodis WHERE key=?',
		'delete_key'	=> 'DELETE FROM plodis WHERE key=?',
		'set_expiry'	=> 'UPDATE plodis SET expiry=? WHERE key=?',
		'alarm'			=> 'SELECT MIN(expiry) FROM plodis WHERE expiry IS NOT NULL',
		'expire'		=> 'DELETE FROM plodis WHERE expiry IS NOT NULL AND expiry < ?',
		'get_keys' 		=> 'SELECT key FROM plodis',
		'get_fuzzy_keys'=> 'SELECT key FROM plodis WHERE key LIKE ?',
	);
	
	private $alarm = 0;
	
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
		
		//fputs(STDERR, "GC\n");
		$this->executeStmt('expire', array($now));
		
		// set a new alarm
		$result = $this->fetchOne('alarm');
		$this->alarm = ($result[0]) ? $result[0] : $now + self::$purge_frequency;
	}
}
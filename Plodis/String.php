<?php
require_once PLODIS_BASE . "/interfaces/Redis_String_2_6_0.php";

class Plodis_String extends Plodis_Group implements Redis_String_2_6_0 {
	
	/**
	 * Whether to return incr/decr results
	 * @var boolean
	 */
	public static $return_values = false;
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, list_index FROM plodis WHERE key=?',
		'insert_key' 	=> 'INSERT INTO plodis (key, item, expiry) VALUES (?, ?, ?)',
		'update_key'	=> 'UPDATE plodis SET item=?, expiry=? WHERE key=?',
		'delete_key'	=> 'DELETE FROM plodis WHERE key=?',
		'incrby' 		=> 'UPDATE plodis SET item=item + ? WHERE key=?',
	);
	
	private $_get;
	
	function set($key, $value) {
		return $this->setex($key, $value, null);
	}
	
	function setex($key, $value, $seconds) {
		if(is_object($value)) throw new RuntimeException("Cannot convert object to string");
		if(is_array($value)) throw new RuntimeException("Cannot convert array to string");
	
		if($seconds) $seconds += time();
		$count = $this->executeStmt('update_key', array($value, $seconds, $key));
	
		if($count==1) {
			return;
		}
	
		if($count > 1) {
			$this->del($key);
		}
	
		$this->executeStmt('insert_key', array($key, $value, $seconds));
	}
	
	function mset($pairs) {
		foreach($pairs as $key=>$value) {
			$this->set($key, $value);
		}
	}
	
	function get($key, $_value=null, $_throw=true) {
		$this->proxy->generic->gc();
		
		$row = $this->fetchOne('select_key', array($key));
		
		if(!$row) {
			return null;
		}
		if($row[1] !== null && $_throw) throw new RuntimeException('Operation against a key holding the wrong kind of value');
		return $row[0];
	}
	
	function mget($keys) {
		foreach($keys as &$key) {
			try {
				$key = $this->get($key);
			} catch(RuntimeException $e) {
				$key = null;
			}
		}
		return $keys;
	}
	
	function incr($key) {
		return $this->incrby($key, 1);
	}
	
	function incrby($key, $increment) {
		$c = $this->executeStmt('incrby', array($increment, $key));
		
		// check for list/hash
		if($c > 1) throw new RuntimeException('Operation against a key holding the wrong kind of value');
		
		if($c == 0) {
			$this->set($key, $increment);
		}
		
		if(self::$return_values) return (int)$this->get($key);
	}
	
	function decr($key) {
		return $this->incrby($key, -1);
	}
	
	function decrby($key, $decrement) {
		return $this->incrby($key, -$decrement);
	}
	
	function append($key, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function bitcount($key, $start=null, $end=null) {
		throw new PlodisNotImplementedError;
	}
	
	function bitop($operation, $destkey, $key) {
		throw new PlodisNotImplementedError;
	}
	
	function getbit($key, $offset) {
		throw new PlodisNotImplementedError;
	}
	
	function getrange($key, $start, $end) {
		throw new PlodisNotImplementedError;
	}
	
	function getset($key, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function incrbyfloat($key, $increment) {
		throw new PlodisNotImplementedError;
	}
	
	function msetnx($keys) {
		throw new PlodisNotImplementedError;
	}
	
	function psetex($key, $milliseconds, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function setbit($key, $offset, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function setnx($key, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function setrange($key, $offset, $value) {
		throw new PlodisNotImplementedError;
	}
	
	function strlen($key) {
		throw new PlodisNotImplementedError;
	}
}
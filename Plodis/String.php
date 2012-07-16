<?php
require_once PLODIS_BASE . "/interfaces/Redis_String_2_6_0.php";

class Plodis_String extends Plodis_Group implements Redis_String_2_6_0 {
	
	/**
	 * Whether to return incr/decr results
	 * @var boolean
	 */
	public static $return_values = false;
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, type FROM <DB> WHERE key=?',
		'insert_key' 	=> 'INSERT INTO <DB> (key, type, item, expiry) VALUES (?, ?, ?, ?)',
		'update_key'	=> 'UPDATE <DB> SET item=?, expiry=?, field=NULL WHERE key=?',
		'delete_key'	=> 'DELETE FROM <DB> WHERE key=?',
		'incrby' 		=> 'UPDATE <DB> SET item=item + ? WHERE key=?',
	);
	
	function set($key, $value) {
		return $this->setex($key, $value, null);
	}
	
	function setex($key, $value, $seconds) {
		if(is_object($value)) throw new RuntimeException("Cannot convert object to string");
		if(is_array($value)) throw new RuntimeException("Cannot convert array to string");
	
		if($seconds !== null) $seconds += time();
		
		// try for an update - most efficient
		$this->proxy->db->lock();
		$count = $this->executeStmt('update_key', array($value, $seconds, $key));
		if($count==1) {
			$this->proxy->db->unlock();
			return;
		}
	
		// if an object or a hash we delete and recreate
		if($count > 1) {
			$this->proxy->generic->del(array($key));
		}
	
		$this->executeStmt('insert_key', array($key, Plodis::TYPE_STRING, $value, $seconds));
		$this->proxy->db->unlock();
	}
	
	function mset($pairs) {
		$this->proxy->db->lock();
		foreach($pairs as $key=>$value) {
			$this->set($key, $value);
		}
		$this->proxy->db->unlock();
	}
	
	public function get($key) {
		$this->proxy->generic->gc();
		
		//$row = $this->fetchOne('select_key', array($key));
		
		// optomise this one to cut out alot of the abstraction
		$stmt = $this->proxy->db->cachedStmt($this->sql['select_key']);
		$stmt->execute(array($key));
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		
		if(!$row) {
			return null;
		}
		if($row[1] != Plodis::TYPE_STRING) throw new PlodisIncorrectKeyType;
		
		return $row[0];
	}
	
	function mget($keys) {
		$this->proxy->db->lock();
		foreach($keys as &$key) {
			try {
				$key = $this->get($key);
			} catch(PlodisIncorrectKeyType $e) {
				$key = null;
			}
		}
		$this->proxy->db->unlock();
		return $keys;
	}
	
	function incr($key) {
		return $this->incrby($key, 1);
	}
	
	function incrby($key, $increment) {
		$this->proxy->db->lock();
		$c = $this->executeStmt('incrby', array($increment, $key));
		
		// check for list/hash
		if($c > 1) {
			$this->proxy->db->unlock(true);
			throw new RuntimeException('Operation against a key holding the wrong kind of value');
		}
		
		if($c == 0) {
			$this->set($key, $increment);
		}
		
		if(self::$return_values) return (int)$this->get($key);
		$this->proxy->db->unlock();
	}
	
	function decr($key) {
		return $this->incrby($key, -1);
	}
	
	function decrby($key, $decrement) {
		return $this->incrby($key, -$decrement);
	}
	
	function append($key, $value) {
		$this->proxy->db->lock();
		$modified = $this->get($key) . $value;
		$this->set($key, $modified);
		$this->proxy->db->unlock();
		return strlen($modified);
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
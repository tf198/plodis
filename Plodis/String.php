<?php
class Plodis_String extends Plodis_Group {
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, expiry FROM plodis WHERE key=?',
		'insert_key' 	=> 'INSERT INTO plodis (key, item, expiry) VALUES (?, ?, ?)',
		'update_key'	=> 'UPDATE plodis SET item=?, expiry=? WHERE key=?',
		'delete_key'	=> 'DELETE FROM plodis WHERE key=?',
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
	
	function get($key) {
		$this->proxy->group_generic->gc();
		
		$row = $this->fetchOne('select_key', array($key));
		
		if(!$row) {
			return null;
		}
		return $row[0];
	}
	
	function mget($keys) {
		return array_map(array($this, 'get'), $keys);
	}
	
}
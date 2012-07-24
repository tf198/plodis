<?php
require_once "IRedis_String_2_6_0.php";

class Plodis_String extends Plodis_Group implements IRedis_String_2_6_0 {
	
	protected $sql = array(
		'select_key' 	=> 'SELECT item, type FROM <DB> WHERE pkey=?',
		'insert_key' 	=> 'INSERT INTO <DB> (pkey, type, item, expiry) VALUES (?, ?, ?, ?)',
		'update_key'	=> 'UPDATE <DB> SET item=?, expiry=?, field=NULL WHERE pkey=?',
		'delete_key'	=> 'DELETE FROM <DB> WHERE pkey=?',
		'incrby' 		=> 'UPDATE <DB> SET item=item + ? WHERE pkey=?',
		'strlen'		=> 'SELECT LENGTH(item), type FROM <DB> WHERE pkey=?',
		'append'		=> 'UPDATE <DB> SET item=item || ? WHERE pkey=?',
		'getbytes'		=> 'SELECT SUBSTR(item, ?, ?), type FROM <DB> WHERE pkey=?',
		'getbytes_end'	=> 'SELECT SUBSTR(item, ?), type FROM <DB> WHERE pkey=?',
		'setbytes'		=> 'UPDATE <DB> SET item=SUBSTR(item,0,?) || ? || substr(item,?) WHERE pkey=?', // not sure how efficient this is
	);
	
	function set($key, $value) {
		return $this->setex($key, null, $value);
	}
	
	function setex($key, $seconds, $value) {
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
	
	public function get($key, $gc=true) {
		if($gc)$this->proxy->generic->gc();
		$item = $this->fetchOne('select_key', array($key));
		 
		if($item) {
			if($item[1] != Plodis::TYPE_STRING) throw new PlodisIncorrectKeyType;
			return $item[0];
		} else {
			$this->proxy->generic->verify($key, 'string');
			return null;
		}
	}
	
	function mget($keys) {
		$this->proxy->db->lock();
		foreach($keys as &$key) {
			try {
				$key = $this->get($key, false);
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
		if ((int) $increment != $increment) throw new PlodisError("Value is not a valid integer");
		$result = $this->incrbyfloat($key, (int) $increment);
		return ($result === null) ? null : (int) $result;
	}
	
	function incrbyfloat($key, $increment) {
		if(!is_numeric($increment)) throw new PlodisError("Value is not a valid float");
		
		$this->proxy->generic->gc();
		$this->proxy->db->lock();
		$c = $this->executeStmt('incrby', array($increment, $key));
		
		// check for list/hash
		if($c > 1) {
			$this->proxy->db->unlock(true);
			throw new PlodisIncorrectKeyType;
		}
		
		if($c == 0) {
			$this->set($key, $increment);
			$result = (string) $increment;
		} else {
			$result = ($this->proxy->options['return_incr_values']) ? $this->get($key) : null;
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function decr($key) {
		return $this->incrby($key, -1);
	}
	
	function decrby($key, $decrement) {
		return $this->incrby($key, -$decrement);
	}
	
	function append($key, $value) {
		$this->proxy->db->lock();
		$c = $this->executeStmt('append', array($value, $key));
		if($c == 0) {
			$this->executeStmt('insert_key', array($key, Plodis::TYPE_STRING, $value, null));
			$result = strlen($value);
		} else {
			$result = ($this->proxy->options['return_counts']) ? $this->strlen($key) : -1;
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function bitcount($key, $start=null, $end=null) {
		$stmt = $this->proxy->db->cachedStmt($this->sql['select_key']);
		$stmt->execute(array($key));
		$stmt->bindColumn(1, $item, PDO::PARAM_LOB);
		$stmt->bindColumn(2, $type, PDO::PARAM_INT);
		$stmt->fetch(PDO::FETCH_BOUND);
		
		$count = 0;
		for($i=0, $c=strlen($item); $i<$c; $i++) {
			$n = ord($item{$i});
			while($n) {
				if($n & 1) $count++;
				$n /= 2;
			}
		}
		return $count;
	}
	
	function bitop($operation, $destkey, $keys) {
		$operation = strtoupper($operation);
		$this->proxy->db->lock();
		$data = $this->get($keys[0]);
		$l = strlen($data);
		if($operation == 'NOT') {
			$data = ~ $data;
		} else {
			for($i=1, $c=count($keys); $i<$c; $i++) {
				$item = $this->get($keys[$i]);
				if(strlen($item) > $l) $l += strlen($item);
				switch($operation) {
					case 'AND':
						$data = $data & $item;
						break;
					case 'OR':
						$data = $data | $item;
						break;
					case 'XOR':
						$data = $data ^ $item;
						break;
					default:
						throw new PlodisError("Unknown operation: {$operation}");
				}
			}
		}
		$this->set($destkey, $data);
		$this->proxy->db->unlock();
		return $l;
	}
	
	function getbyte($key, $offset) {
		$result = $this->fetchOne('getbytes', array($offset+1, 1, $key));
		
		if(!$result) {
			$this->proxy->generic->verify($key, 'string');
			return 0;
		}
		
		if($result[1] != Plodis::TYPE_STRING) throw new PlodisIncorrectKeyType;
		
		if($result[0] === '') return 0;
		return ord($result[0]);
	}
	
	function setbyte($key, $offset, $value) {
		$value = chr($value);
		$this->setrange($key, $offset, $value);
	}
	
	function getbit($key, $offset) {
		$byte = floor($offset/8);
		$bit = $offset % 8;
		
		$c = $this->getbyte($key, $byte) >> $bit;
		return $c & 1;
	}
	
	function setbit($key, $offset, $value) {
		$byte = floor($offset/8);
		$bit = $offset % 8;
	
		$this->proxy->db->lock();
		try {
			$b = $this->getbyte($key, $byte);
		} catch(PlodisIncorrectKeyType $e) {
			$this->proxy->db->unlock();
			throw $e;
		}
		//echo "-- {$b}\n";
		
		$current = ($b >> $bit) & 1;
		if($current != $value) {
			//var_dump(decbin($b));
			$n = $b | (1 << $bit);
			//var_dump(decbin($n));
			$this->setbyte($key, $byte, $n);
		}
		
		$this->proxy->db->unlock();
		return $current;
	}
	
	function getrange($key, $start, $end) {
		$this->proxy->db->lock();
		if($start>=0) $start++;
		
		if($end == -1) {
			$row = $this->fetchOne('getbytes_end', array($start, $key));
		} else {
			if($end < 0) $end = $this->strlen($key) + $end;
			$end = $end - $start + 2;
			$row = $this->fetchOne('getbytes', array($start, $end, $key));
		}
		
		if($row) {
			if($row[1] != Plodis::TYPE_STRING) throw new PlodisIncorrectKeyType;
			$result = $row[0];
		} else {
			$this->proxy->generic->verify($key, 'string');
			$result = null;
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function getset($key, $value) {
		$this->proxy->db->lock();
		try {
			$current = $this->get($key);
			$this->set($key, $value);
			$this->proxy->db->unlock();
			return $current;
		} catch(PlodisIncorrectKeyType $e) {
			$this->proxy->db->unlock();
			throw $e;
		}
	}
	
	function msetnx($pairs) {
		$this->proxy->db->lock();
		$sql = "SELECT pkey FROM <DB> WHERE pkey IN (?";
		for($i=1, $c=count($pairs); $i<$c; $i++) $sql .= ", ?";
		$sql .= ")";
		
		$stmt = $this->proxy->db->cachedStmt($sql);
		$stmt->execute(array_keys($pairs));
		$data = $stmt->fetch(PDO::FETCH_NUM);
		if($data) {
			$result = 0;
		} else {
			$stmt = $this->proxy->db->cachedStmt($this->sql['insert_key']);
			foreach($pairs as $key=>$value) {
				$stmt->execute(array($key, Plodis::TYPE_STRING, $value, null));
			}
			$result = 1;
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function psetex($key, $milliseconds, $value) {
		return $this->setex($key, $milliseconds / 1000, $value);
	}
	
	function setnx($key, $value) {
		$this->proxy->db->lock();
		$type = $this->proxy->generic->type($key);
		if($type === null) {
			$this->executeStmt('insert_key', array($key, Plodis::TYPE_STRING, $value, null));
			$result = 1;
		} else {
			$result = 0;
		}
		$this->proxy->db->unlock();
		return $result;
	}
	
	function setrange($key, $offset, $data) {
		$stmt = $this->proxy->db->cachedStmt($this->sql['setbytes']);
		$stmt->bindValue(1, $offset+1, PDO::PARAM_INT);
		$stmt->bindValue(2, $data, PDO::PARAM_LOB);
		$stmt->bindValue(3, $offset+1+strlen($data), PDO::PARAM_INT);
		$stmt->bindValue(4, $key, PDO::PARAM_STR);
		$stmt->execute();
		
		if($stmt->rowCount() == 0) {
			$data = str_repeat("\0", $offset) . $data;
			$stmt = $this->proxy->db->cachedStmt($this->sql['insert_key']);
			$stmt->bindValue(1, $key, PDO::PARAM_STR);
			$stmt->bindValue(2, Plodis::TYPE_STRING, PDO::PARAM_INT);
			$stmt->bindValue(3, $data, PDO::PARAM_LOB);
			$stmt->bindValue(4, null);
			$stmt->execute();
			return strlen($data);
		} else {
			return ($this->proxy->options['return_counts']) ? $this->strlen($key) : -1;
		}
	}
	
	function strlen($key) {
		$row = $this->fetchOne('strlen', array($key));
		if($row) {
			if($row[1] != Plodis::TYPE_STRING) throw new PlodisIncorrectKeyType;
			return (int) $row[0];
		} else {
			return 0;
		}
	}
}
<?php
class Plodis_Group {
	
	/**
	 * Parent object
	 * @var Plodis
	 */
	protected $proxy;
	
	protected $sql = array();
	
	protected $type = 'unknown';
	
	function __construct($proxy) {
		$this->proxy = $proxy;
	}
	
	protected function getSQL($sql) {
		if(isset($this->sql["{$sql}_{$this->proxy->db->driver}"])) {
			$sql = $this->sql["{$sql}_{$this->proxy->db->driver}"];
		} elseif(isset($this->sql[$sql])) {
			$sql = $this->sql[$sql];
		}
		return $sql;
	}
	
	protected function getStmt($sql) {
		return $this->proxy->db->cachedStmt($this->getSQL($sql));
	}
	
	protected function fetchOne($which, $params=array(), $column=null) {
		$stmt = $this->getStmt($which);
		$stmt->execute($params);
		$result = ($column === null) ? $stmt->fetch(PDO::FETCH_NUM) : $stmt->fetch(PDO::FETCH_COLUMN, $column);
		$stmt->closeCursor();
		return $result;
	}
	
	protected function fetchAll($which, $params=array(), $column=null) {
		$stmt = $this->getStmt($which);
		$stmt->execute($params);
		if($column !== null) {
			return $stmt->fetchAll(PDO::FETCH_COLUMN, $column);
		} else {
			return $stmt->fetchAll(PDO::FETCH_NUM);
		}
	}
	
	protected function executeStmt($which, $params=array()) {
		$stmt = $this->getStmt($which);
		$stmt->execute($params);
		return $stmt->rowCount();
	}
	
	public function pluck(&$arr, $col) {
		foreach($arr as &$row) $row = $row[$col];
		return $arr;
	}
	
	public function verify($key, $levels=0) {
		if($this->proxy->options['validation_checks'] == false) return;
		$type = $this->proxy->generic->type($key);
		
		if($type === null) return;
		if($type != $this->type) {
			while($levels--) $this->proxy->db->unlock();
			throw new PlodisIncorrectKeyType;
		}
	}
	
	public function countItems($key, $which, $params) {
		$this->proxy->generic->gc();
		$this->proxy->db->lock();
		$c = $data = $this->fetchOne($which, $params, 0);
		if($c == 0) $this->verify($key, 1);
		$this->proxy->db->unlock();
		return (int) $c;
	}
	
	protected function fetchOneGCVerify($key, $which, $params, $type_field=1, $target_field=null, $default=null) {
		$this->proxy->generic->gc();
		$item = $this->fetchOne($which, $params);
		 
		if($item) {
			if(Plodis::$types[$item[$type_field]] != $this->type) throw new PlodisIncorrectKeyType;
		} else {
			$this->verify($key);
		}
		if($target_field === null) {
			return $item;
		} else {
			return ($item) ? $item[$target_field] : $default;
		}
	}
	
	protected function fetchAllGCVerify($key, $which, $params, $type_field=1, $target_field=null) {
		$this->proxy->generic->gc();
		$all = $this->fetchAll($which, $params);
		
		if($all) {
			if(Plodis::$types[$all[0][$type_field]] != $this->type) throw new PlodisIncorrectKeyType;
		} else {
			$this->verify($key);
		}
		return ($target_field === null) ? $all : $this->pluck($all, $target_field);
	}
}
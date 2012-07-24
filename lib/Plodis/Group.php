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
	
	public function countItems($which, $params, $key) {
		$this->proxy->generic->gc();
		$this->proxy->db->lock();
		$c = $data = $this->fetchOne($which, $params, 0);
		if($c == 0) $this->proxy->generic->verify($key, $this->type, 1);
		$this->proxy->db->unlock();
		return (int) $c;
	}
}
<?php
class Plodis_Group {
	
	/**
	 * Parent object
	 * @var Plodis
	 */
	protected $proxy;
	
	protected $sql = array();
	
	function __construct($proxy) {
		$this->proxy = $proxy;
	}
	
	protected function getStmt($which) {
		if(!isset($this->sql[$which])) throw new RuntimeException("No SQL for '{$which}'");
		$sql = $this->sql[$which];
		
		return $this->proxy->cachedStmt($sql);
	}
	
	protected function fetchOne($which, $params=array()) {
		$stmt = $this->getStmt($which);
		$stmt->execute($params);
		$result = $stmt->fetch(PDO::FETCH_NUM);
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
}
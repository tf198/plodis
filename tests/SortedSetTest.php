<?php
require_once "BaseTest.php";

class SortedSetTest extends BaseTest {
	
	function testZAdd() {
		$this->assertSame(1, $this->db->zadd('test1', 1, 'one'));
		$this->assertSame(0, $this->db->zadd('test1', 1, 'one'));
		$this->assertSame(1, $this->db->zadd('test1', 2, 'two'));
		
		$this->assertSame(2, $this->db->zcard('test1'));
		
		$this->db->set('test2', 'one');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'zadd', 'test2', 'two');
	}
	
}
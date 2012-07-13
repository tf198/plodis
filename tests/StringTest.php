<?php
require_once "BaseTest.php";

class StringTest extends BaseTest {
	
	function testGet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame('1', $this->db->get('test1'));
		$this->assertSame('two', $this->db->get('test2'));
		
		// expired
		$this->db->expire('test1', -10);
		$this->assertSame(null, $this->db->get('test1'));
		
		// non-existent
		$this->assertSame(null, $this->db->get('non-existant'));
		
		// list value
		$this->db->rpush('test3', 'one');
		$this->assertThrows('RuntimeException: Operation against a key', $this->db, 'get', 'test3');
	}
	
	function testSet() {
		$this->db->set('test1', null);
		$this->db->set('test2', 1);
		$this->db->set('test3', 'two');
		$this->db->set('test4', json_encode(new stdClass()));
		
		$this->assertThrows('RuntimeException: Cannot convert object to string', $this->db, 'set', 'test5', $this);
		$this->assertThrows('RuntimeException: Cannot convert array to string', $this->db, 'set', 'test5', array(1,2,3));
	}
	
	function testMGet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame(array('1', 'two'), $this->db->mget('test1', 'test2'));
		
		$this->assertSame(array('1', null, 'two'), $this->db->mget('test1', 'test3', 'test2'));
		
		$this->db->rpush('test3', 'one');
		$this->assertSame(array('1', null), $this->db->mget('test1', 'test3'));
		
	}
	
	function testMSet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertEquals(array('test1', 'test2'), $this->db->keys());
	}
	
	function testIncr() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame(null, $this->db->incr('test1'));
		$this->assertSame('2', $this->db->get('test1'));
		
		// strict behavior
		Plodis_String::$return_values = true;
		$this->assertSame(3, $this->db->incr('test1'));
		Plodis_String::$return_values = false;
		
		//$this->assertThrows('x', $this->db, 'incr', 'test2');
		//$this->db->incr('test2');
		//var_dump($this->db->get('test2'));
		$this->markTestIncomplete();
	}
	
	function testAppend() {
		$this->db->set('test1', 'one');
		
		// normal behaviour
		$this->assertSame(6, $this->db->append('test1', 'two'));
		$this->assertSame('onetwo', $this->db->get('test1'));
		
		// creation
		$this->assertSame(3, $this->db->append('test2', 'two'));
		$this->assertSame('two', $this->db->get('test2'));
	}
}
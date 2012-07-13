<?php
require_once 'BaseTest.php';

class ListTest extends BaseTest {
	
	function testRPush() {
		$this->assertSame(-1, $this->db->rpush('test1', 'one'));
		$this->assertSame(-1, $this->db->rpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(-1, $this->db->rpush('test1', 'three', 'four'));
		
		// strict behavior
		Plodis_List::$return_counts = true;
		$this->assertSame(5, $this->db->rpush('test1', 'five'));
		$this->assertSame(7, $this->db->rpush('test1', 'six', 'seven'));
		Plodis_List::$return_counts = false;
		
		// check result
		$this->assertSame(array('one', 'two', 'three', 'four', 'five', 'six', 'seven'), $this->db->lrange('test1', 0, -1));
		
		$this->db->linsert('test1', 'BEFORE', 'four', 'eight');
		$this->db->rpush('test1', 'nine');
		$this->assertSame(array('one', 'two', 'three', 'eight', 'four', 'five', 'six', 'seven', 'nine'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLPush() {
		$this->assertSame(-1, $this->db->lpush('test1', 'one'));
		$this->assertSame(-1, $this->db->lpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(-1, $this->db->lpush('test1', 'three', 'four'));
		
		// strict behavior
		Plodis_List::$return_counts = true;
		$this->assertSame(5, $this->db->lpush('test1', 'five'));
		$this->assertSame(7, $this->db->lpush('test1', 'six', 'seven'));
		Plodis_List::$return_counts = false;
		
		// check result
		$this->assertSame(array('seven', 'six', 'five', 'four', 'three', 'two', 'one'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLRange() {
		$this->assertSame(array(), $this->db->lrange('test1', 0, 0));
		
		$this->db->rpush('test1', 'one', 'two', 'three', 'four');
		
		$this->assertSame(array('one', 'two'), $this->db->lrange('test1', 0, 1));
		$this->assertSame(array('one', 'two', 'three'), $this->db->lrange('test1', 0, 2));
		$this->assertSame(array('two', 'three'), $this->db->lrange('test1', 1, 2));
		
		// start beyond end of list
		$this->assertSame(array(), $this->db->lrange('test1', 5, 7));
		
		// end beyond end of list
		$this->assertSame(array('three', 'four'), $this->db->lrange('test1', 2, 12));
		
		// negative indexes
		$this->assertSame(array('three', 'four'), $this->db->lrange('test1', -2, -1));
		$this->assertSame(array('three', 'four'), $this->db->lrange('test1', 2, -1));
		$this->assertSame(array('two', 'three'), $this->db->lrange('test1', 1, -2));
		$this->assertSame(array('two'), $this->db->lrange('test1', 1, -3));
		
		// crossover
		$this->assertSame(array(), $this->db->lrange('test1', 2, 1));
		$this->assertSame(array(), $this->db->lrange('test1', 1, -4));
		
	}
	
	function testLIndex() {
		$this->assertSame(null, $this->db->lindex('test1', 0));
		
		$this->db->rpush('test1', 'one', 'two', 'three', 'four');
		
		$this->assertSame('one', $this->db->lindex('test1', 0));
		$this->assertSame('three', $this->db->lindex('test1', 2));
		$this->assertSame('four', $this->db->lindex('test1', 3));
		
		// out of range
		$this->assertSame(null, $this->db->lindex('test1', 4));
		
		// negative indices
		$this->assertSame('four', $this->db->lindex('test1', -1));
		$this->assertSame('two', $this->db->lindex('test1', -3));
		$this->assertSame(null, $this->db->lindex('test1', -5));
	}
	
	function testLInsert() {
		$this->db->rpush('test1', 'one', 'two', 'three', 'three', 'four');
		
		$this->assertSame(-1, $this->db->linsert('test1', 'before', 'two', 'five'));
		$this->assertSame(array('one', 'five', 'two', 'three', 'three', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->db->linsert('test1', 'after', 'three', 'six');
		$this->assertSame(array('one', 'five', 'two', 'three', 'three', 'six', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->db->linsert('test1', 'before', 'one', 'seven');
		$this->assertSame(array('seven', 'one', 'five', 'two', 'three', 'three', 'six', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->db->linsert('test1', 'after', 'six', 'eight');
		$this->assertSame(array('seven', 'one', 'five', 'two', 'three', 'three', 'six', 'eight', 'four'), $this->db->lrange('test1', 0, -1));
		
		// check that push commands still end up in the right place
		$this->db->rpush('test1', 'nine');
		$this->db->lpush('test1', 'ten');
		$this->assertSame(array('ten', 'seven', 'one', 'five', 'two', 'three', 'three', 'six', 'eight', 'four', 'nine'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLRem() {
		$this->db->rpush('test1', 'one', 'two', 'four', 'three', 'one', 'one', 'two', 'two', 'three', 'three', 'three', 'four');
		
		$this->assertSame(3, $this->db->lrem('test1', 0, 'two'));
		$this->assertSame(9, $this->db->llen('test1'));
		
		$this->assertSame(2, $this->db->lrem('test1', 2, 'three'));
		$this->assertSame(array('one', 'four', 'one', 'one', 'three', 'three', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->assertSame(2, $this->db->lrem('test1', -2, 'one'));
		$this->assertSame(array('one', 'four', 'three', 'three', 'four'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLPop() {
		$this->assertSame(null, $this->db->lpop('test1'));
		$this->db->rpush('test1', 'one', 'two', 'three');
		
		$this->assertSame('one', $this->db->lpop('test1'));
		$this->assertSame('two', $this->db->lpop('test1'));
		
		$this->db->rpush('test1', 'four');
		
		$this->assertSame('three', $this->db->lpop('test1'));
		$this->assertSame('four', $this->db->lpop('test1'));
		$this->assertSame(null, $this->db->lpop('test1'));
	}
	
	function testRPop() {
		$this->assertSame(null, $this->db->rpop('test1'));
		$this->db->rpush('test1', 'one', 'two', 'three');
		
		$this->assertSame('three', $this->db->rpop('test1'));
		
		$this->db->rpush('test1', 'four');
		
		$this->assertSame('four', $this->db->rpop('test1'));
		$this->assertSame('two', $this->db->rpop('test1'));
		$this->assertSame('one', $this->db->rpop('test1'));
		$this->assertSame(null, $this->db->rpop('test1'));
	}
	
	function testBLPop() {
		$this->markTestIncomplete();
	}
	
	function testBRPop() {
		$this->markTestIncomplete();
	}
}
<?php
require_once 'Plodis.php';

class ListTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Redish object
	 * @var Redish
	 */
	public $db;
	
	function setUp() {
		$this->db = new Plodis(new PDO('sqlite::memory:'));
		// add some data so we are not fresh
		$this->db->mset(array('a' => 'a', 'b' => 'b'));
	}
	
	function testRPush() {
		$this->assertSame(null, $this->db->rpush('test1', 'one'));
		$this->assertSame(null, $this->db->rpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(null, $this->db->rpush('test1', 'three', 'four'));
		
		// strict behavior
		Plodis::$strict = true;
		$this->assertSame(5, $this->db->rpush('test1', 'five'));
		$this->assertSame(7, $this->db->rpush('test1', 'six', 'seven'));
		Plodis::$strict = false;
		
		// check result
		$this->assertSame(array('one', 'two', 'three', 'four', 'five', 'six', 'seven'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLPush() {
		$this->assertSame(null, $this->db->lpush('test1', 'one'));
		$this->assertSame(null, $this->db->lpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(null, $this->db->lpush('test1', 'three', 'four'));
		
		// strict behavior
		Plodis::$strict = true;
		$this->assertSame(5, $this->db->lpush('test1', 'five'));
		$this->assertSame(7, $this->db->lpush('test1', 'six', 'seven'));
		Plodis::$strict = false;
		
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
		$this->db->rpush('test1', 'one', 'two', 'three', 'four');
		
		$this->assertSame(null, $this->db->linsert('test1', 'before', 'two', 'five'));
		$this->assertSame(array('one', 'five', 'two', 'three', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->assertSame(null, $this->db->linsert('test1', 'after', 'three', 'six'));
		$this->assertSame(array('one', 'five', 'two', 'three', 'six', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->assertSame(null, $this->db->linsert('test1', 'before', 'one', 'seven'));
		$this->assertSame(array('seven', 'one', 'five', 'two', 'three', 'six', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->assertSame(null, $this->db->linsert('test1', 'after', 'six', 'eight'));
		$this->assertSame(array('seven', 'one', 'five', 'two', 'three', 'six', 'eight', 'four'), $this->db->lrange('test1', 0, -1));
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
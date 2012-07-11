<?php
require_once 'Redish.php';

class ListTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Redish object
	 * @var Redish
	 */
	public $db;
	
	function setUp() {
		$this->db = new Redish(new PDO('sqlite::memory:'));
	}
	
	function testRPush() {
		$this->assertSame(-1, $this->db->rpush('test1', 'one'));
		$this->assertSame(-1, $this->db->rpush('test1', 'two'));
		
		$this->assertSame(2, $this->db->llen('test1'));
		
		// check they went in the right order
		$this->assertSame(array('one', 'two'), $this->db->lrange('test1', 0, 100));
	}
	
	function testLRange() {
		$this->db->rpush('test1', 'one', 'two', 'three', 'four');
		
		$this->assertSame(array('one', 'two'), $this->db->lrange('test1', 0, 1));
		$this->assertSame(array('one', 'two', 'three'), $this->db->lrange('test1', 0, 2));
		$this->assertSame(array('two', 'three'), $this->db->lrange('test1', 1, 1));
	}
}
<?php
require_once "Redish.php";

class StoreTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Redish object
	 * @var Redish
	 */
	public $db;
	
	function setUp() {
		$this->db = new Redish(new PDO('sqlite::memory:'));
	}
	
	function testGet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame('1', $this->db->get('test1'));
		$this->assertSame('two', $this->db->get('test2'));
		
		$this->assertSame(null, $this->db->get('non-existant'));
	}
	
	function testSet() {
		$this->db->set('test1', null);
		$this->db->set('test2', 1);
	}
	
	function testDel() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertEquals(1, $this->db->del('test1'));
		$this->assertEquals(array('test2'), $this->db->keys());
		
		$this->assertEquals(0, $this->db->del('bad1', 'bad2'));
		$this->assertEquals(array('test2'), $this->db->keys());
	}
	
	function testExists() {
		$this->db->set('test1', 'one');
		
		$this->assertSame(1, $this->db->exists('test1'));
		$this->assertSame(0, $this->db->exists('test2'));
	}
}
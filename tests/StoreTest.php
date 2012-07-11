<?php
require_once "Plodis.php";

class StoreTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Redish object
	 * @var Redish
	 */
	public $db;
	
	function setUp() {
		$this->db = new Plodis(new PDO('sqlite::memory:'));
	}
	
	function testGet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame('1', $this->db->get('test1'));
		$this->assertSame('two', $this->db->get('test2'));
		
		// expired
		$this->db->expire('test1', -10);
		$this->assertSame(null, $this->db->get('test1'));
		
		// non-existent
		$this->assertSame(null, $this->db->get('non-existant'));
	}
	
	function testSet() {
		$this->db->set('test1', null);
		$this->db->set('test2', 1);
		$this->db->set('test3', 'two');
		$this->db->set('test4', json_encode($this));
		
		try {
			$this->db->set('test5', $this);
			$this->fail();
		} catch(RuntimeException $e) {
			$this->assertEquals('Cannot convert object to string', $e->getMessage());
		}
		
		try {
			$this->db->set('test5', array(1, 2, 3));
			$this->fail();
		} catch(RuntimeException $e) {
			$this->assertEquals('Cannot convert array to string', $e->getMessage());
		}
	}
	
	function testMGet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame(array('1', 'two'), $this->db->mget('test1', 'test2'));
		
		$this->assertSame(array('1', null, 'two'), $this->db->mget('test1', 'test3', 'test2'));
	}
	
	function testMSet() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertEquals(array('test1', 'test2'), $this->db->keys());
	}
	
	function testDel() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame(1, $this->db->del('test1'));
		$this->assertSame(array('test2'), $this->db->keys());
		
		$this->assertSame(0, $this->db->del('bad1', 'bad2'));
		$this->assertSame(array('test2'), $this->db->keys());
	}
	
	function testExists() {
		$this->db->set('test1', 'one');
		
		$this->assertSame(1, $this->db->exists('test1'));
		$this->assertSame(0, $this->db->exists('test2'));
		
		// expired
		$this->db->expire('test1', -10);
		$this->assertSame(0, $this->db->exists('test1'));
	}
	
	function testKeys() {
		// no keys
		$this->assertSame(array(), $this->db->keys());
		
		$this->db->mset(array('one' => 1, 'two' => 'two', 'three' => 'iii'));
		
		// normal match
		$this->assertSame(array('one', 'two', 'three'), $this->db->keys());
		
		// fuzzy matches
		$this->assertSame(array('two'), $this->db->keys('two')); // silly but valid
		$this->assertSame(array('two', 'three'), $this->db->keys('t*'));
		$this->assertSame(array('one', 'three'), $this->db->keys('*e*'));
		$this->assertSame(array(), $this->db->keys('z*'));
	}
	
	function testAppend() {
		$this->db->set('test1', 'one');
		
		// normal behaviour
		$this->assertSame(6, $this->db->append('test1', 'two'));
		
		// creation
		$this->assertSame(3, $this->db->append('test2', 'two'));
	}
}
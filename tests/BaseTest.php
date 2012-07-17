<?php
require_once "Plodis.php";

abstract class BaseTest extends PHPUnit_Framework_TestCase {

	/**
	 * Plodis object
	 * @var Plodis
	 */
	public $db;

	function setUp() {
		$this->db = new Plodis(new PDO('sqlite::memory:'));
		
		$this->db->mset(array('check_1' => 'one', 'check_2' => 'two'));
		$this->db->lpush('check_3', 'one', 'two');
		$this->db->rpush('check_3', 'three', 'four');
		$this->db->hset('check_4', 'one', '1');
		$this->db->hset('check_4', 'two', '2');
	}
	
	function tearDown() {
		$this->assertSame(array('one', 'two'), $this->db->mget('check_1', 'check_2'));
		$this->assertSame(array('two', 'one', 'three', 'four'), $this->db->lrange('check_3', 0, -1));
		$this->assertSame(array('1', '2'), $this->db->hvals('check_4'));
	}
	
	function assertThrows($message, $obj, $method, $param) {
		$params = array_slice(func_get_args(), 3);
		
		try {
			call_user_func_array(array($obj, $method), $params);
			$this->fail('Should have throw an exception');
		} catch(PHPUnit_Framework_AssertionFailedError $a) {
			throw $a;
		} catch(Exception $e) {
			$result = get_class($e) . ": " . $e->getMessage();
			$this->assertStringStartsWith($message, $result);
		}
	}
}
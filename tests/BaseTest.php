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
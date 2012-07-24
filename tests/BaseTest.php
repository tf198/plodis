<?php
//if(!defined('BACKEND')) define('BACKEND', 'PLODIS');

if(BACKEND == 'PLODIS' || BACKEND == 'MYSQL') {
	require_once "Plodis.php";
	Plodis::$log_level = LOG_WARNING;
} elseif(BACKEND == 'PREDIS') {
	require_once "predis.phar";
}

abstract class BaseTest extends PHPUnit_Framework_TestCase {

	/**
	 * Plodis object
	 * @var Plodis
	 */
	public $db;

	public $check_keys = array('check_1', 'check_2', 'check_3', 'check_4', 'check_5');
	
	public $skip_checks = false;
	
	function setUp() {
	switch(BACKEND) {
			case 'PLODIS':
				$this->db = new Plodis(':memory:');
				break;
			case 'PREDIS':
				$this->db = new Predis\Client(PREDIS_SERVER, array('profile' => '2.6'));
				$this->db->flushall();
				break;
			case 'MYSQL':
				$pdo = new PDO(MYSQL_DSN, MYSQL_USER, MYSQL_PASS);
				$this->db = new Plodis($pdo, true, false);
				$this->db->flushdb();
				break;
			default:
				throw new Exception("No backend defined: " . BACKEND);
		}
		
		$this->db->mset(array('check_1' => 'one', 'check_2' => 'two'));
		$this->db->lpush('check_3', 'one', 'two');
		$this->db->rpush('check_3', 'three', 'four');
		$this->db->hset('check_4', 'one', '1');
		$this->db->hset('check_4', 'two', '2');
		$this->db->sadd('check_5', 'a', 'b', 'c');
	}
	
	function tearDown() {
		if($this->skip_checks) return;
		if(BACKEND == 'PREDIS') return;
		
		$this->assertSame(array('one', 'two'), $this->db->mget('check_1', 'check_2'));
		$this->assertSame(array('two', 'one', 'three', 'four'), $this->db->lrange('check_3', 0, -1));
		$this->assertSame(array('1', '2'), $this->db->hvals('check_4'));
		$this->assertSame(array('a', 'b', 'c'), $this->db->smembers('check_5'));
		$this->assertSame(0, $this->db->db->getLockCount(), "Transaction locks remaining");
	}
	
	static function assertSame($expected, $actual, $message='') {
		if(BACKEND == 'PREDIS') {
			// Predis returns bools instead of 0|1
			if(is_bool($actual)) $actual = ($actual) ? 1 : 0;
			// Redis ordering is different - just check the contents
			if(is_array($actual)) {
				sort($expected);
				sort($actual);
			}
		}
		return parent::assertSame($expected, $actual, $message);
	}
	
	static function assertSameItems($expected, $actual, $message='') {
		sort($expected);
		sort($actual);
		return parent::assertSame($expected, $actual, $message);
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
			if(BACKEND == 'PREDIS') return;
			$this->assertStringStartsWith($message, $result);
		}
	}
}
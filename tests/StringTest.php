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
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'get', 'test3');
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
		
		$this->assertEquals(array('test1', 'test2'), $this->db->keys('test*'));
	}
	
	function testIncr() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
		
		$this->assertSame(2, $this->db->incr('test1'));
		
		if(BACKEND != 'PLODIS') return;
		
		$this->db->setOption('return_incr_values', false);
		$this->assertSame(null, $this->db->incr('test1'));
		$this->db->setOption('return_incr_values', true);
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
	/* 2.6.0
	function testIncrByFloat() {
		$this->db->set('test1', '7.5');
		
		$this->assertSame('8.3', $this->db->incrbyfloat('test1', 0.8));
		$this->assertSame('8.3', $this->db->get('test1'));
		$this->assertSame('5.9', $this->db->incrbyfloat('test1', -2.4));
		
		$this->assertSame('4.5', $this->db->incrbyfloat('test2', 4.5));
		$this->assertSame('4.5', $this->db->get('test2'));
		
		$this->assertEquals('205.9', $this->db->incrbyfloat('test1', '2.0e2'));
		
		$this->assertThrows('PlodisError: Value is not a valid float', $this->db, 'incrbyfloat', 'test1', "test");
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'incrbyfloat', 'check_3', 0.3);
	}
	*/
	function testIncrBy() {
		$this->db->set('test1', 7);
	
		$this->assertSame(9, $this->db->incrby('test1', 2));
		$this->assertSame('9', $this->db->get('test1'));
		$this->assertSame(6, $this->db->incrby('test1', -3));
	
		$this->assertSame(4, $this->db->incrby('test2', "4"));
		$this->assertSame('4', $this->db->get('test2'));
	
		$this->assertThrows('PlodisError: Value is not a valid integer', $this->db, 'incrby', 'test1', '2.0e2');
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'incrby', 'check_3', 12);
	}
	
	function testSetNX() {
		$this->assertSame(1, $this->db->setnx('test1', 'one'));
		$this->assertSame(0, $this->db->setnx('test1', 'two'));
		$this->assertSame('one', $this->db->get('test1'));
		
		// setnx can be used on other types
		$this->assertSame(0, $this->db->setnx('check_3', 12));
	}
	
	function testStrLen() {
		$this->db->set('test1', 'testing');
		
		$this->assertSame(7, $this->db->strlen('test1'));
		$this->assertSame(0, $this->db->strlen('test2'));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'strlen', 'check_3');
	}
	
	function testMSetNX() {
		$this->db->set('test1', 'testing');
		
		$this->assertSame(0, $this->db->msetnx(array('test1' => 'one', 'test2' => 'two')));
		$this->assertSame(1, $this->db->msetnx(array('test2' => 'two', 'test3' => 'three')));
		
		$this->assertSame('three', $this->db->get('test3'));
	}
	
	function testGetSet() {
		$this->db->set('test1', 'one');
		
		$this->assertSame('one', $this->db->getset('test1', 'two'));
		$this->assertSame('two', $this->db->get('test1'));
		
		$this->assertSame(null, $this->db->getset('test2', 'three'));
		$this->assertSame('three', $this->db->get('test2'));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'getset', 'check_3', 'one');
	}
	
	function testGetRange() {
		$this->db->set('test1', 'This is a string');
		
		$this->assertSame('This', $this->db->getrange('test1', 0, 3));
		$this->assertSame('ing', $this->db->getrange('test1', -3, -1));
		$this->assertSame('This is a string', $this->db->getrange('test1', 0, -1));
		$this->assertSame('string', $this->db->getrange('test1', 10, 100));
		$this->assertSame('his', $this->db->getrange('test1', 1, 3));
		$this->assertSame('is a str', $this->db->getrange('test1', 5, -4));
		$this->assertSame('s', $this->db->getrange('test1', 3, 3));
	}
	/* 2.6.0
	function testSetBit() {
		$this->db->setbit('test1', 0, 1);
		$this->db->setbit('test1', 5, 1);
		$this->db->setbit('test1', 6, 1);
		$this->db->setbit('test1', 14, 1);
		
		if(BACKEND != 'PLODIS') $this->markTestIncomplete();
		$this->assertSame(97, $this->db->string->getbyte('test1', 0));
		$this->assertSame(64, $this->db->string->getbyte('test1', 1));
		
		$this->assertSame('a@', $this->db->get('test1'));
	}
	
	function testGetBit() {
		if(BACKEND == 'MYSQL') $this->markTestIncomplete();
		$this->setByte('test1', 0, 97);
		$this->setByte('test1', 1, 63);
		$this->assertSame(1, $this->db->getbit('test1', 0));
		$this->assertSame(0, $this->db->getbit('test1', 1));
		$this->assertSame(0, $this->db->getbit('test1', 2));
		$this->assertSame(0, $this->db->getbit('test1', 3));
		$this->assertSame(0, $this->db->getbit('test1', 4));
		$this->assertSame(1, $this->db->getbit('test1', 5));
		$this->assertSame(1, $this->db->getbit('test1', 6));
		$this->assertSame(0, $this->db->getbit('test1', 7));
		$this->assertSame(1, $this->db->getbit('test1', 8));
		$this->assertSame(1, $this->db->getbit('test1', 9));
		$this->assertSame(1, $this->db->getbit('test1', 10));
		$this->assertSame(1, $this->db->getbit('test1', 11));
		$this->assertSame(1, $this->db->getbit('test1', 12));
		$this->assertSame(1, $this->db->getbit('test1', 13));
		$this->assertSame(0, $this->db->getbit('test1', 14));
		$this->assertSame(0, $this->db->getbit('test1', 15));
		
		$this->assertSame(0, $this->db->getbit('test2', 12));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'getbit', 'check_3', 3);
	}
	
	function testInverse() {
		if(BACKEND != 'PLODIS') $this->markTestSkipped();
		$this->db->string->setbyte('test1', 1, 12);
		$this->assertSame(0, $this->db->string->getbyte('test1', 0));
		$this->assertSame(12, $this->db->string->getbyte('test1', 1));
	}
	
	function testGetByte() {
		if(BACKEND != 'PLODIS') $this->markTestSkipped();
		$this->db->set('test1', 'abcde');
		$this->assertSame(98, $this->db->string->getbyte('test1', 1));
		$this->assertSame(101, $this->db->string->getbyte('test1', 4));
		$this->assertSame(0, $this->db->string->getbyte('test1', 5));
		$this->assertSame(0, $this->db->string->getbyte('test2', 2));
	}
	
	function testSetByte() {
		if(BACKEND != 'PLODIS') $this->markTestSkipped();
		$this->db->set('test1', 'abcde');
		$this->db->string->setbyte('test1', 2, 90);
		$this->assertSame('abZde', $this->db->get('test1'));
		
		$this->db->string->setbyte('test2', 2, 90);
		$this->assertSame("\0\0Z", $this->db->get('test2'));
	}
	
	function testBitCount() {
		if(BACKEND == 'MYSQL') $this->markTestIncomplete();
		$this->setByte('test1', 0, 97);
		$this->assertSame(3, $this->db->bitcount('test1'));
		$this->setByte('test1', 1, 67);
		$this->assertSame(6, $this->db->bitcount('test1'));
	}
	
	function setByte($key, $byte, $value) {
		if(BACKEND == 'PLODIS') {
			$this->db->string->setbyte($key, $byte, $value);
		} else {
			$i=0;
			$offset = $byte * 8;
			while($value) {
				if($value & 1) $this->db->setbit($key, $offset + $i, 1);
				$value >>= 1;
				$i++;
			}
		}
	}
	
	function testBitOp() {
		if(BACKEND != 'PLODIS') $this->markTestIncomplete();
		$this->setByte('test1', 0, 97);
		$this->setByte('test2', 0, 63);
		$this->setByte('test3', 0, 73);
		
		$this->assertSame(1, $this->db->bitop('not', 'res1', 'test1'));
		$this->assertSame(ord(~ 'a'), $this->db->string->getbyte('res1', 0));
		
		$this->assertSame(1, $this->db->bitop('and', 'res2', 'test1', 'test2'));
		$this->assertSame(ord('a' & '?'), $this->db->string->getbyte('res2', 0));
		
		$this->assertSame(1, $this->db->bitop('and', 'res3', 'test1', 'test2', 'test3'));
		$this->assertSame(ord('a' & '?' & 'C'), $this->db->string->getbyte('res3', 0));
		
		$this->assertSame(1, $this->db->bitop('or', 'res4', 'test1', 'test2'));
		$this->assertSame(ord('a' | '?'), $this->db->string->getbyte('res4', 0));
		
		$this->assertSame(1, $this->db->bitop('xor', 'res5', 'test1', 'test2'));
		$this->assertSame(ord('a' ^ '?'), $this->db->string->getbyte('res5', 0));
	}
	*/
	function testSetEx() {
		$this->db->setex('test1', 23, 'one');
		$this->assertEquals(23, $this->db->ttl('test1'));
	}
	
}
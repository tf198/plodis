<?php
require_once "BaseTest.php";

class HashTest extends BaseTest {
	
	function testHSet() {
		$this->assertSame(1, $this->db->hset('test1', 'user', 'bob'));
		$this->assertSame('bob', $this->db->hget('test1', 'user'));
		
		$this->assertSame(0, $this->db->hset('test1', 'user', 'andy'));
		$this->assertSame('andy', $this->db->hget('test1', 'user'));
	}
	
	function testHGet() {
		$this->assertSame(null, $this->db->hget('test1', 'user'));
		
		$this->db->hset('test1', 'user', 'bob');
		$this->assertSame('bob', $this->db->hget('test1', 'user'));
		
		$this->assertSame(null, $this->db->hget('test1', 'name'));
		
		$this->assertSame(null, $this->db->hget('test2', 'name'));
	}
	
	function testHDel() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
		
		$this->assertSame(1, $this->db->hdel('test1', 'firstname'));
		
		$this->assertSame(3, $this->db->hdel('test1', 'lastname', 'age', 'user', 'fav_cheese'));
	}
	
	function testHMGet() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
		
		$this->assertSame(array('Brown', 'bob', '23'), $this->db->hmget('test1', 'lastname', 'user', 'age'));
		$this->assertSame(array(null, "Bob", null), $this->db->hmget('test1', 'fav_cheese', 'firstname', 'height'));
		
		$this->assertSame(array(null, null), $this->db->hmget('test2', 'one', 'two'));
	}
	
	function testHExists() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
	
		$this->assertSame(1, $this->db->hexists('test1', 'user'));
		$this->assertSame(0, $this->db->hexists('test1', 'height'));
		$this->assertSame(0, $this->db->hexists('test2', 'user'));
	}
	
	function testHLen() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
		$this->db->hmset('test2', array('one' => 'a', 'two' => 'b'));
	
		$this->assertSame(4, $this->db->hlen('test1'));
		$this->assertSame(2, $this->db->hlen('test2'));
		
		$this->assertSame(0, $this->db->hlen('test3'));
	}
	
	function testHSetNX() {
		$this->assertSame(1, $this->db->hsetnx('test1', 'user', 'bob'));
		$this->assertSame('bob', $this->db->hget('test1', 'user'));
	
		$this->assertSame(0, $this->db->hsetnx('test1', 'user', 'andy'));
		$this->assertSame('bob', $this->db->hget('test1', 'user'));
	}
	
	function testHGetAll() {
		$test1 = array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => '23');
		$this->db->hmset('test1', $test1);
		$this->assertSame($test1, $this->db->hgetall('test1'));
		
		$this->assertSame(array(), $this->db->hgetall('test2'));
	}
	
	function testHVals() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
		
		$this->assertSame(array('bob', 'Bob', 'Brown', '23'), $this->db->hvals('test1'));
		$this->assertSame(array(), $this->db->hvals('test2'));
	}
	
	function testHKeys() {
		$this->db->hmset('test1', array('user' => 'bob', 'firstname' => 'Bob', 'lastname' => 'Brown', 'age' => 23));
	
		$this->assertSame(array('user', 'firstname', 'lastname', 'age'), $this->db->hkeys('test1'));
		$this->assertSame(array(), $this->db->hkeys('test2'));
	}
	
	function testHIncrBy() {
		$this->db->hmset('test1', array('one' =>  1, 'two' => '2', 'three' => 'three'));
		
		$this->assertSame(5, $this->db->hincrby('test1', 'one', 4));
		$this->assertSame(8, $this->db->hincrby('test1', 'two', 6));
		$this->assertSame(10, $this->db->hincrby('test1', 'two', 2));
		
		$this->assertSame(3, $this->db->hincrby('test1', 'four', 3));
		
		$this->assertThrows('PlodisError: ERR value is not an integer or out of range', $this->db, 'hincrby', 'test1', 'four', 2.3);
	}
	
	function testHIncrByFloat() {
		$this->db->hmset('test1', array('one' =>  1, 'two' => '2.6', 'three' => 'three'));
	
		$this->assertSame(5.7, $this->db->hincrbyfloat('test1', 'one', 4.7));
		$this->assertSame(8.9, $this->db->hincrbyfloat('test1', 'two', 6.3));
		
		$this->assertSame(3.5, $this->db->hincrbyfloat('test1', 'four', 3.5));
	}
	
}
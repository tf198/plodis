<?php
require_once 'BaseTest.php';

class ListTest extends BaseTest {
	
	function testRPush() {
		$this->assertSame(1, $this->db->rpush('test1', 'one'));
		$this->assertSame(2, $this->db->rpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(4, $this->db->rpush('test1', 'three', 'four'));
		
		// opt behavior
		if(BACKEND == 'PLODIS') {
			$this->db->setOption('return_counts', false);
			$this->assertSame(-1, $this->db->rpush('test1', 'five'));
			$this->assertSame(-1, $this->db->rpush('test1', 'six', 'seven'));
			$this->db->setOption('return_counts', true);
		} else {
			$this->db->rpush('test1', 'five', 'six', 'seven');
		}
		
		// check result
		$this->assertSame(array('one', 'two', 'three', 'four', 'five', 'six', 'seven'), $this->db->lrange('test1', 0, -1));
		
		$this->db->linsert('test1', 'BEFORE', 'four', 'eight');
		$this->db->rpush('test1', 'nine');
		$this->assertSame(array('one', 'two', 'three', 'eight', 'four', 'five', 'six', 'seven', 'nine'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLPush() {
		$this->assertSame(1, $this->db->lpush('test1', 'one'));
		$this->assertSame(2, $this->db->lpush('test1', 'two'));
		
		// multi insert
		$this->assertSame(4, $this->db->lpush('test1', 'three', 'four'));
		
		// opt behavior
		if(BACKEND == 'PLODIS') {
			$this->db->setOption('return_counts', false);
			$this->assertSame(-1, $this->db->lpush('test1', 'five'));
			$this->assertSame(-1, $this->db->lpush('test1', 'six', 'seven'));
			$this->db->setOption('return_counts', true);
		} else {
			$this->db->lpush('test1', 'five', 'six', 'seven');
		}
		
		// check result
		$this->assertSame(array('seven', 'six', 'five', 'four', 'three', 'two', 'one'), $this->db->lrange('test1', 0, -1));
		
		// check push to other types
		$this->db->set('test2', 'one');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'lpush', 'test2', 'two');
		
		$this->db->hset('test3', 'thing', 'it');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'lpush', 'test3', 'three');
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
		
		$this->assertSame(6, $this->db->linsert('test1', 'before', 'two', 'five'));
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
		
		$this->db->linsert('test1', 'before', 'three', 'eleven');
		$this->assertSame(array('ten', 'seven', 'one', 'five', 'two', 'eleven', 'three', 'three', 'six', 'eight', 'four', 'nine'), $this->db->lrange('test1', 0, -1));
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
	
	function testLSet() {
		$this->db->rpush('test1', 'one', 'two', 'three', 'three', 'four');
		
		$this->db->lset('test1', 1, 'five');
		$this->assertSame(array('one', 'five', 'three', 'three', 'four'), $this->db->lrange('test1', 0, -1));
		
		$this->db->lset('test1', 4, 'six');
		$this->assertSame(array('one', 'five', 'three', 'three', 'six'), $this->db->lrange('test1', 0, -1));
		
		$this->db->lset('test1', 0, 'seven');
		$this->assertSame(array('seven', 'five', 'three', 'three', 'six'), $this->db->lrange('test1', 0, -1));
		
		// negative indicies
		$this->db->lset('test1', -2, 'eight');
		$this->assertSame(array('seven', 'five', 'three', 'eight', 'six'), $this->db->lrange('test1', 0, -1));
		
		$this->assertThrows('RuntimeException: Index out of range: 5', $this->db, 'lset', 'test1', 5, 'nine');
		
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
		$this->markTestSkipped();
	}
	
	function testBRPop() {
		$this->markTestSkipped();
	}
	
	function testLLen() {
		$this->db->rpush('test1', 'one', 'two', 'three', 'three', 'four');
		
		$this->assertSame(5, $this->db->llen('test1'));
		
		$this->db->rpush('test1', 'five');
		$this->assertSame(6, $this->db->llen('test1'));
		
		$this->db->lrem('test1', 0, 'three');
		$this->assertSame(4, $this->db->llen('test1'));
		
		$this->assertSame(0, $this->db->llen('test2'));
	}
	
	function testRPopLPush() {
		$this->db->rpush('test1', 'one', 'two', 'three');
		$this->db->rpush('test2', 'a', 'b', 'c');
		
		$this->assertSame('three', $this->db->rpoplpush('test1', 'test2'));
		$this->assertSame(array('one', 'two'), $this->db->lrange('test1', 0, -1));
		$this->assertSame(array('three', 'a', 'b', 'c'), $this->db->lrange('test2', 0, -1));
		
		// to empty list
		$this->assertSame('two', $this->db->rpoplpush('test1', 'test3'));
		$this->assertSame(array('one'), $this->db->lrange('test1', 0, -1));
		$this->assertSame(array('two'), $this->db->lrange('test3', 0, -1));
		
		$this->assertSame('one', $this->db->rpoplpush('test1', 'test3'));
		$this->assertSame(null, $this->db->rpoplpush('test1', 'test3'));
		
		$this->assertSame('c', $this->db->rpoplpush('test2', 'test2'));
		$this->assertSame(array('c', 'three', 'a', 'b'), $this->db->lrange('test2', 0, -1));
	}
	
	function testLPushX() {
		$this->assertSame(0, $this->db->lpushx('test1', 'one'));
		$this->assertSame(null, $this->db->get('test1'));
		
		$this->db->lpush('test1', 'one');
		$this->assertSame(2, $this->db->lpushx('test1', 'two'));
		$this->assertSame(array('two', 'one'), $this->db->lrange('test1', 0, -1));
	}
	
	function testRPushX() {
		$this->assertSame(0, $this->db->rpushx('test1', 'one'));
		$this->assertSame(null, $this->db->get('test1'));
	
		$this->db->rpush('test1', 'one');
		$this->assertSame(2, $this->db->rpushx('test1', 'two'));
		$this->assertSame(array('one', 'two'), $this->db->lrange('test1', 0, -1));
	}
	
	function testLTrim() {
		if(BACKEND != 'PLODIS') $this->markTestIncomplete(); // Predis returns 1
		$this->db->rpush('test1', 'a', 'b', 'c', 'd', 'e');
		$this->assertSame(null, $this->db->ltrim('test1', 0, 3));
		$this->assertSame(array('a', 'b', 'c'), $this->db->lrange('test1', 0, -1));
		
		$this->db->rpush('test2', 'a', 'b', 'c', 'd', 'e');
		$this->assertSame(null, $this->db->ltrim('test2', 0, -2));
		$this->assertSame(array('a', 'b', 'c', 'd'), $this->db->lrange('test2', 0, -1));
		
		$this->db->rpush('test3', 'a', 'b', 'c', 'd', 'e');
		$this->assertSame(null, $this->db->ltrim('test3', 1, 3));
		$this->assertSame(array('b', 'c'), $this->db->lrange('test3', 0, -1));
		
		$this->db->rpush('test4', 'a', 'b', 'c', 'd', 'e');
		$this->assertSame(null, $this->db->ltrim('test4', 1, -3));
		$this->assertSame(array('b', 'c'), $this->db->lrange('test4', 0, -1));
		
		$this->db->rpush('test5', 'a', 'b', 'c', 'd', 'e');
		$this->assertSame(null, $this->db->ltrim('test5', -4, -2));
		$this->assertSame(array('c', 'd'), $this->db->lrange('test5', 0, -1));
	}
	
	function testOverwriteList() {
		$this->db->rpush('test1', 'one', 'two', 'three');
		
		$this->db->set('test1', 'one');
		$this->assertSame('one', $this->db->get('test1'));
	}
	
	function testLRPop() {
		// alternating L R pop ops
		$this->db->rpush('test1', 'a', 'b', 'c', 'd', 'e');
		
		$this->assertSame('a', $this->db->lpop('test1'));
		$this->assertSame('e', $this->db->rpop('test1'));
		$this->assertSame('b', $this->db->lpop('test1'));
		$this->assertSame('d', $this->db->rpop('test1'));
		$this->assertSame('c', $this->db->lpop('test1'));
		$this->assertSame(null, $this->db->rpop('test1'));
	}
}
<?php
require_once "BaseTest.php";

class SetTest extends BaseTest {
	function testSAdd() {
		$this->assertSame(1, $this->db->sadd('test1', 'one'));
		$this->assertSame(0, $this->db->sadd('test1', 'one'));
		$this->assertSame(2, $this->db->sadd('test1', 'one', 'two', 'three'));
		
		$this->assertSame(3, $this->db->scard('test1'));
		
		$this->db->set('test2', 'one');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'sadd', 'test2', 'two');
	}
	
	function testSCard() {
		$this->assertSame(0, $this->db->scard('test1'));
		
		$this->db->sadd('test1', '1', '2', '3', '4');
		$this->assertSame(4, $this->db->scard('test1'));
		
		$this->db->set('test2', 'one');
		$this->db->lpush('test3', 'two');
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'scard', 'test2');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'scard', 'test3');
	}
	
	function testSMembers() {
		$this->assertSame(array(), $this->db->smembers('test1'));
		
		$this->db->sadd('test1', 'one', 'two', 'three');
		$this->assertSame(array('one', 'two', 'three'), $this->db->smembers('test1'));
		
		$this->db->set('test2', 'one');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'smembers', 'test2');
	}
	
	function testSRandMember() {
		$this->assertSame(null, $this->db->srandmember('test1'));
		
		$items = array('1', '2', '3', '4');
		$this->db->sadd('test1', $items);
		
		$this->assertContains($this->db->srandmember('test1'), $items);
		
		$this->db->set('test2', 'one');
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'srandmember', 'test2');
	}
	
	function testSDiff() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertSame(array('a','b', 'd'), $this->db->sdiff('test1', 'test2'));
		
		$this->assertSame(array('b', 'd'), $this->db->sdiff('test1', 'test2', 'test3'));
		
		$this->assertSame(array('b', 'd'), $this->db->sdiff('test1', 'test3', 'test4'));
		
		$this->assertSame(array('a', 'c', 'e'), $this->db->sdiff('test3'));
		
		$this->assertSame(array(), $this->db->sdiff('test4', 'test1'));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'sdiff', 'check_1', 'test1');
	}
	
	function testSDiffStore() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertSame(2, $this->db->sdiffstore('test4', 'test1', 'test2', 'test3'));
		$this->assertSame(array('b','d'), $this->db->smembers('test4'));
	}
	
	function testSRem() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd', 'e', 'f');
		$this->assertSame(1, $this->db->srem('test1', 'c'));
		$this->assertSame(2, $this->db->srem('test1', 'b', 'c', 'd'));
		$this->assertSame(1, $this->db->srem('test1', 'f', 'g'));
		
		$this->assertSame(array('a', 'e'), $this->db->smembers('test1'));
		
		$this->assertSame(0, $this->db->srem('test2', 'a', 'b'));
	}
	
	function testSIsMember() {
		$this->assertSame(0, $this->db->sismember('test1', 'a'));
		
		$this->db->sadd('test1', 'a', 'b', 'c');
		
		$this->assertSame(1, $this->db->sismember('test1', 'a'));
		$this->assertSame(0, $this->db->sismember('test1', 'd'));
		
		$this->assertThrows('PlodisIncorrectKeyType: ', $this->db, 'sismember', 'check_1', 'a');
	}
	
	function testSInter() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertSame(array('c'), $this->db->sinter('test1', 'test2'));
		$this->assertSame(array('c'), $this->db->sinter('test1', 'test2', 'test3'));
		$this->assertSame(array('a', 'c'), $this->db->sinter('test1', 'test3'));
		
		$this->assertSame(array(), $this->db->sinter('test1', 'test4'));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'sinter', 'check_1', 'test2');
	}
	
	function testSInterStore() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertSame(2, $this->db->sinterstore('test4', 'test1', 'test3'));
		$this->assertSame(array('a', 'c'), $this->db->smembers('test4'));
	}
	
	function testSMove() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertSame(1, $this->db->smove('test1', 'test2', 'b'));
		$this->assertSame(0, $this->db->smove('test1', 'test2', 'e'));
		$this->assertSame(array('a', 'c', 'd'), $this->db->smembers('test1'));
		$this->assertSame(array('c', 'b'), $this->db->smembers('test2'));
		
		$this->assertSame(1, $this->db->smove('test1', 'test3', 'c'));
		$this->assertSame(array('a', 'd'), $this->db->smembers('test1'));
		$this->assertSame(array('a', 'c', 'e'), $this->db->smembers('test3'));
	}
	
	function testSUnion() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c', 'f', 'z');
		$this->db->sadd('test3', 'a', 'c', 'e');
		
		$this->assertEquals(array('a', 'b', 'c', 'd'), $this->db->sunion('test1'));
		$this->assertEquals(array('a', 'b', 'd', 'c', 'f', 'z'), $this->db->sunion('test1', 'test2'));
		$this->assertEquals(array('b', 'd', 'f', 'z', 'a', 'c', 'e'), $this->db->sunion('test1', 'test2', 'test3'));
		
		$this->assertThrows('PlodisIncorrectKeyType:', $this->db, 'sunion', 'check_1', 'test2');
	}
	
	function testSUnionStore() {
		$this->db->sadd('test1', 'a', 'b', 'c', 'd');
		$this->db->sadd('test2', 'c', 'f', 'z');
		$this->db->sadd('test3', 'a', 'c', 'e');
	
		$this->assertSame(5, $this->db->sunionstore('test4', 'test1', 'test3'));
		$this->assertSame(array('b', 'd', 'a', 'c', 'e'), $this->db->smembers('test4'));
	}
}
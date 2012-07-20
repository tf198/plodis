<?php
require_once "BaseTest.php";

class GenericTest extends BaseTest {
	
	function testDel() {
		$this->db->mset(array('test1' => 1, 'test2' => 'two'));
	
		$this->assertSame(1, $this->db->del('test1'));
		$this->assertSame(array('test2'), $this->db->keys('test*'));
	
		$this->assertSame(0, $this->db->del('bad1', 'bad2'));
		$this->assertSame(array('test2'), $this->db->keys('test*'));
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
		$this->assertSame($this->check_keys, $this->db->keys('*'));
		
		$loc = new Plodis(':memory:');
		
		// no keys
		$this->assertSame(array(), $loc->keys('*'));
	
		$loc->mset(array('one' => 1, 'two' => 'two', 'three' => 'iii'));
	
		// normal match
		$this->assertSame(array('one', 'two', 'three'), $loc->keys('*'));
	
		// fuzzy matches
		$this->assertSame(array('two'), $loc->keys('two')); // silly but valid
		$this->assertSame(array('two', 'three'), $loc->keys('t*'));
		$this->assertSame(array('one', 'three'), $loc->keys('*e*'));
		$this->assertSame(array(), $loc->keys('z*'));
	}
	
	function testExpire() {
		$this->db->set('test1', 1);
		
		$this->assertSame(1, $this->db->expire('test1', 20));
		$this->assertSame(20, $this->db->ttl('test1'));
		
		$this->assertSame(0, $this->db->expire('test2', 20));
	}
	
	function testExpireAt() {
		$this->db->set('test1', 1);
		
		$this->assertSame(1, $this->db->expireat('test1', time() + 12));
		$this->assertSame(12, $this->db->ttl('test1'));
		
		$this->assertSame(0, $this->db->expireat('test2', time() + 12));
	}
	
	function testPersist() {
		if(strcmp(Plodis::REDIS_VERSION, '2.6.0') < 0)  $this->markTestSkipped();
		$this->db->setex('test1', 1, 13);
		
		$this->assertSame(13, $this->db->ttl('test1'));
		$this->assertSame(1, $this->db->persist('test1'));
		$this->assertSame(-1, $this->db->ttl('test1'));
		
		$this->assertSame(0, $this->db->persist('test2'));
	}
	
	function testTTL() {
		// unset
		$this->assertSame(-1, $this->db->ttl('test1'));
		
		// persistent
		$this->db->set('test1', 1);
		$this->assertSame(-1, $this->db->ttl('test1'));
		
		// transient
		$this->db->expire('test1', 14);
		$this->assertSame(14, $this->db->ttl('test1'));
		
		// expired
		$this->db->expireat('test1', time()-10);
		$this->assertSame(-1, $this->db->ttl('test1'));
	}
	
	function testPTTL() {
		if(strcmp(Plodis::REDIS_VERSION, '2.6.0') < 0)  $this->markTestSkipped();
		// unset
		$this->assertSame(-1, $this->db->pttl('test1'));
		
		// persistent
		$this->db->set('test1', 1);		
		$this->assertSame(-1, $this->db->pttl('test1'));
		
		// transient
		$this->db->pexpire('test1', 1034);
		$this->assertSame(1034, $this->db->pttl('test1'));
		
		// expired
		$this->db->expireat('test1', time()-10);
		$this->assertSame(-1, $this->db->pttl('test1'));
	}
	
	function testPExpire() {
		if(strcmp(Plodis::REDIS_VERSION, '2.6.0') < 0)  $this->markTestSkipped();
		$this->db->set('test1', 1);
		
		// normal usage
		$this->assertSame(1, $this->db->pexpire('test1', 20));
		$this->assertEquals(20, $this->db->pttl('test1'), $message='', $delta=1.0);
		
		// non-existing key
		$this->assertSame(0, $this->db->pexpire('test2', 20));
	}
	
	function testExpireOnConstruct() {
		$this->db->set('test1', 1);
		
		$this->db->expireat('test1', time()-1);
		
		$this->db = new Plodis($this->db->db->getConnection());
		
		$this->assertSame(null, $this->db->get('test1'));
	}
	
	function testExpireOnAccess() {
		$this->db->set('test1', 1);
		$this->db->expireat('test1', time()-1);
		
		$this->assertSame(null, $this->db->get('test1'));
	}
	
	function testAlarm() {
		$this->db->set('test1', 1);
 		$this->db->pexpire('test1', 20);
		
		$this->assertSame('1', $this->db->get('test1'));
		usleep(25000); // allow a fraction over 20 milliseconds
		$this->assertSame(null, $this->db->get('test1'));
	}
	
	function testType() {
		$this->db->set('test1', '1');
		$this->assertSame('string', $this->db->type('test1'));
		
		$this->db->rpush('test2', '1');
		$this->assertSame('list', $this->db->type('test2'));
		
		$this->db->hset('test3', 'one', '1');
		$this->assertSame('hash', $this->db->type('test3'));
		
		// TODO: Set and ZSet
	}
	
	function testRename() {
		$this->db->rpush('test1', 'one', 'two', 'three');
		$this->db->set('test2', 'four');
		$this->db->hset('test3', 'test', 'five');
		
		$this->db->rename('test1', 'test4');
		$this->assertSame(array('one', 'two', 'three'), $this->db->lrange('test4', 0, -1));
		
		$this->db->rename('test2', 'test5');
		$this->assertSame('four', $this->db->get('test5'));
		
		$this->db->rename('test3', 'test6');
		$this->assertSame('five', $this->db->hget('test6', 'test'));
		
		$this->assertThrows('PlodisError: Key does not exist', $this->db, 'rename', 'test7', 'test8');
	}
	
	function testRenameNX() {
		$this->db->set('test1', 'one');
		$this->db->renamenx('test1', 'test2');
		$this->assertSame('one', $this->db->get('test2'));
		
		$this->db->set('test3', 'two');
		
		$this->db->renamenx('test2', 'test3');
		$this->assertSame('one', $this->db->get('test2'));
		$this->assertSame('two', $this->db->get('test3'));
	}
	
	function testRandom() {
		$loc = new Plodis(':memory:');
		
		$this->assertSame(null, $loc->randomkey());
		
		$values = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);
		$loc->mset($values);
		$diff = 0;
		$prev = '';
		for($i=0; $i<10; $i++) {
			$c = $loc->randomkey();
			if($c != $prev) $diff++;
			$this->assertTrue(array_key_exists($c, $values));
			$prev = $c;
		}
		$this->assertGreaterThan(5, $diff);
	}
	
	function testSort() {
		$this->db->rpush('test1', 1, 4, 3, 6, 7, 4, 3, 4);
		$this->db->mset(array('weight_1' => 10, 'weight_7' => 2));
						
		$this->assertSame(array('1', '3', '3', '4', '4', '4', '6', '7'), $this->db->sort('test1'));
		
		$this->assertSame(array('4', '3', '6', '4', '3', '4', '7', '1'), $this->db->sort('test1', 'weight_*'));
		
		$this->assertSame(array('4', '3', '6', '4', '3', '4', '1', '7'), $this->db->sort('test1', 'weight_*', null, null, null, "ALPHA"));
		
		$this->assertSame(8, count($this->db->sort('test1', 'weight_*', null, array('#', 'weight_*'))));
		
		$this->assertSame(null, $this->db->sort('test1', null, null, null, null, null, 'test2'));
		$this->assertSame(array('1', '3', '3', '4', '4', '4', '6', '7'), $this->db->lrange('test2', 0, -1));
	}
	
}
<?php
require_once "Plodis.php";

class ExpireTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Plodis object
	 * @var Plodis
	 */
	public $db;
	
	function setUp() {
		$this->db = new Plodis(new PDO('sqlite::memory:'));
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
		$this->markTestSkipped();
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
		$this->markTestSkipped();
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
		$this->markTestSkipped();
		$this->db->set('test1', 1);
		
		// normal usage
		$this->assertSame(1, $this->db->pexpire('test1', 20));
		$this->assertSame(20, $this->db->pttl('test1'), $message='', $delta=1.0);
		
		// non-existing key
		$this->assertSame(0, $this->db->pexpire('test2', 20));
	}
	
	function testExpireOnConstruct() {
		$this->db->set('test1', 1);
		
		$this->db->expireat('test1', time()-1);
		
		$this->db = new Plodis($this->db->conn);
		
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
}
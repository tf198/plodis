<?php
require_once "BaseTest.php";

class ServerTest extends BaseTest {
	/* 2.6.0
	function testTime() {
		$time = $this->db->time();
		$this->assertEquals(time(), $time[0], '', 1.0);
	}
	*/
	
	function testInfo() {
		if(BACKEND == 'PREDIS') $this->markTestSkipped();
		$expected = array(
			'redis_version' => '2.4.0',
			'db0' => 'keys=5,expires=0',
		);
		if(BACKEND == 'MYSQL') $expected['db1'] = 'keys=0,expires=0';
		$this->assertSame($expected, $this->db->info());
	}
	
	function testDBSize() {
		$this->assertSame(count($this->check_keys), $this->db->dbsize());
	}
	
	function testFlushDB() {
		$this->db->flushdb();
		
		$this->assertSame(0, $this->db->dbsize());
		$this->skip_checks = true;
	}
	
	function testFlushAll() {
		$this->db->select(1);
		$this->db->set('test1', 'one');
		$this->db->flushall();
		
		$this->assertSame(0, $this->db->dbsize());
		$this->db->select(0);
		$this->assertSame(0, $this->db->dbsize());
		
		$this->skip_checks = true;
	}
	
	function testConfigGet() {
		if(BACKEND == 'PREDIS') return;
		$this->assertSame('0.1', $this->db->config_get('poll_frequency'));
		$this->assertSame(null, $this->db->config_get('non-existent'));
		
		$this->assertSame(array_keys($this->db->options), $this->db->config_get('*'));
	}
	
	function testConfigSet() {
		if(BACKEND == 'PREDIS') return;
		$this->db->config_set('poll_frequency', 0.2);
		$this->db->config_set('non-existent', 'test');
		
		$this->assertSame('0.2', $this->db->config_get('poll_frequency'));
		$this->assertSame('test', $this->db->config_get('non-existent'));
	}
	
	function testNoOps() {
		if(BACKEND == 'PREDIS') return;
		// these all do nothing but should not throw exceptions
		$this->db->bgrewriteaof();
		$this->db->bgsave();
		$this->db->config_resetstat();
		$this->db->save();
		$this->db->slaveof('10.0.0.1', 1234);
		$this->db->sync();
	}
}
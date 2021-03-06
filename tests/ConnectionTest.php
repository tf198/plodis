<?php
require_once 'BaseTest.php';

class ConnectionTest extends BaseTest {
	function testEcho() {
		if(BACKEND == 'PREDIS') $this->markTestIncomplete();
		$this->assertSame('Hello World', $this->db->_echo('Hello World'));
		$this->assertSame('1', $this->db->_echo(1));
	}
	
	function testPing() {
		if(BACKEND == 'PREDIS') $this->markTestIncomplete();
		$this->assertSame('PONG', $this->db->ping());
	}
}
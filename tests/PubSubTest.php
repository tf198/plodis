<?php
require_once "Plodis.php";

class PubSubTest extends PHPUnit_Framework_TestCase {
	
	function testUsage() {
		$pdo = new PDO('sqlite::memory:');
		
		$pub = new Plodis($pdo);
		
		$sub1 = new Plodis($pdo);
		$sub1->subscribe('test-channel-1');
		$sub1->subscribe('test-channel-3');
		
		$sub2 = new Plodis($pdo);
		$sub2->subscribe('test-channel-1');
		$sub2->subscribe('test-channel-2');
		
		$this->assertSame(null, $sub1->poll());
		$this->assertSame(null, $sub2->poll());
		
		$pub->publish('test-channel-1', 'one');
		$this->assertSame('one', $sub1->poll());
		$this->assertSame('one', $sub2->poll());
		
		$pub->publish('test-channel-2', 'two');
		$this->assertSame(null, $sub1->poll());
		$this->assertSame('two', $sub2->poll());
		
		$pub->publish('test-channel-3', 'three');
		$this->assertSame('three', $sub1->poll());
		$this->assertSame(null, $sub2->poll());
		
		// all digested
		$this->assertSame(null, $sub1->poll());
		$this->assertSame(null, $sub2->poll());
		
		// double subscription
		$sub2->subscribe('test-channel-1');
		
		$pub->publish('test-channel-1', 'four');
		$this->assertSame('four', $sub1->poll());
		$this->assertSame('four', $sub2->poll());
		$this->assertSame('four', $sub2->poll());
		
		// all digested
		$this->assertSame(null, $sub1->poll());
		$this->assertSame(null, $sub2->poll());
	}
	
	function testUnsubscribe() {
		$pdo = new PDO('sqlite::memory:');
		
		$pub = new Plodis($pdo);
		
		$sub1 = new Plodis($pdo);
		$sub1->subscribe('test-channel-1');
		
		$pub->publish('test-channel-1', 'one');
		$this->assertSame('one', $sub1->poll());
		
		$sub1->unsubscribe('test-channel-1');
		
		$pub->publish('test-channel-1', 'two');
		$this->assertSame(null, $sub1->poll());
		
	}
}
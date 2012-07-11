<?php
include dirname(dirname(__FILE__)) . "/Plodis.php";

$queue = new Plodis(new PDO('sqlite:data/testing.sq3'));

while(true) {
	$item = $queue->blpop('queue-test');
	echo "GOT {$item}\n";
	if($item == 'quit') break;
}

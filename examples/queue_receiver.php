<?php
include dirname(dirname(__FILE__)) . "/lib/Plodis.php";

$queue = new Plodis(new PDO('sqlite:data/testing.sq3'));

while(true) {
	$item = $queue->blpop('queue-test', 0);
	echo "GOT {$item}\n";
	if($item == 'quit') break;
}

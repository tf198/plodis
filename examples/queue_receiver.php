<?php
include dirname(dirname(__FILE__)) . "/Redish.php";

$queue = new Redish(new PDO('sqlite:testing.sq3'));

while(true) {
	$item = $queue->blpop('queue-test');
	echo "GOT {$item}\n";
}

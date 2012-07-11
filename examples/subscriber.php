<?php
include dirname(dirname(__FILE__)) . "/Redish.php";

$redis = new Redish(new PDO('sqlite:testing.sq3'));

$redis->subscribe('test-channel');

while(true) {
	$item = $redis->receive();
	echo "GOT {$item}\n";
}

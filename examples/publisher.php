<?php
include dirname(dirname(__FILE__)) . "/Redish.php";

$redis = new Redish(new PDO('sqlite:testing.sq3'));

while(true) {
	$input = trim(fgets(STDIN));
	$redis->publish('test-channel', $input);
	if($input == 'quit') break;
}

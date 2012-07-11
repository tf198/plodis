<?php
include dirname(dirname(__FILE__)) . "/Redish.php";

$queue = new Redish(new PDO('sqlite:testing.sq3'));

while(true) {
	$input = trim(fgets(STDIN));
	$queue->rpush('queue-test', $input);
	if($input == 'quit') break;
}

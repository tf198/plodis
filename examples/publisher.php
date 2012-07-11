<?php
include dirname(dirname(__FILE__)) . "/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:testing.sq3'));

while(true) {
	$input = trim(fgets(STDIN));
	$plodis->publish('test-channel', $input);
	if($input == 'quit') break;
}

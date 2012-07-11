<?php
include dirname(dirname(__FILE__)) . "/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:data/testing.sq3'));

$plodis->subscribe('test-channel');

while(true) {
	$item = $plodis->receive();
	fwrite(STDOUT, "GOT {$item}\n");
	if($item == 'quit') break;
}

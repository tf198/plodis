<?php
include dirname(dirname(__FILE__)) . "/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:testing.sq3'));

$plodis->subscribe('test-channel');

while(true) {
	$item = $plodis->receive();
	echo "GOT {$item}\n";
}

<?php
include dirname(dirname(__FILE__)) . "/lib/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:data/load_test.sq3'));

$pid = getmypid();
$i = 0;

$delay = 250000;
$jobs = 100;

echo "Producer {$pid} starting {$jobs} jobs\n";

for($i=0; $i<$jobs; $i++) {
	// create jobs every X seconds
	$job = array('id' => $pid . ':' . $i);
	$plodis->lpush('job-in', json_encode($job));
	usleep($delay);
}

echo "Producer {$pid} finished\n";
<?php
include dirname(dirname(__FILE__)) . "/lib/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:data/load_test.sq3'));

$pid = getmypid();

echo "Consumer {$pid} finished\n";

while(true) {
	$data = $plodis->brpop('job-in', 2);
	if($data == null) break;
	
	$job = json_decode($data, true);
	usleep(rand(250000, 500000));
	$result = array('worker' => $pid, 'job' => $job['id'], 'result' => true);
	$plodis->lpush('job-out', json_encode($result));
	echo "Worker {$pid} processed job {$job['id']}\n";
}

echo "Consumer {$pid} finished\n";
<?php
include dirname(dirname(__FILE__)) . "/lib/Plodis.php";

$plodis = new Plodis(new PDO('sqlite:data/load_test.sq3'));

$processes = array();

$plodis->del('job-in', 'job-out');

$producers = 2;
$consumers = 10;

$path = realpath(dirname(__FILE__));

for($i=0; $i<$producers; $i++) {
	$processes[] = proc_open("php {$path}/producer.php", array(), $pipes);
}
for($i=0; $i<$consumers; $i++) {
	$processes[] = proc_open("php {$path}/consumer.php", array(), $pipes);
}

echo "Waiting for jobs to start...\n";
sleep(1);

$stats = array();

echo "Waiting for jobs to finish\n";
while(true) {
	$data = $plodis->brpop('job-out', 3);
	if($data == null) break;
	$result = json_decode($data, true);
	if(!isset($stats[$result['worker']])) $stats[$result['worker']] = 0;
	$stats[$result['worker']]++;
}

echo "\n\nStats:\n";
$total = 0;
foreach($stats as $worker=>$count) {
	echo "Worker {$worker}: {$count}\n";
	$total += $count;
}

echo "Total jobs: {$total}\n";
<?php
$GLOBALS['bench_mem'] = 0;
$GLOBALS['bench_ts_start'] = microtime(true);
$GLOBALS['bench_ts'] = microtime(true);
fprintf(STDERR, <<<EOF
===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================

EOF
		);

define('BENCH_DATA', 'benchmark.sq3');
define('LOOP_SIZE', 500);

function bench($message, $count=1) {
	$now = microtime(true);
	$mem = memory_get_usage();
	
	$ops = $count / ($now-$GLOBALS['bench_ts']);
	fprintf(STDERR, "%5d %4d %6d %4d %7d %s\n", $mem/1024, ($mem-$GLOBALS['bench_mem'])/1024, ($now-$GLOBALS['bench_ts_start'])*1000, ($now-$GLOBALS['bench_ts'])*1000, $ops, $message);
	
	$GLOBALS['bench_ts'] = $now;
	$GLOBALS['bench_mem'] = $mem;
}

bench('init');

@unlink(BENCH_DATA);
$pdo = new PDO('sqlite:' . BENCH_DATA);
bench('PDO creating from new');

include "Plodis.php";
bench('include');

$db = new Plodis($pdo);
bench('construct (including tables)');

unset($db, $pdo);
bench('free');

$pdo = new PDO('sqlite:' . BENCH_DATA);
bench('PDO from existing file');

$db = new Plodis($pdo);
bench('construct (if not exists)');

bench('Starting loop tests - ' . LOOP_SIZE . " iterations");

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->set('item_' . $i, $i+1);
}
bench('SET (insert)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->set('item_' . $i, $i);
}
bench('SET (update)', LOOP_SIZE);

$db->lock();
for($i=0; $i<LOOP_SIZE; $i++) {
	$db->set('item_' . $i, $i);
}
$db->unlock();
bench('SET (update, locked)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->get('item_' . $i) == $i);
}
bench('GET', LOOP_SIZE);

$db->lock();
for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->get('item_' . $i) == $i);
}
$db->unlock();
bench('GET (locked)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->lpush('list1', $i);
}
bench('LPUSH', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->rpush('list1', $i);
}
bench('RPUSH', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->lpop('list1');
}
bench('LPOP', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->llen('list1') == LOOP_SIZE);
}
bench('LLEN', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->lindex('list1', $i) == $i);
}
bench('LINDEX', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->rpop('list1');
}
bench('RPOP', LOOP_SIZE);

// free everything we can
unset($pdo, $db);
@unlink(BENCH_DATA);
bench('cleanup');
fprintf(STDERR, "===== ==== ====== ==== ======= =======================================\n");
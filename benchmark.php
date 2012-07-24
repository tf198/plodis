<?php
$backend = ($argc>1) ? strtoupper($argv[1]) : 'PLODIS';
$server = ($argc>2) ? $argv[2] : null;

fprintf(STDOUT, "Running benchmarks using {$backend}\n");

$GLOBALS['bench_mem'] = 0;
$GLOBALS['bench_ts_start'] = microtime(true);
$GLOBALS['bench_ts'] = microtime(true);
fprintf(STDOUT, <<<EOF
===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================

EOF
		);

$key = rand(1000, 9999);

define('BENCH_DATA', 'data/benchmark.sq3');
#define('BENCH_DATA', ':memory:');
define('LOOP_SIZE', 1000);

function bench($message, $count=1) {
	$now = microtime(true);
	$mem = memory_get_usage();
	
	$ops = $count / ($now-$GLOBALS['bench_ts']);
	fprintf(STDOUT, "%5d %4d %6d %4d %7d %s\n", $mem/1024, ($mem-$GLOBALS['bench_mem'])/1024, ($now-$GLOBALS['bench_ts_start'])*1000, ($now-$GLOBALS['bench_ts'])*1000, $ops, $message);
	
	$GLOBALS['bench_ts'] = $now;
	$GLOBALS['bench_mem'] = $mem;
}

bench('init (' . $key . ')');

switch($backend) {
	case 'PREDIS':
		require "predis.phar";
		bench('include PREDIS');
		
		$db = new Predis\Client($server);
		bench('construct PREDIS');
		break;
	case 'PLODIS':
	case 'MYSQL':
		require "Plodis.php";
		bench('include PLODIS');
		
		$pdo = ($backend == 'PLODIS') ? new PDO('sqlite:' . BENCH_DATA) : new PDO($argv[2], $argv[3], $argv[4]);
		bench('PDO from existing data');
		
		$opt = ($backend == 'PLODIS');
		$db = new Plodis($pdo, array('return_counts' => false, 'validation_checks' => false));
		bench('construct PLODIS');
		
		break;
	default:
		throw new Exception("Unknown backend: " . $backend);
}

bench("Starting loop tests - " . LOOP_SIZE . " iterations");

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->set("{$key}_{$i}", $i);
}
bench('SET (insert)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->set("{$key}_{$i}", $i);
}
bench('SET (update)', LOOP_SIZE);

$replies = $db->pipeline(function($pipe) use($key) {
	for($i=0; $i<LOOP_SIZE; $i++) {
		$pipe->set("{$key}_{$i}", $i);
	}
});
bench('SET (pipelined)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
 	assert($db->get("{$key}_{$i}") == $i);
	//echo $db->get("{$key}_{$i}") . " {$i}" . PHP_EOL;
}
bench('GET', LOOP_SIZE);

$replies = $db->pipeline(function($pipe) use($key) {
	for($i=0; $i<LOOP_SIZE; $i++) {
		$pipe->get("{$key}_{$i}");
	}
});
bench('GET (pipelined)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->lpush("list_{$key}", $i);
}
bench('LPUSH', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->rpush("list_{$key}", $i);
}
bench('RPUSH', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->lpop("list_{$key}");
}
bench('LPOP', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->llen("list_{$key}") == LOOP_SIZE);
}
bench('LLEN', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->lindex("list_{$key}", $i) == $i);
}
bench('LINDEX', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->rpop("list_{$key}");
}
bench('RPOP', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->hset('hash_1', 'field_' . $i, $i);
}
bench('HSET', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	assert($db->hget('hash_1', 'field_' . $i) == $i);
}
bench('HGET', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->sadd('set_1', rand(0, 10));
}
bench('SADD (RAND 10)', LOOP_SIZE);

for($i=0; $i<LOOP_SIZE; $i++) {
	$db->sadd('set_2', rand(0, 100));
}
bench('SADD (RAND 100)', LOOP_SIZE);

$gc_count = ($db instanceof Plodis) ? $db->generic->gc_count : -1;

// free everything we can
unset($pdo, $db);
bench('cleanup');
fprintf(STDOUT, "===== ==== ====== ==== ======= =======================================\n");
fprintf(STDOUT, "GC COUNT: {$gc_count}\n");
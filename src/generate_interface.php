<?php
if($argc < 3) die('Usage: php generate_interface.php <version> <group>');

include "generate_common.php";

$safe_version = str_replace('.', '_', $argv[1]);
$group = strtolower($argv[2]);

$data = json_decode(file_get_contents(REDIS_DOC . 'commands.json'), true);

$version = str_replace('_', '.', $safe_version);
$name = "Redis_" . ucfirst($group) . "_{$safe_version}";

echo <<< EOF
<?php
/**
 * Stub functions for Redis group {$group}
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version {$version}
 */
interface {$name} {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "{$version}";
	
    const REDIS_GROUP = "{$group}";


EOF;

foreach($data as $key=>$command) {
	if(strcmp($command['since'], $version) > 0) continue;
	if($command['group'] != $group) continue;

	$info = redis_command_info($key, $command);
	
	echo <<< EOF
    {$info['doc']}
    public function {$info['func']}({$info['params']});


EOF;
  //break;

}

echo "}\n";
?>

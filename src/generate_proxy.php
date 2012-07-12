<?php
if($argc < 2) die('Usage: php generate_proxy.php <version> <group1> <group2> ...');

include "generate_common.php";

$safe_version = str_replace('.', '_', $argv[1]);

$include_groups = array_slice($argv, 2);
foreach($include_groups as &$group) $group = strtolower($group);

$data = json_decode(file_get_contents(REDIS_DOC . 'commands.json'), true);

$version = str_replace('_', '.', $safe_version);
$inclusions = implode(', ', $include_groups);

echo <<< EOF
<?php
/**
 * @package Plodis
 * @author Tris Forster
 */

require_once "Plodis/Proxy.php";

/**
 * Proxy for Redis methods.  Dispatches calls to the group class
 * This class is automatically generated from the Redis docs on github.
 *
 * Included groups: {$inclusions}
 * Version emulation: {$version}
 *
 * @link https://github.com/antirez/redis-doc
 * @package Plodis
 * @author Tris Forster
 * @version {$version}
 */
class Plodis extends Plodis_Proxy {

	/**
	 * Redis server version
	 * @var string
	 */
	const REDIS_VERSION = "{$version}";


EOF;

foreach($data as $key=>$command) {
	if(strcmp($command['since'], $version) > 0) continue;
	if(array_search($command['group'], $include_groups) === false) continue;
	
	$info = redis_command_info($key, $command);
	
	echo <<< EOF
    {$info['doc']}
    public function {$info['func']}({$info['params']}) {
{$info['code']}
    }


EOF;
  //break;

}

echo "}\n";
?>

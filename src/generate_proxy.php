<?php
define('REDIS_DOC', dirname(__FILE__) . '/redis-doc/');
$safe_version = $argv[1];

$exclude_groups = array(
	'server', 'connection', 'scripting', 'transactions',
	'set', 'sorted_set', 'hash',
);

$data = json_decode(file_get_contents(REDIS_DOC . 'commands.json'), true);

$version = str_replace('_', '.', $safe_version);

$type_map = array(
	'key' => 'string',
	'pattern' => 'string',
);

$exclusions = implode(', ', $exclude_groups);
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
 * Excludes methods in groups: {$exclusions}
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
	if(array_search($command['group'], $exclude_groups) !== false) continue;

	// couple of fixes
	if($key == 'KEYS') {
		$command['arguments'][0]['optional'] = 'true';
	}
	
	$func = str_replace(' ', '_', strtolower($key));
	if($func == 'echo') $func = "_echo";

	$doc = array('/**');
	$doc[] = "     * {$command['summary']}";
	$doc[] = "     *";
	$doc[] = "     * @since {$command['since']}";
	$doc[] = "     * @api";
	$doc[] = "     * @group {$command['group']}";
	$doc[] = "     * @link http://redis.io/commands/" . strtolower(str_replace('', '-', $key)) . " {$key}";
	$doc[] = "     *";

	if(!isset($command['arguments'])) $command['arguments'] = array();

	$params = array();
	$raw_params = array();
	$code = array();
	foreach($command['arguments'] as $key=>$arg) {
		$help = '';
		$type = $arg['type'];
		if(is_array($type)) $type = "multitype:" . $type[0];
		if(isset($type_map[$type])) $type = $type_map[$type];
		if($type == 'enum') {
			$type = 'string';
			$help = ' [ ' . implode(', ', $arg['enum']) . ' ]';
		}
		
		$name = isset($arg['command']) ? strtolower($arg['command']) : $arg['name'];
		if(is_array($name)) $name = $name[0] . 's';
		$name = str_replace('-', '_', $name);
		
		if(isset($arg['optional'])) $help .' (optional)';
		if(isset($arg['multiple'])) {
			//$type .= '|multitype:' . $type;
			$help .= ' (multiple)';
			if($key + 1 == count($command['arguments'])) {
				$code[] = "        if(!is_array(\${$name})) \${$name} = array_slice(func_get_args(), {$key});";
			}
		}
		
		// add doc tag
		$doc[] = "     * @param {$type} \${$name}{$help}";
		// generate arg parameter
		$param = "\${$name}";
		$raw_params[] = $param;
		if(isset($arg['optional'])) $param .= '=null';
		$params[] = $param;
	}
	$params = implode(', ', $params);
	$raw_params = implode(', ', $raw_params);
	
	$doc[] = "     * @return " . get_return($key);
	$doc[] = "     */";
	$doc = implode(PHP_EOL, $doc);
	
	//$code[] = "        return \$this->group('{$command['group']}')->{$func}({$raw_params});";
	$code[] = "        return \$this->group_{$command['group']}->{$func}({$raw_params});";
	$code = implode(PHP_EOL, $code);
	
	echo <<< EOF
    {$doc}
    public function {$func}({$params}) {
{$code}
    }


EOF;
  //break;

}

echo "}\n";

function get_return($key) {

	$type_map = array(
		'@status-reply' => 'null',
		'@integer-reply,' => 'integer',
		'@integer-reply' => 'integer',
		'@multi-bulk-reply' => 'multitype:string',
		'@bulk-reply' => 'string',
		'`nil`' => '`null`',
		'`OK`' => '`true`',
		':' => '',
	);

	$override = array(
		'INFO' => 'multitype:string key/value pairs',
		'TYPE' => 'string type of `key`, or `none` when `key` does not exist.'
	);

	if(isset($override[$key])) return $override[$key];

	$file = REDIS_DOC . "commands/" . strtolower($key) . ".md";
	if(!is_readable($file)) return 'null no documentation available';
	
	$data = file($file);
	$pos = array_search("@return\n", $data);
	if($pos === false) return 'null';

	$i=$pos+1;
	while($i<count($data)) {
		if($data[$i][0] == '@') {
			$line = $data[$i++];
			while($i<count($data)) {
				if($data[$i][0] == "@") break;
				$line .= '     *   ' . $data[$i++];
			}
			return trim(strtr($line, $type_map));
		}
		$i++;
	}
	return 'null';
}
?>

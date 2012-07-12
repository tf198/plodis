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
 * Stub functions for Redis
 * This interface is automatically generated from the Redis docs on github.
 *
 * Excludes methods in groups: {$exclusions}
 * Version emulation: {$version}
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version {$version}
 */
interface Redis_{$safe_version} {

	/**
	 * Redis server version
	 * @var string
	 */
	const REDIS_VERSION = "{$version}";


EOF;

foreach($data as $key=>$command) {
	if(strcmp($command['since'], $version) > 0) continue;
	if(array_search($command['group'], $exclude_groups) !== false) continue;

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

	$args = array();
	foreach($command['arguments'] as $arg) {
		$help = '';
		$type = $arg['type'];
		if(is_array($type)) $type = "multitype:" . $type[0];
		if(isset($type_map[$type])) $type = $type_map[$type];
		if($type == 'enum') {
			$type = 'string';
			$help = ' [ ' . implode(', ', $arg['enum']) . ' ]';
		}
		if(isset($arg['optional'])) $help .' (optional)';
		if(isset($arg['multiple'])) $type .= '|multitype:' . $type;
		
		$name = isset($arg['command']) ? strtolower($arg['command']) : $arg['name'];
		if(is_array($name)) $name = $name[0] . 's';
		
		// add doc tag
		$doc[] = "     * @param {$type} \${$name}{$help}";
		// generate arg parameter
		$param = "\${$name}";
		if(isset($arg['optional'])) $param .= '=null';
		$args[] = $param;
	}
	$args = implode(', ', $args);

	$doc[] = "     * @return " . get_return($key);
	$doc[] = "     */";
	$doc = implode(PHP_EOL, $doc);

	echo <<< EOF
    {$doc}
  	public function {$func}({$args});


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

	$data = file(REDIS_DOC . "commands/" . strtolower($key) . ".md");
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

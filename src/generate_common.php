<?php
define('REDIS_DOC', dirname(__FILE__) . '/redis-doc/');

function redis_return_type($key) {

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

function redis_command_info($cmd, $command) {
	
	$type_map = array(
		'key' => 'string',
		'pattern' => 'string',
		'posix time' => 'integer',
	);
	
	$func = str_replace(' ', '_', strtolower($cmd));
	if($func == 'echo') $func = "_echo";
	
	$doc = array('/**');
	$doc[] = "     * {$command['summary']}";
	$doc[] = "     *";
	$doc[] = "     * @since {$command['since']}";
	$doc[] = "     * @api";
	$doc[] = "     * @group {$command['group']}";
	$doc[] = "     * @link http://redis.io/commands/" . strtolower(str_replace('', '-', $cmd)) . " {$cmd}";
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
	
	$doc[] = "     * @return " . redis_return_type($cmd);
	$doc[] = "     */";
	
	$raw_params = implode(', ', $raw_params);
	
	$code[] = "        return \$this->{$command['group']}->{$func}({$raw_params});";
	
	return array(
		'func' => $func,
		'doc' => implode(PHP_EOL, $doc),
		'params' => implode(', ', $params),
		'raw_params' => $raw_params,
		'code' => implode(PHP_EOL, $code),
	);
}
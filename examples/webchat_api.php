<?php 
$base = dirname(dirname(__FILE__));

include "{$base}/Plodis.php";

$datafile = "{$base}/data/webchat.sq3";
$plodis = new Plodis($datafile);

header('Content-type: application/json');

$action = (isset($_GET['action'])) ? $_GET['action'] : 'listen';
$user = (isset($_GET['user'])) ? $_GET['user'] : null;

if(!$user) fail('No username given');
$plodis->pubsub->setuid($user);

switch($action) {
	case 'listen':
		$data = $plodis->pubsub->bpoll(25);
		if(!$data) send(null);
		// send the raw json
		echo $data;
		exit;
	case 'join':
		if(!isset($_GET['channel'])) fail('No channel specified');
		$channel = $_GET['channel'];
		
		$old_channels = array_keys($plodis->pubsub->channels());
		
		$current = $plodis->hget('webchat', $user);
		// unsubscribe from old channel
		if($current) {
			$plodis->publish($current, data("<i>Left channel {$current}</i>"));
			$plodis->unsubscribe($current);
			$plodis->publish($current, data($plodis->pubsub->subscribers($current), 2));
		}
		
		// subscribe to new channel
		$plodis->subscribe($channel);
		$plodis->publish($channel, data("<i>Joined channel {$channel}</i>"));
		$plodis->publish($channel, data($plodis->pubsub->subscribers($channel), 2));
		
		// store our channel
		$plodis->hset('webchat', $user, $channel);
		
		// send out new channel list if required
		$new_channels = array_keys($plodis->pubsub->channels());
		if($new_channels != $old_channels) {
			foreach($plodis->hkeys('webchat') as $user) {
				//$plodis->publish($channel, data("<i>Sending update to {$user}</i>"));
				$plodis->pubsub->send($user, data($new_channels, 3));
			}
		}
		
		send("User {$user} joined channel {$channel}");
	case 'send':
		if(!isset($_GET['message'])) fail('No message given');
		$channel = $plodis->hget('webchat', $user);
		if(!$channel) fail('No current channel');
		
		$c = $plodis->publish($channel, data($_GET['message']));
		send($c);
	case 'login':
		$channel = $plodis->hget('webchat', $user);
		if(!$channel) {
			$plodis->hset('webchat', $user, '-');
		}
		
		$result = array(
			'channel' => $channel,
			'subscribers' => $plodis->pubsub->subscribers($channel),
			'channels' => array_keys($plodis->pubsub->channels()),
		);
		send($result);
	case 'logout':
		$current = $plodis->hget('webchat', $user);
		if($current) {
			$plodis->publish($current, data("<i>Left channel {$current}</i>"));
			$plodis->unsubscribe($current);
		}
		$plodis->hdel('webchat', $user);
		send('Logged out');
	default:
		fail("Unknown action: {$action}");
}

function data($data, $type=1) {
	global $user;
	$data = array('user' => $user, 'type' => $type, 'data' => $data);
	return json_encode($data);
}

function fail($message) {
	send($message, -1);
}

function send($message, $type=0) {
	echo data($message, $type);
	exit;
}

?>
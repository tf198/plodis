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
		$data = json_decode($plodis->pubsub->bpoll(25));
		send($data);
	case 'join':
		if(!isset($_GET['channel'])) fail('No channel specified');
		$channel = $_GET['channel'];
		
		$current = $plodis->hget('webchat', $user);
		// unsubscribe from old channel
		if($current) {
			$plodis->publish($current, message("<i>Left channel {$current}</i>", true));
			$plodis->unsubscribe($current);
		}
		
		// subscribe to new channel
		$plodis->subscribe($channel);
		$plodis->publish($channel, message("<i>Joined channel {$channel}</i>", true));
		
		// store our channel
		$plodis->hset('webchat', $user, $channel);
		
		send("User {$user} joined channel {$channel}");
	case 'send':
		if(!isset($_GET['message'])) fail('No message given');
		$channel = $plodis->hget('webchat', $user);
		if(!$channel) fail('No current channel');
		
		$c = $plodis->publish($channel, message($_GET['message']));
		send($c);
	case 'info':
		$channel = $plodis->hget('webchat', $user);
		$result = array(
			'channel' => $channel,
			'subscribers' => $plodis->pubsub->subscribers($channel),
			'channels' => $plodis->pubsub->channels(),
		);
		send($result);
	case 'logout':
		$current = $plodis->hget('webchat', $user);
		if($current) {
			$plodis->publish($current, message("<i>Left channel {$current}</i>", true));
			$plodis->unsubscribe($current);
		}
		$plodis->hdel('webchat', $user);
		send('Logged out');
	default:
		fail("Unknown action: {$action}");
}

function message($message, $reload=false) {
	global $user;
	$data = array('user' => $user, 'message' => $message);
	if($reload) $data['reload'] = true;
	return json_encode($data);
}

function fail($message) {
	send($message, 'error');
}

function send($message, $status='ok') {
	echo json_encode(array('status' => $status, 'message' => $message));
	exit;
}

?>
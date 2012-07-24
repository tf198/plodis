<?php
require_once "IRedis_Pubsub_2_4_0.php";

class Plodis_Pubsub extends Plodis_Group implements IRedis_Pubsub_2_4_0 {
	
	const CHANNEL_PREFIX = '_channel_';
	
	const SUBSCRIBER_PREFIX = '_subscriber_';
	
	const CHANNEL_LIST = '__channel_list__';
	
	private $uid;
	
	function __construct($proxy) {
		parent::__construct($proxy);
		$this->uid = uniqid();
	}
	
	function setuid($uid) {
		$this->uid = $uid;
	}
	
	function getuid() {
		return $this->uid;
	}
	
	function subscribe($channels) {
		if(!is_array($channels)) $channels = func_get_args();
	
		foreach($channels as $channel) {
			$this->proxy->sadd(self::CHANNEL_PREFIX . $channel, $this->uid);
			$this->proxy->hincrby(self::CHANNEL_LIST, $channel, 1);
		}
	}
	
	function unsubscribe($channels=array()) {
		if(!is_array($channels)) $channels = func_get_args();
		
		foreach($channels as $channel) {
			$c = $this->proxy->srem(self::CHANNEL_PREFIX . $channel, $this->uid);
			if($c) {
				$c = $this->proxy->hincrby(self::CHANNEL_LIST, $channel, -1);
				if($c == 0) $this->proxy->hdel(self::CHANNEL_LIST, $channel);
			}
		}
	}
	
	function publish($channel, $message) {
		$subscribers = $this->proxy->smembers(self::CHANNEL_PREFIX . $channel);
		foreach($subscribers as $subscriber) {
			$this->send($subscriber, $message);
		}
		//$this->debug();
		return count($subscribers);
	}
	
	function broadcast($filter, $message) {
		$subscribers = array();
		foreach(array_keys($this->channels()) as $channel) {
			$subscribers += $this->proxy->smembers(self::CHANNEL_PREFIX . $channel);
		}
		var_dump($subscribers);
		foreach(array_unique($subscribers) as $subscriber) {
			$this->send($subscriber, $message);
		}
	}
	
	function send($subscriber, $message) {
		$this->proxy->rpush(self::SUBSCRIBER_PREFIX . $subscriber, array($message));
	}
	
	function channels() {
		return $this->proxy->hgetall(self::CHANNEL_LIST);
	}
	
	function subscribers($channel) {
		return $this->proxy->smembers(self::CHANNEL_PREFIX . $channel);
	}
	
	function psubscribe($patterns) {
		throw new PlodisNotImplementedError;
	}
	
	function punsubscribe($pattern=null) {
		throw new PlodisNotImplementedError;
	}
	
	function poll() {
		return $this->proxy->list->lpop(self::SUBSCRIBER_PREFIX . $this->uid);
	}
	
	function bpoll($timeout=0) {
		return $this->proxy->list->blpop(self::SUBSCRIBER_PREFIX . $this->uid, $timeout);
	}
	
}
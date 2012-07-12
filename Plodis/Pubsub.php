<?php
require_once PLODIS_BASE . "/interfaces/Redis_Pubsub_2_6_0.php";

class Plodis_Pubsub extends Plodis_Group implements Redis_Pubsub_2_6_0 {
	
	const CHANNEL_PREFIX = '_channel_';
	
	const SUBSCRIBER_PREFIX = '_subscriber_';
	
	public $uid;
	
	function __construct($proxy) {
		parent::__construct($proxy);
		$this->uid = uniqid();
	}
	
	private function _channels($channels) {
		foreach($channels as &$channel) {
			$channel = "_channel_" . $channel;
		}
		return $channels;
	}
	
	function subscribe($channels) {
		if(!is_array($channels)) $channels = func_get_args();
	
		foreach($channels as $channel) {
			$this->proxy->list->rpush(self::CHANNEL_PREFIX . $channel, $this->uid);
			//$this->publish($channel, "{$this->uid} joined channel {$channel}");
		}
	}
	
	function unsubscribe($channels=array()) {
		if(!is_array($channels)) $channels = func_get_args();
	
		foreach($channels as $channel) {
			$this->proxy->list->lrem(self::CHANNEL_PREFIX . $channel, 1, $this->uid);
		}
	}
	
	function publish($channel, $message) {
		$subscribers = $this->proxy->list->lrange(self::CHANNEL_PREFIX . $channel, 0, -1);
		foreach($subscribers as $subscriber) {
			$this->proxy->list->rpush(self::SUBSCRIBER_PREFIX . $subscriber, $message);
		}
		//$this->debug();
		return count($subscribers);
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
	
	function bpoll() {
		return $this->proxy->list->blpop(self::SUBSCRIBER_PREFIX . $this->uid);
	}
	
}
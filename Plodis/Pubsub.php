<?php
class Plodis_Pubsub extends Plodis_Group {
	
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
			$this->proxy->group_list->rpush(self::CHANNEL_PREFIX . $channel, $this->uid);
			//$this->publish($channel, "{$this->uid} joined channel {$channel}");
		}
	}
	
	function unsubscribe($channels=array()) {
		if(!is_array($channels)) $channels = func_get_args();
	
		foreach($channels as $channel) {
			$this->proxy->group_list->lrem(self::CHANNEL_PREFIX . $channel, 1, $this->uid);
		}
	}
	
	function publish($channel, $message) {
		$subscribers = $this->proxy->group_list->lrange(self::CHANNEL_PREFIX . $channel, 0, -1);
		foreach($subscribers as $subscriber) {
			$this->proxy->group_list->rpush(self::SUBSCRIBER_PREFIX . $subscriber, $message);
		}
		//$this->debug();
		return count($subscribers);
	}
	
	function psubscribe($patterns) {
		throw new RuntimeException('Not implemented');
	}
	
	function punsubscribe($pattern=null) {
		throw new RuntimeException('Not implemented');
	}
	
	function poll() {
		return $this->proxy->group_list->lpop(self::SUBSCRIBER_PREFIX . $this->uid);
	}
	
	function bpoll() {
		return $this->proxy->group_list->blpop(self::SUBSCRIBER_PREFIX . $this->uid);
	}
	
}
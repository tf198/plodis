<?php
/**
 * Redis pubsub methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface IRedis_Pubsub_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "pubsub";

    /**
     * Listen for messages published to channels matching the given patterns
     *
     * @since 2.0.0
     * @api
     * @group pubsub
     * @link http://redis.io/commands/psubscribe PSUBSCRIBE
     *
     * @param multitype:pattern $patterns (multiple)
     * @return null
     */
    public function psubscribe($patterns);

    /**
     * Post a message to a channel
     *
     * @since 2.0.0
     * @api
     * @group pubsub
     * @link http://redis.io/commands/publish PUBLISH
     *
     * @param string $channel
     * @param string $message
     * @return integer the number of clients that received the message.
     */
    public function publish($channel, $message);

    /**
     * Stop listening for messages posted to channels matching the given patterns
     *
     * @since 2.0.0
     * @api
     * @group pubsub
     * @link http://redis.io/commands/punsubscribe PUNSUBSCRIBE
     *
     * @param string $pattern (multiple)
     * @return null
     */
    public function punsubscribe($pattern=null);

    /**
     * Listen for messages published to the given channels
     *
     * @since 2.0.0
     * @api
     * @group pubsub
     * @link http://redis.io/commands/subscribe SUBSCRIBE
     *
     * @param multitype:string $channels (multiple)
     * @return null
     */
    public function subscribe($channels);

    /**
     * Stop listening for messages posted to the given channels
     *
     * @since 2.0.0
     * @api
     * @group pubsub
     * @link http://redis.io/commands/unsubscribe UNSUBSCRIBE
     *
     * @param string $channel (multiple)
     * @return null
     */
    public function unsubscribe($channel=null);

}

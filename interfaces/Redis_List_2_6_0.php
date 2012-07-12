<?php
/**
 * Redis list methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface Redis_List_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "list";

    /**
     * Remove and get the first element in a list, or block until one is available
     *
     * @since 2.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/blpop BLPOP
     *
     * @param string $key (multiple)
     * @param integer $timeout
     * @return null no documentation available
     */
    public function blpop($key, $timeout);

    /**
     * Remove and get the last element in a list, or block until one is available
     *
     * @since 2.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/brpop BRPOP
     *
     * @param string $key (multiple)
     * @param integer $timeout
     * @return null no documentation available
     */
    public function brpop($key, $timeout);

    /**
     * Pop a value from a list, push it to another list and return it; or block until one is available
     *
     * @since 2.2.0
     * @api
     * @group list
     * @link http://redis.io/commands/brpoplpush BRPOPLPUSH
     *
     * @param string $source
     * @param string $destination
     * @param integer $timeout
     * @return null no documentation available
     */
    public function brpoplpush($source, $destination, $timeout);

    /**
     * Get an element from a list by its index
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lindex LINDEX
     *
     * @param string $key
     * @param integer $index
     * @return null no documentation available
     */
    public function lindex($key, $index);

    /**
     * Insert an element before or after another element in a list
     *
     * @since 2.2.0
     * @api
     * @group list
     * @link http://redis.io/commands/linsert LINSERT
     *
     * @param string $key
     * @param string $where [ BEFORE, AFTER ]
     * @param string $pivot
     * @param string $value
     * @return null no documentation available
     */
    public function linsert($key, $where, $pivot, $value);

    /**
     * Get the length of a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/llen LLEN
     *
     * @param string $key
     * @return null no documentation available
     */
    public function llen($key);

    /**
     * Remove and get the first element in a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lpop LPOP
     *
     * @param string $key
     * @return null no documentation available
     */
    public function lpop($key);

    /**
     * Prepend one or multiple values to a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lpush LPUSH
     *
     * @param string $key
     * @param string $value (multiple)
     * @return null no documentation available
     */
    public function lpush($key, $value);

    /**
     * Prepend a value to a list, only if the list exists
     *
     * @since 2.2.0
     * @api
     * @group list
     * @link http://redis.io/commands/lpushx LPUSHX
     *
     * @param string $key
     * @param string $value
     * @return null no documentation available
     */
    public function lpushx($key, $value);

    /**
     * Get a range of elements from a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lrange LRANGE
     *
     * @param string $key
     * @param integer $start
     * @param integer $stop
     * @return null no documentation available
     */
    public function lrange($key, $start, $stop);

    /**
     * Remove elements from a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lrem LREM
     *
     * @param string $key
     * @param integer $count
     * @param string $value
     * @return null no documentation available
     */
    public function lrem($key, $count, $value);

    /**
     * Set the value of an element in a list by its index
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/lset LSET
     *
     * @param string $key
     * @param integer $index
     * @param string $value
     * @return null no documentation available
     */
    public function lset($key, $index, $value);

    /**
     * Trim a list to the specified range
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/ltrim LTRIM
     *
     * @param string $key
     * @param integer $start
     * @param integer $stop
     * @return null no documentation available
     */
    public function ltrim($key, $start, $stop);

    /**
     * Remove and get the last element in a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/rpop RPOP
     *
     * @param string $key
     * @return null no documentation available
     */
    public function rpop($key);

    /**
     * Remove the last element in a list, append it to another list and return it
     *
     * @since 1.2.0
     * @api
     * @group list
     * @link http://redis.io/commands/rpoplpush RPOPLPUSH
     *
     * @param string $source
     * @param string $destination
     * @return null no documentation available
     */
    public function rpoplpush($source, $destination);

    /**
     * Append one or multiple values to a list
     *
     * @since 1.0.0
     * @api
     * @group list
     * @link http://redis.io/commands/rpush RPUSH
     *
     * @param string $key
     * @param string $value (multiple)
     * @return null no documentation available
     */
    public function rpush($key, $value);

    /**
     * Append a value to a list, only if the list exists
     *
     * @since 2.2.0
     * @api
     * @group list
     * @link http://redis.io/commands/rpushx RPUSHX
     *
     * @param string $key
     * @param string $value
     * @return null no documentation available
     */
    public function rpushx($key, $value);

}

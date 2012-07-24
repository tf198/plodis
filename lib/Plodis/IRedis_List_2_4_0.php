<?php
/**
 * Redis list methods for version 2.4.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.4.0
 */
interface IRedis_List_2_4_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.4.0";
	
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
     * @return multitype:string specifically
     *   
     *   * A `null` multi-bulk when no element could be popped and the timeout expired.
     *   * A two-element multi-bulk with the first element being the name of the key
     *     where an element was popped and the second element being the value of the
     *     popped element.
     *
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
     * @return multitype:string specifically
     *   
     *   * A `null` multi-bulk when no element could be popped and the timeout expired.
     *   * A two-element multi-bulk with the first element being the name of the key
     *     where an element was popped and the second element being the value of the
     *     popped element.
     *
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
     * @return string the element being popped from `source` and pushed to `destination`.
     *   If `timeout` is reached, a @nil-reply is returned.
     *   
     *   ## Pattern Reliable queue
     *   
     *   Please see the pattern description in the `RPOPLPUSH` documentation.
     *   
     *   ## Pattern Circular list
     *   
     *   Please see the pattern description in the `RPOPLPUSH` documentation.
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
     * @return string the requested element, or `null` when `index` is out of range.
     *
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
     * @return integer the length of the list after the insert operation, or `-1` when
     *   the value `pivot` was not found.
     *
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
     * @return integer the length of the list at `key`.
     *
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
     * @return string the value of the first element, or `null` when `key` does not exist.
     *
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
     * @return integer the length of the list after the push operations.
     *
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
     * @return integer the length of the list after the push operation.
     *
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
     * @return multitype:string list of elements in the specified range.
     *
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
     * @return integer the number of removed elements.
     *
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
     * @return null
     *
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
     * @return null
     *
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
     * @return string the value of the last element, or `null` when `key` does not exist.
     *
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
     * @return string the element being popped and pushed.
     *
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
     * @return integer the length of the list after the push operation.
     *
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
     * @return integer the length of the list after the push operation.
     *
     */
    public function rpushx($key, $value);

}

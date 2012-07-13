<?php
/**
 * Redis hash methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface Redis_Hash_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "hash";

    /**
     * Delete one or more hash fields
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hdel HDEL
     *
     * @param string $key
     * @param string $field (multiple)
     * @return null no documentation available
     */
    public function hdel($key, $field);

    /**
     * Determine if a hash field exists
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hexists HEXISTS
     *
     * @param string $key
     * @param string $field
     * @return null no documentation available
     */
    public function hexists($key, $field);

    /**
     * Get the value of a hash field
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hget HGET
     *
     * @param string $key
     * @param string $field
     * @return null no documentation available
     */
    public function hget($key, $field);

    /**
     * Get all the fields and values in a hash
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hgetall HGETALL
     *
     * @param string $key
     * @return null no documentation available
     */
    public function hgetall($key);

    /**
     * Increment the integer value of a hash field by the given number
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hincrby HINCRBY
     *
     * @param string $key
     * @param string $field
     * @param integer $increment
     * @return null no documentation available
     */
    public function hincrby($key, $field, $increment);

    /**
     * Increment the float value of a hash field by the given amount
     *
     * @since 2.6.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hincrbyfloat HINCRBYFLOAT
     *
     * @param string $key
     * @param string $field
     * @param double $increment
     * @return null no documentation available
     */
    public function hincrbyfloat($key, $field, $increment);

    /**
     * Get all the fields in a hash
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hkeys HKEYS
     *
     * @param string $key
     * @return null no documentation available
     */
    public function hkeys($key);

    /**
     * Get the number of fields in a hash
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hlen HLEN
     *
     * @param string $key
     * @return null no documentation available
     */
    public function hlen($key);

    /**
     * Get the values of all the given hash fields
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hmget HMGET
     *
     * @param string $key
     * @param string $field (multiple)
     * @return null no documentation available
     */
    public function hmget($key, $field);

    /**
     * Set multiple hash fields to multiple values
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hmset HMSET
     *
     * @param string $key
     * @param multitype:string $fields (multiple)
     * @return null no documentation available
     */
    public function hmset($key, $fields);

    /**
     * Set the string value of a hash field
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hset HSET
     *
     * @param string $key
     * @param string $field
     * @param string $value
     * @return null no documentation available
     */
    public function hset($key, $field, $value);

    /**
     * Set the value of a hash field, only if the field does not exist
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hsetnx HSETNX
     *
     * @param string $key
     * @param string $field
     * @param string $value
     * @return null no documentation available
     */
    public function hsetnx($key, $field, $value);

    /**
     * Get all the values in a hash
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hvals HVALS
     *
     * @param string $key
     * @return null no documentation available
     */
    public function hvals($key);

}

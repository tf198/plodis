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
     * @return integer the number of fields that were removed from the hash, not
     *   including specified but non existing fields.
     *
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
     * @return integer specifically
     *   
     *   * `1` if the hash contains `field`.
     *   * `0` if the hash does not contain `field`, or `key` does not exist.
     *
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
     * @return string the value associated with `field`, or `null` when `field` is not
     *   present in the hash or `key` does not exist.
     *
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
     * @return multitype:string list of fields and their values stored in the hash, or an
     *   empty list when `key` does not exist.
     *
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
     * @return integer the value at `field` after the increment operation.
     *
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
     * @return string the value of `field` after the increment.
     *
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
     * @return multitype:string list of fields in the hash, or an empty list when `key` does
     *   not exist.
     *
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
     * @return integer number of fields in the hash, or `0` when `key` does not exist.
     *
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
     * @return multitype:string list of values associated with the given fields, in the same
     *   order as they are requested.
     *   
     *   ```cli
     *   HSET myhash field1 "Hello"
     *   HSET myhash field2 "World"
     *   HMGET myhash field1 field2 nofield
     *   ```
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
     * @return null
     *
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
     * @return integer specifically
     *   
     *   * `1` if `field` is a new field in the hash and `value` was set.
     *   * `0` if `field` already exists in the hash and the value was updated.
     *
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
     * @return integer specifically
     *   
     *   * `1` if `field` is a new field in the hash and `value` was set.
     *   * `0` if `field` already exists in the hash and no operation was performed.
     *
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
     * @return multitype:string list of values in the hash, or an empty list when `key` does
     *   not exist.
     *
     */
    public function hvals($key);

}

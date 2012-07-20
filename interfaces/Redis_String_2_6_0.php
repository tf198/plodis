<?php
/**
 * Redis string methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface Redis_String_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "string";

    /**
     * Append a value to a key
     *
     * @since 2.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/append APPEND
     *
     * @param string $key
     * @param string $value
     * @return integer the length of the string after the append operation.
     *
     */
    public function append($key, $value);

    /**
     * Count set bits in a string
     *
     * @since 2.6.0
     * @api
     * @group string
     * @link http://redis.io/commands/bitcount BITCOUNT
     *
     * @param string $key
     * @param integer $start
     * @param integer $end
     * @return integer
     *   
     *   The number of bits set to 1.
     *
     */
    public function bitcount($key, $start=null, $end=null);

    /**
     * Perform bitwise operations between strings
     *
     * @since 2.6.0
     * @api
     * @group string
     * @link http://redis.io/commands/bitop BITOP
     *
     * @param string $operation
     * @param string $destkey
     * @param string $key (multiple)
     * @return integer
     *   
     *   The size of the string stored in the destination key, that is equal to the
     *   size of the longest input string.
     *
     */
    public function bitop($operation, $destkey, $key);

    /**
     * Decrement the integer value of a key by one
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/decr DECR
     *
     * @param string $key
     * @return integer the value of `key` after the decrement
     *
     */
    public function decr($key);

    /**
     * Decrement the integer value of a key by the given number
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/decrby DECRBY
     *
     * @param string $key
     * @param integer $decrement
     * @return integer the value of `key` after the decrement
     *
     */
    public function decrby($key, $decrement);

    /**
     * Get the value of a key
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/get GET
     *
     * @param string $key
     * @return string the value of `key`, or `null` when `key` does not exist.
     *
     */
    public function get($key);

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @since 2.2.0
     * @api
     * @group string
     * @link http://redis.io/commands/getbit GETBIT
     *
     * @param string $key
     * @param integer $offset
     * @return integer the bit value stored at _offset_.
     *
     */
    public function getbit($key, $offset);

    /**
     * Get a substring of the string stored at a key
     *
     * @since 2.4.0
     * @api
     * @group string
     * @link http://redis.io/commands/getrange GETRANGE
     *
     * @param string $key
     * @param integer $start
     * @param integer $end
     * @return string
     *
     */
    public function getrange($key, $start, $end);

    /**
     * Set the string value of a key and return its old value
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/getset GETSET
     *
     * @param string $key
     * @param string $value
     * @return string the old value stored at `key`, or `null` when `key` did not exist.
     *
     */
    public function getset($key, $value);

    /**
     * Increment the integer value of a key by one
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/incr INCR
     *
     * @param string $key
     * @return integer the value of `key` after the increment
     *
     */
    public function incr($key);

    /**
     * Increment the integer value of a key by the given amount
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/incrby INCRBY
     *
     * @param string $key
     * @param integer $increment
     * @return integer the value of `key` after the increment
     *
     */
    public function incrby($key, $increment);

    /**
     * Increment the float value of a key by the given amount
     *
     * @since 2.6.0
     * @api
     * @group string
     * @link http://redis.io/commands/incrbyfloat INCRBYFLOAT
     *
     * @param string $key
     * @param double $increment
     * @return string the value of `key` after the increment.
     *
     */
    public function incrbyfloat($key, $increment);

    /**
     * Get the values of all the given keys
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/mget MGET
     *
     * @param string $key (multiple)
     * @return multitype:string list of values at the specified keys.
     *
     */
    public function mget($key);

    /**
     * Set multiple keys to multiple values
     *
     * @since 1.0.1
     * @api
     * @group string
     * @link http://redis.io/commands/mset MSET
     *
     * @param multitype:key $keys (multiple)
     * @return null always `true` since `MSET` can't fail.
     *
     */
    public function mset($keys);

    /**
     * Set multiple keys to multiple values, only if none of the keys exist
     *
     * @since 1.0.1
     * @api
     * @group string
     * @link http://redis.io/commands/msetnx MSETNX
     *
     * @param multitype:key $keys (multiple)
     * @return integer specifically
     *   
     *   * `1` if the all the keys were set.
     *   * `0` if no key was set (at least one key already existed).
     *
     */
    public function msetnx($keys);

    /**
     * Set the value and expiration in milliseconds of a key
     *
     * @since 2.6.0
     * @api
     * @group string
     * @link http://redis.io/commands/psetex PSETEX
     *
     * @param string $key
     * @param integer $milliseconds
     * @param string $value
     * @return null
     */
    public function psetex($key, $milliseconds, $value);

    /**
     * Set the string value of a key
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/set SET
     *
     * @param string $key
     * @param string $value
     * @return null always `true` since `SET` can't fail.
     *
     */
    public function set($key, $value);

    /**
     * Sets or clears the bit at offset in the string value stored at key
     *
     * @since 2.2.0
     * @api
     * @group string
     * @link http://redis.io/commands/setbit SETBIT
     *
     * @param string $key
     * @param integer $offset
     * @param string $value
     * @return integer the original bit value stored at _offset_.
     *
     */
    public function setbit($key, $offset, $value);

    /**
     * Set the value and expiration of a key
     *
     * @since 2.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/setex SETEX
     *
     * @param string $key
     * @param integer $seconds
     * @param string $value
     * @return null
     *
     */
    public function setex($key, $seconds, $value);

    /**
     * Set the value of a key, only if the key does not exist
     *
     * @since 1.0.0
     * @api
     * @group string
     * @link http://redis.io/commands/setnx SETNX
     *
     * @param string $key
     * @param string $value
     * @return integer specifically
     *   
     *   * `1` if the key was set
     *   * `0` if the key was not set
     *
     */
    public function setnx($key, $value);

    /**
     * Overwrite part of a string at key starting at the specified offset
     *
     * @since 2.2.0
     * @api
     * @group string
     * @link http://redis.io/commands/setrange SETRANGE
     *
     * @param string $key
     * @param integer $offset
     * @param string $value
     * @return integer the length of the string after it was modified by the command.
     *
     */
    public function setrange($key, $offset, $value);

    /**
     * Get the length of the value stored in a key
     *
     * @since 2.2.0
     * @api
     * @group string
     * @link http://redis.io/commands/strlen STRLEN
     *
     * @param string $key
     * @return integer the length of the string at `key`, or `0` when `key` does not
     *   exist.
     *
     */
    public function strlen($key);

}

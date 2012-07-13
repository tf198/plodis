<?php
/**
 * @package Plodis
 * @author Tris Forster
 */

require_once "Plodis/Proxy.php";

/**
 * Proxy for Redis version 2.6.0 methods.  Dispatches calls to the group class
 * This class is automatically generated from the Redis docs on github.
 *
 * Included modules: connection, generic, string, list, hash, pubsub
 *
 * @link https://github.com/antirez/redis-doc
 * @package Plodis
 * @author Tris Forster
 * @version 2.6.0
 */
class Plodis extends Plodis_Proxy {

	/**
	 * Redis server version
	 * @var string
	 */
	const REDIS_VERSION = "2.6.0";

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
    public function append($key, $value) {
        return $this->string->append($key, $value);
    }

    /**
     * Authenticate to the server
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/auth AUTH
     *
     * @param string $password
     * @return null
     */
    public function auth($password) {
        return $this->connection->auth($password);
    }

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
    public function bitcount($key, $start=null, $end=null) {
        return $this->string->bitcount($key, $start, $end);
    }

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
    public function bitop($operation, $destkey, $key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 2);
        return $this->string->bitop($operation, $destkey, $key);
    }

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
    public function blpop($key, $timeout) {
        return $this->list->blpop($key, $timeout);
    }

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
    public function brpop($key, $timeout) {
        return $this->list->brpop($key, $timeout);
    }

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
    public function brpoplpush($source, $destination, $timeout) {
        return $this->list->brpoplpush($source, $destination, $timeout);
    }

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
    public function decr($key) {
        return $this->string->decr($key);
    }

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
    public function decrby($key, $decrement) {
        return $this->string->decrby($key, $decrement);
    }

    /**
     * Delete a key
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/del DEL
     *
     * @param string $key (multiple)
     * @return integer The number of keys that were removed.
     *
     */
    public function del($key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 0);
        return $this->generic->del($key);
    }

    /**
     * Return a serialized version of the value stored at the specified key.
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/dump DUMP
     *
     * @param string $key
     * @return string the serialized value.
     *
     */
    public function dump($key) {
        return $this->generic->dump($key);
    }

    /**
     * Echo the given string
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/echo ECHO
     *
     * @param string $message
     * @return string
     *
     */
    public function _echo($message) {
        return $this->connection->_echo($message);
    }

    /**
     * Determine if a key exists
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/exists EXISTS
     *
     * @param string $key
     * @return integer specifically
     *   
     *   * `1` if the key exists.
     *   * `0` if the key does not exist.
     *
     */
    public function exists($key) {
        return $this->generic->exists($key);
    }

    /**
     * Set a key's time to live in seconds
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/expire EXPIRE
     *
     * @param string $key
     * @param integer $seconds
     * @return integer specifically
     *   
     *   * `1` if the timeout was set.
     *   * `0` if `key` does not exist or the timeout could not be set.
     *
     */
    public function expire($key, $seconds) {
        return $this->generic->expire($key, $seconds);
    }

    /**
     * Set the expiration for a key as a UNIX timestamp
     *
     * @since 1.2.0
     * @api
     * @group generic
     * @link http://redis.io/commands/expireat EXPIREAT
     *
     * @param string $key
     * @param integer $timestamp
     * @return integer specifically
     *   
     *   * `1` if the timeout was set.
     *   * `0` if `key` does not exist or the timeout could not be set (see `EXPIRE`).
     *
     */
    public function expireat($key, $timestamp) {
        return $this->generic->expireat($key, $timestamp);
    }

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
    public function get($key) {
        return $this->string->get($key);
    }

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
    public function getbit($key, $offset) {
        return $this->string->getbit($key, $offset);
    }

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
    public function getrange($key, $start, $end) {
        return $this->string->getrange($key, $start, $end);
    }

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
    public function getset($key, $value) {
        return $this->string->getset($key, $value);
    }

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
    public function hdel($key, $field) {
        if(!is_array($field)) $field = array_slice(func_get_args(), 1);
        return $this->hash->hdel($key, $field);
    }

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
    public function hexists($key, $field) {
        return $this->hash->hexists($key, $field);
    }

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
    public function hget($key, $field) {
        return $this->hash->hget($key, $field);
    }

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
    public function hgetall($key) {
        return $this->hash->hgetall($key);
    }

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
    public function hincrby($key, $field, $increment) {
        return $this->hash->hincrby($key, $field, $increment);
    }

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
    public function hincrbyfloat($key, $field, $increment) {
        return $this->hash->hincrbyfloat($key, $field, $increment);
    }

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
    public function hkeys($key) {
        return $this->hash->hkeys($key);
    }

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
    public function hlen($key) {
        return $this->hash->hlen($key);
    }

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
    public function hmget($key, $field) {
        if(!is_array($field)) $field = array_slice(func_get_args(), 1);
        return $this->hash->hmget($key, $field);
    }

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
    public function hmset($key, $fields) {
        if(!is_array($fields)) $fields = array_slice(func_get_args(), 1);
        return $this->hash->hmset($key, $fields);
    }

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
    public function hset($key, $field, $value) {
        return $this->hash->hset($key, $field, $value);
    }

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
    public function hsetnx($key, $field, $value) {
        return $this->hash->hsetnx($key, $field, $value);
    }

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
    public function hvals($key) {
        return $this->hash->hvals($key);
    }

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
    public function incr($key) {
        return $this->string->incr($key);
    }

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
    public function incrby($key, $increment) {
        return $this->string->incrby($key, $increment);
    }

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
    public function incrbyfloat($key, $increment) {
        return $this->string->incrbyfloat($key, $increment);
    }

    /**
     * Find all keys matching the given pattern
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/keys KEYS
     *
     * @param string $pattern
     * @return multitype:string list of keys matching `pattern`.
     *
     */
    public function keys($pattern=null) {
        return $this->generic->keys($pattern);
    }

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
    public function lindex($key, $index) {
        return $this->list->lindex($key, $index);
    }

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
    public function linsert($key, $where, $pivot, $value) {
        return $this->list->linsert($key, $where, $pivot, $value);
    }

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
    public function llen($key) {
        return $this->list->llen($key);
    }

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
    public function lpop($key) {
        return $this->list->lpop($key);
    }

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
    public function lpush($key, $value) {
        if(!is_array($value)) $value = array_slice(func_get_args(), 1);
        return $this->list->lpush($key, $value);
    }

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
    public function lpushx($key, $value) {
        return $this->list->lpushx($key, $value);
    }

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
    public function lrange($key, $start, $stop) {
        return $this->list->lrange($key, $start, $stop);
    }

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
    public function lrem($key, $count, $value) {
        return $this->list->lrem($key, $count, $value);
    }

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
    public function lset($key, $index, $value) {
        return $this->list->lset($key, $index, $value);
    }

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
    public function ltrim($key, $start, $stop) {
        return $this->list->ltrim($key, $start, $stop);
    }

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
    public function mget($key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 0);
        return $this->string->mget($key);
    }

    /**
     * Atomically transfer a key from a Redis instance to another one.
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/migrate MIGRATE
     *
     * @param string $host
     * @param string $port
     * @param string $key
     * @param integer $destination_db
     * @param integer $timeout
     * @return null The command returns OK on success.
     */
    public function migrate($host, $port, $key, $destination_db, $timeout) {
        return $this->generic->migrate($host, $port, $key, $destination_db, $timeout);
    }

    /**
     * Move a key to another database
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/move MOVE
     *
     * @param string $key
     * @param integer $db
     * @return integer specifically
     *   
     *   * `1` if `key` was moved.
     *   * `0` if `key` was not moved.
     */
    public function move($key, $db) {
        return $this->generic->move($key, $db);
    }

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
    public function mset($keys) {
        if(!is_array($keys)) $keys = array_slice(func_get_args(), 0);
        return $this->string->mset($keys);
    }

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
    public function msetnx($keys) {
        if(!is_array($keys)) $keys = array_slice(func_get_args(), 0);
        return $this->string->msetnx($keys);
    }

    /**
     * Inspect the internals of Redis objects
     *
     * @since 2.2.3
     * @api
     * @group generic
     * @link http://redis.io/commands/object OBJECT
     *
     * @param string $subcommand
     * @param string $arguments (multiple)
     * @return @examples
     *   
     *   ```
     *   redis> lpush mylist "Hello World"
     *   (integer) 4
     *   redis> object refcount mylist
     *   (integer) 1
     *   redis> object encoding mylist
     *   "ziplist"
     *   redis> object idletime mylist
     *   (integer) 10
     *   ```
     *   
     *   In the following example you can see how the encoding changes once Redis is no
     *   longer able to use the space saving encoding.
     *   
     *   ```
     *   redis> set foo 1000
     *   OK
     *   redis> object encoding foo
     *   "int"
     *   redis> append foo bar
     *   (integer) 7
     *   redis> get foo
     *   "1000bar"
     *   redis> object encoding foo
     *   "raw"
     *   ```
     */
    public function object($subcommand, $arguments=null) {
        if(!is_array($arguments)) $arguments = array_slice(func_get_args(), 1);
        return $this->generic->object($subcommand, $arguments);
    }

    /**
     * Remove the expiration from a key
     *
     * @since 2.2.0
     * @api
     * @group generic
     * @link http://redis.io/commands/persist PERSIST
     *
     * @param string $key
     * @return integer specifically
     *   
     *   * `1` if the timeout was removed.
     *   * `0` if `key` does not exist or does not have an associated timeout.
     *
     */
    public function persist($key) {
        return $this->generic->persist($key);
    }

    /**
     * Set a key's time to live in milliseconds
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/pexpire PEXPIRE
     *
     * @param string $key
     * @param integer $milliseconds
     * @return null
     */
    public function pexpire($key, $milliseconds) {
        return $this->generic->pexpire($key, $milliseconds);
    }

    /**
     * Set the expiration for a key as a UNIX timestamp specified in milliseconds
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/pexpireat PEXPIREAT
     *
     * @param string $key
     * @param integer $milliseconds_timestamp
     * @return integer specifically
     *   
     *   * `1` if the timeout was set.
     *   * `0` if `key` does not exist or the timeout could not be set (see `EXPIRE`).
     *
     */
    public function pexpireat($key, $milliseconds_timestamp) {
        return $this->generic->pexpireat($key, $milliseconds_timestamp);
    }

    /**
     * Ping the server
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/ping PING
     *
     * @return null
     *
     */
    public function ping() {
        return $this->connection->ping();
    }

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
    public function psetex($key, $milliseconds, $value) {
        return $this->string->psetex($key, $milliseconds, $value);
    }

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
    public function psubscribe($patterns) {
        if(!is_array($patterns)) $patterns = array_slice(func_get_args(), 0);
        return $this->pubsub->psubscribe($patterns);
    }

    /**
     * Get the time to live for a key in milliseconds
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/pttl PTTL
     *
     * @param string $key
     * @return integer Time to live in milliseconds or `-1` when `key` does not exist
     *   or does not have a timeout.
     *
     */
    public function pttl($key) {
        return $this->generic->pttl($key);
    }

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
    public function publish($channel, $message) {
        return $this->pubsub->publish($channel, $message);
    }

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
    public function punsubscribe($pattern=null) {
        if(!is_array($pattern)) $pattern = array_slice(func_get_args(), 0);
        return $this->pubsub->punsubscribe($pattern);
    }

    /**
     * Close the connection
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/quit QUIT
     *
     * @return null always OK.
     */
    public function quit() {
        return $this->connection->quit();
    }

    /**
     * Return a random key from the keyspace
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/randomkey RANDOMKEY
     *
     * @return string the random key, or `null` when the database is empty.
     */
    public function randomkey() {
        return $this->generic->randomkey();
    }

    /**
     * Rename a key
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/rename RENAME
     *
     * @param string $key
     * @param string $newkey
     * @return null
     *
     */
    public function rename($key, $newkey) {
        return $this->generic->rename($key, $newkey);
    }

    /**
     * Rename a key, only if the new key does not exist
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/renamenx RENAMENX
     *
     * @param string $key
     * @param string $newkey
     * @return integer specifically
     *   
     *   * `1` if `key` was renamed to `newkey`.
     *   * `0` if `newkey` already exists.
     *
     */
    public function renamenx($key, $newkey) {
        return $this->generic->renamenx($key, $newkey);
    }

    /**
     * Create a key using the provided serialized value, previously obtained using DUMP.
     *
     * @since 2.6.0
     * @api
     * @group generic
     * @link http://redis.io/commands/restore RESTORE
     *
     * @param string $key
     * @param integer $ttl
     * @param string $serialized_value
     * @return null The command returns OK on success.
     *
     */
    public function restore($key, $ttl, $serialized_value) {
        return $this->generic->restore($key, $ttl, $serialized_value);
    }

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
    public function rpop($key) {
        return $this->list->rpop($key);
    }

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
    public function rpoplpush($source, $destination) {
        return $this->list->rpoplpush($source, $destination);
    }

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
    public function rpush($key, $value) {
        if(!is_array($value)) $value = array_slice(func_get_args(), 1);
        return $this->list->rpush($key, $value);
    }

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
    public function rpushx($key, $value) {
        return $this->list->rpushx($key, $value);
    }

    /**
     * Change the selected database for the current connection
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/select SELECT
     *
     * @param integer $index
     * @return null
     */
    public function select($index) {
        return $this->connection->select($index);
    }

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
    public function set($key, $value) {
        return $this->string->set($key, $value);
    }

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
    public function setbit($key, $offset, $value) {
        return $this->string->setbit($key, $offset, $value);
    }

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
    public function setex($key, $seconds, $value) {
        return $this->string->setex($key, $seconds, $value);
    }

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
    public function setnx($key, $value) {
        return $this->string->setnx($key, $value);
    }

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
    public function setrange($key, $offset, $value) {
        return $this->string->setrange($key, $offset, $value);
    }

    /**
     * Sort the elements in a list, set or sorted set
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/sort SORT
     *
     * @param string $key
     * @param string $by
     * @param multitype:integer $limit
     * @param string $get (multiple)
     * @param string $order [ ASC, DESC ]
     * @param string $sorting [ ALPHA ]
     * @param string $store
     * @return multitype:string list of sorted elements.
     */
    public function sort($key, $by=null, $limit=null, $get=null, $order=null, $sorting=null, $store=null) {
        return $this->generic->sort($key, $by, $limit, $get, $order, $sorting, $store);
    }

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
    public function strlen($key) {
        return $this->string->strlen($key);
    }

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
    public function subscribe($channels) {
        if(!is_array($channels)) $channels = array_slice(func_get_args(), 0);
        return $this->pubsub->subscribe($channels);
    }

    /**
     * Get the time to live for a key
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/ttl TTL
     *
     * @param string $key
     * @return integer TTL in seconds or `-1` when `key` does not exist or does not
     *   have a timeout.
     *
     */
    public function ttl($key) {
        return $this->generic->ttl($key);
    }

    /**
     * Determine the type stored at key
     *
     * @since 1.0.0
     * @api
     * @group generic
     * @link http://redis.io/commands/type TYPE
     *
     * @param string $key
     * @return string type of `key`, or `none` when `key` does not exist.
     */
    public function type($key) {
        return $this->generic->type($key);
    }

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
    public function unsubscribe($channel=null) {
        if(!is_array($channel)) $channel = array_slice(func_get_args(), 0);
        return $this->pubsub->unsubscribe($channel);
    }

}

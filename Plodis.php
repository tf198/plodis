<?php
/**
 * @package Plodis
 * @author Tris Forster
 */

require_once "Plodis/Proxy.php";

/**
 * Proxy for Redis methods.  Dispatches calls to the group class
 * This class is automatically generated from the Redis docs on github.
 *
 * Excludes methods in groups: server, connection, scripting, transactions, set, sorted_set, hash
 * Version emulation: 2.6.0
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
     * @return null no documentation available
     */
    public function append($key, $value) {
        return $this->plodis_string->append($key, $value);
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
     * @return null no documentation available
     */
    public function bitcount($key, $start=null, $end=null) {
        return $this->plodis_string->bitcount($key, $start, $end);
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
     * @return null no documentation available
     */
    public function bitop($operation, $destkey, $key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 2);
        return $this->plodis_string->bitop($operation, $destkey, $key);
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
     * @return null no documentation available
     */
    public function blpop($key, $timeout) {
        return $this->plodis_list->blpop($key, $timeout);
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
     * @return null no documentation available
     */
    public function brpop($key, $timeout) {
        return $this->plodis_list->brpop($key, $timeout);
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
     * @return null no documentation available
     */
    public function brpoplpush($source, $destination, $timeout) {
        return $this->plodis_list->brpoplpush($source, $destination, $timeout);
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
     * @return null no documentation available
     */
    public function decr($key) {
        return $this->plodis_string->decr($key);
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
     * @return null no documentation available
     */
    public function decrby($key, $decrement) {
        return $this->plodis_string->decrby($key, $decrement);
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
     * @return null no documentation available
     */
    public function del($key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 0);
        return $this->plodis_generic->del($key);
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
     * @return null no documentation available
     */
    public function dump($key) {
        return $this->plodis_generic->dump($key);
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
     * @return null no documentation available
     */
    public function exists($key) {
        return $this->plodis_generic->exists($key);
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
     * @return null no documentation available
     */
    public function expire($key, $seconds) {
        return $this->plodis_generic->expire($key, $seconds);
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
     * @param posix time $timestamp
     * @return null no documentation available
     */
    public function expireat($key, $timestamp) {
        return $this->plodis_generic->expireat($key, $timestamp);
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
     * @return null no documentation available
     */
    public function get($key) {
        return $this->plodis_string->get($key);
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
     * @return null no documentation available
     */
    public function getbit($key, $offset) {
        return $this->plodis_string->getbit($key, $offset);
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
     * @return null no documentation available
     */
    public function getrange($key, $start, $end) {
        return $this->plodis_string->getrange($key, $start, $end);
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
     * @return null no documentation available
     */
    public function getset($key, $value) {
        return $this->plodis_string->getset($key, $value);
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
     * @return null no documentation available
     */
    public function incr($key) {
        return $this->plodis_string->incr($key);
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
     * @return null no documentation available
     */
    public function incrby($key, $increment) {
        return $this->plodis_string->incrby($key, $increment);
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
     * @return null no documentation available
     */
    public function incrbyfloat($key, $increment) {
        return $this->plodis_string->incrbyfloat($key, $increment);
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
     * @return null no documentation available
     */
    public function keys($pattern=null) {
        return $this->plodis_generic->keys($pattern);
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
     * @return null no documentation available
     */
    public function lindex($key, $index) {
        return $this->plodis_list->lindex($key, $index);
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
     * @return null no documentation available
     */
    public function linsert($key, $where, $pivot, $value) {
        return $this->plodis_list->linsert($key, $where, $pivot, $value);
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
     * @return null no documentation available
     */
    public function llen($key) {
        return $this->plodis_list->llen($key);
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
     * @return null no documentation available
     */
    public function lpop($key) {
        return $this->plodis_list->lpop($key);
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
     * @return null no documentation available
     */
    public function lpush($key, $value) {
        if(!is_array($value)) $value = array_slice(func_get_args(), 1);
        return $this->plodis_list->lpush($key, $value);
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
     * @return null no documentation available
     */
    public function lpushx($key, $value) {
        return $this->plodis_list->lpushx($key, $value);
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
     * @return null no documentation available
     */
    public function lrange($key, $start, $stop) {
        return $this->plodis_list->lrange($key, $start, $stop);
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
     * @return null no documentation available
     */
    public function lrem($key, $count, $value) {
        return $this->plodis_list->lrem($key, $count, $value);
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
     * @return null no documentation available
     */
    public function lset($key, $index, $value) {
        return $this->plodis_list->lset($key, $index, $value);
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
     * @return null no documentation available
     */
    public function ltrim($key, $start, $stop) {
        return $this->plodis_list->ltrim($key, $start, $stop);
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
     * @return null no documentation available
     */
    public function mget($key) {
        if(!is_array($key)) $key = array_slice(func_get_args(), 0);
        return $this->plodis_string->mget($key);
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
     * @return null no documentation available
     */
    public function migrate($host, $port, $key, $destination_db, $timeout) {
        return $this->plodis_generic->migrate($host, $port, $key, $destination_db, $timeout);
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
     * @return null no documentation available
     */
    public function move($key, $db) {
        return $this->plodis_generic->move($key, $db);
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
     * @return null no documentation available
     */
    public function mset($keys) {
        if(!is_array($keys)) $keys = array_slice(func_get_args(), 0);
        return $this->plodis_string->mset($keys);
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
     * @return null no documentation available
     */
    public function msetnx($keys) {
        if(!is_array($keys)) $keys = array_slice(func_get_args(), 0);
        return $this->plodis_string->msetnx($keys);
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
     * @return null no documentation available
     */
    public function object($subcommand, $arguments=null) {
        if(!is_array($arguments)) $arguments = array_slice(func_get_args(), 1);
        return $this->plodis_generic->object($subcommand, $arguments);
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
     * @return null no documentation available
     */
    public function persist($key) {
        return $this->plodis_generic->persist($key);
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
     * @return null no documentation available
     */
    public function pexpire($key, $milliseconds) {
        return $this->plodis_generic->pexpire($key, $milliseconds);
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
     * @param posix time $milliseconds_timestamp
     * @return null no documentation available
     */
    public function pexpireat($key, $milliseconds_timestamp) {
        return $this->plodis_generic->pexpireat($key, $milliseconds_timestamp);
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
     * @return null no documentation available
     */
    public function psetex($key, $milliseconds, $value) {
        return $this->plodis_string->psetex($key, $milliseconds, $value);
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
     * @return null no documentation available
     */
    public function psubscribe($patterns) {
        if(!is_array($patterns)) $patterns = array_slice(func_get_args(), 0);
        return $this->plodis_pubsub->psubscribe($patterns);
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
     * @return null no documentation available
     */
    public function pttl($key) {
        return $this->plodis_generic->pttl($key);
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
     * @return null no documentation available
     */
    public function publish($channel, $message) {
        return $this->plodis_pubsub->publish($channel, $message);
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
     * @return null no documentation available
     */
    public function punsubscribe($pattern=null) {
        if(!is_array($pattern)) $pattern = array_slice(func_get_args(), 0);
        return $this->plodis_pubsub->punsubscribe($pattern);
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
        return $this->plodis_generic->randomkey();
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
     * @return null no documentation available
     */
    public function rename($key, $newkey) {
        return $this->plodis_generic->rename($key, $newkey);
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
     * @return null no documentation available
     */
    public function renamenx($key, $newkey) {
        return $this->plodis_generic->renamenx($key, $newkey);
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
     * @return null no documentation available
     */
    public function restore($key, $ttl, $serialized_value) {
        return $this->plodis_generic->restore($key, $ttl, $serialized_value);
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
     * @return null no documentation available
     */
    public function rpop($key) {
        return $this->plodis_list->rpop($key);
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
     * @return null no documentation available
     */
    public function rpoplpush($source, $destination) {
        return $this->plodis_list->rpoplpush($source, $destination);
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
     * @return null no documentation available
     */
    public function rpush($key, $value) {
        if(!is_array($value)) $value = array_slice(func_get_args(), 1);
        return $this->plodis_list->rpush($key, $value);
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
     * @return null no documentation available
     */
    public function rpushx($key, $value) {
        return $this->plodis_list->rpushx($key, $value);
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
     * @return null no documentation available
     */
    public function set($key, $value) {
        return $this->plodis_string->set($key, $value);
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
     * @return null no documentation available
     */
    public function setbit($key, $offset, $value) {
        return $this->plodis_string->setbit($key, $offset, $value);
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
     * @return null no documentation available
     */
    public function setex($key, $seconds, $value) {
        return $this->plodis_string->setex($key, $seconds, $value);
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
     * @return null no documentation available
     */
    public function setnx($key, $value) {
        return $this->plodis_string->setnx($key, $value);
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
     * @return null no documentation available
     */
    public function setrange($key, $offset, $value) {
        return $this->plodis_string->setrange($key, $offset, $value);
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
     * @return null no documentation available
     */
    public function sort($key, $by=null, $limit=null, $get=null, $order=null, $sorting=null, $store=null) {
        return $this->plodis_generic->sort($key, $by, $limit, $get, $order, $sorting, $store);
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
     * @return null no documentation available
     */
    public function strlen($key) {
        return $this->plodis_string->strlen($key);
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
     * @return null no documentation available
     */
    public function subscribe($channels) {
        if(!is_array($channels)) $channels = array_slice(func_get_args(), 0);
        return $this->plodis_pubsub->subscribe($channels);
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
     * @return null no documentation available
     */
    public function ttl($key) {
        return $this->plodis_generic->ttl($key);
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
     * @return null no documentation available
     */
    public function type($key) {
        return $this->plodis_generic->type($key);
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
     * @return null no documentation available
     */
    public function unsubscribe($channel=null) {
        if(!is_array($channel)) $channel = array_slice(func_get_args(), 0);
        return $this->plodis_pubsub->unsubscribe($channel);
    }

}

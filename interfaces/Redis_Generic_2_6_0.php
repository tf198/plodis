<?php
/**
 * Redis generic methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface Redis_Generic_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "generic";

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
    public function del($key);

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
    public function dump($key);

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
    public function exists($key);

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
    public function expire($key, $seconds);

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
    public function expireat($key, $timestamp);

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
    public function keys($pattern=null);

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
    public function migrate($host, $port, $key, $destination_db, $timeout);

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
    public function move($key, $db);

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
    public function object($subcommand, $arguments=null);

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
    public function persist($key);

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
    public function pexpire($key, $milliseconds);

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
    public function pexpireat($key, $milliseconds_timestamp);

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
    public function pttl($key);

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
    public function randomkey();

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
    public function rename($key, $newkey);

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
    public function renamenx($key, $newkey);

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
    public function restore($key, $ttl, $serialized_value);

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
    public function sort($key, $by=null, $limit=null, $get=null, $order=null, $sorting=null, $store=null);

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
    public function ttl($key);

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
    public function type($key);

}

<?php
/**
 * Redis server methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface IRedis_Server_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "server";

    /**
     * Asynchronously rewrite the append-only file
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/bgrewriteaof BGREWRITEAOF
     *
     * @return null always `true`.
     */
    public function bgrewriteaof();

    /**
     * Asynchronously save the dataset to disk
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/bgsave BGSAVE
     *
     * @return null
     */
    public function bgsave();

    /**
     * Get the value of a configuration parameter
     *
     * @since 2.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/config get CONFIG GET
     *
     * @param string $parameter
     * @return null
     */
    public function config_get($parameter);

    /**
     * Set a configuration parameter to the given value
     *
     * @since 2.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/config set CONFIG SET
     *
     * @param string $parameter
     * @param string $value
     * @return null `true` when the configuration was set properly.
     *   Otherwise an error is returned.
     */
    public function config_set($parameter, $value);

    /**
     * Reset the stats returned by INFO
     *
     * @since 2.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/config resetstat CONFIG RESETSTAT
     *
     * @return null always `true`.
     */
    public function config_resetstat();

    /**
     * Return the number of keys in the selected database
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/dbsize DBSIZE
     *
     * @return integer
     */
    public function dbsize();

    /**
     * Get debugging information about a key
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/debug object DEBUG OBJECT
     *
     * @param string $key
     * @return null
     */
    public function debug_object($key);

    /**
     * Make the server crash
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/debug segfault DEBUG SEGFAULT
     *
     * @return null
     */
    public function debug_segfault();

    /**
     * Remove all keys from all databases
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/flushall FLUSHALL
     *
     * @return null
     */
    public function flushall();

    /**
     * Remove all keys from the current database
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/flushdb FLUSHDB
     *
     * @return null
     */
    public function flushdb();

    /**
     * Get information and statistics about the server
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/info INFO
     *
     * @return multitype:string key/value pairs
     */
    public function info();

    /**
     * Get the UNIX time stamp of the last successful save to disk
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/lastsave LASTSAVE
     *
     * @return integer an UNIX time stamp.
     */
    public function lastsave();

    /**
     * Listen for all requests received by the server in real time
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/monitor MONITOR
     *
     * @return null
     */
    public function monitor();

    /**
     * Synchronously save the dataset to disk
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/save SAVE
     *
     * @return null The commands returns OK on success.
     */
    public function save();

    /**
     * Synchronously save the dataset to disk and then shut down the server
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/shutdown SHUTDOWN
     *
     * @param string $NOSAVE [ NOSAVE ]
     * @param string $SAVE [ SAVE ]
     * @return null on error.
     *   On success nothing is returned since the server quits and the connection is
     *   closed.
     */
    public function shutdown($NOSAVE=null, $SAVE=null);

    /**
     * Make the server a slave of another instance, or promote it as master
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/slaveof SLAVEOF
     *
     * @param string $host
     * @param string $port
     * @return null
     */
    public function slaveof($host, $port);

    /**
     * Manages the Redis slow queries log
     *
     * @since 2.2.12
     * @api
     * @group server
     * @link http://redis.io/commands/slowlog SLOWLOG
     *
     * @param string $subcommand
     * @param string $argument
     * @return null
     */
    public function slowlog($subcommand, $argument=null);

    /**
     * Internal command used for replication
     *
     * @since 1.0.0
     * @api
     * @group server
     * @link http://redis.io/commands/sync SYNC
     *
     * @return null
     */
    public function sync();

    /**
     * Return the current server time
     *
     * @since 2.6.0
     * @api
     * @group server
     * @link http://redis.io/commands/time TIME
     *
     * @return multitype:string, specifically
     *   
     *   A multi bulk reply containing two elements
     *   
     *   * unix time in seconds.
     *   * microseconds.
     *
     */
    public function time();

}

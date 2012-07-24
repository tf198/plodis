<?php
/**
 * Redis connection methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
interface IRedis_Connection_2_6_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.6.0";
	
    const REDIS_GROUP = "connection";

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
    public function auth($password);

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
    public function _echo($message);

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
    public function ping();

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
    public function quit();

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
    public function select($index);

}

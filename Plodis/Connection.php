<?php

require_once PLODIS_BASE . '/interfaces/Redis_Connection_2_6_0.php';

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
class Plodis_Connection extends Plodis_Group implements Redis_Connection_2_6_0 {
	
    /**
     * Authenticate to the server
     *
     * @since 1.0.0
     * @api
     * @group connection
     * @link http://redis.io/commands/auth AUTH
     *
     * @param string $password
     * @return null no documentation available
     */
    public function auth($password) {
    	$this->proxy->log('Dummy call to AUTH', LOG_INFO);
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
     * @return null no documentation available
     */
    public function _echo($message) {
    	return (string) $message;
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
    	return "PONG";
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
    	$this->proxy->db->close();
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
     * @return null no documentation available
     */
    public function select($index) {
    	$this->proxy->db->selectDatabase($index);
    }

}

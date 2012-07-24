<?php
require_once "IRedis_Server_2_6_0.php";

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
class Plodis_Server extends Plodis_Group implements IRedis_Server_2_6_0 {

	protected $sql = array(
		'dbsize'		=> 'SELECT COUNT(DISTINCT pkey) FROM <DB>',
		'dbs'			=> "SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'plodis_%'",
		'dbs_MYSQL'		=> "SHOW TABLES LIKE 'plodis_%'",
		'flushdb'		=> "DELETE FROM <DB>",
		'expires'		=> "SELECT COUNT(DISTINCT pkey) FROM <DB> WHERE expiry IS NOT NULL",
	);
	
	private function executeStmtDB($which, $db, $params=array(), $column=null) {
		$stmt = $this->proxy->db->cachedStmt(str_replace('<DB>', $db, $this->sql[$which]));
    	$stmt->execute($params);
    	if($column === null) {
    		return $stmt->rowCount();
    	} else {
    		return $stmt->fetch(PDO::FETCH_COLUMN, $column);
    	}
	}
	
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
    public function bgrewriteaof() {
    	$this->proxy->log('Dummy call to BGREWRITEAOF', LOG_INFO);
    }

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
    public function bgsave() {
    	$this->proxy->log('Dummy call to BGSAVE', LOG_INFO);
    }

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
    public function config_get($parameter) {
    	if ($parameter == '*') {
    		return array_keys($this->proxy->options);
    	} else {
    		$value = $this->proxy->getOption($parameter);
    		return ($value === null) ? null : (string) $value;
    	}
    }

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
    public function config_set($parameter, $value) {
    	$this->proxy->setOption($parameter, $value);
    }

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
    public function config_resetstat() {
    	$this->proxy->log('Dummy call to RESETSTAT', LOG_INFO);
    }

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
    public function dbsize() {
    	return (int) $this->fetchOne('dbsize', array(), 0);
    }

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
    public function debug_object($key) {
    	throw new PlodisNotImplementedError;
    }

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
    public function debug_segfault() {
    	throw new RuntimeException("Dummy SegFault"); // :-)
    }

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
    public function flushall() {
    	$dbs = $this->fetchAll('dbs', array(), 0);
    	foreach($dbs as $db) $this->executeStmtDB('flushdb', $db);
    }

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
    public function flushdb() {
    	$this->executeStmt('flushdb');
    }

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
    public function info() {
    	// general info
    	$result = array(
    		'redis_version' => self::REDIS_VERSION,
    	);
    	
    	// db info
    	$dbs = $this->fetchAll('dbs', array(), 0);
    	foreach($dbs as $db) {
    		$id = substr($db, 7);
    		$keys = $this->executeStmtDB('dbsize', $db, array(), 0);
    		$expires = $this->executeStmtDB('expires', $db, array(), 0);
    		$result["db{$id}"] = "keys={$keys},expires={$expires}";
    	} 
    	
    	return $result;
    }

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
    public function lastsave() {
    	return time();
    }

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
    public function monitor() {
    	throw new PlodisNotImplementedError;
    }

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
    public function save() {
    	$this->proxy->log('Dummy call to SAVE', LOG_INFO);
    }

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
    public function shutdown($NOSAVE=null, $SAVE=null) {
    	$this->proxy->db->close();
    }

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
    public function slaveof($host, $port) {
    	$this->proxy->log('Dummy call to SLAVEOF', LOG_INFO);
    }

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
    public function slowlog($subcommand, $argument=null) {
    	throw new PlodisNotImplementedError;
    }

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
    public function sync() {
    	$this->proxy->log('Dummy call to SYNC', LOG_INFO);
    }

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
    public function time() {
    	$now = microtime(true);
    	$sec = floor($now);
    	return array($sec, (int)(($now-$sec) * 1000000));
    }

}

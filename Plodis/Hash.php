<?php

require_once PLODIS_BASE . '/interfaces/Redis_Hash_2_6_0.php';

/**
 * Redis hash methods for version 2.6.0
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
class Plodis_Hash extends Plodis_Group implements Redis_Hash_2_6_0 {

	protected $sql = array(
		'h_select'		=> 'SELECT field, item FROM <DB> WHERE key=? ORDER BY id',
		'h_insert' 		=> 'INSERT INTO <DB> (key, field, item) VALUES (?, ?, ?)',
		'h_update' 		=> 'UPDATE <DB> SET item=? WHERE key=? AND field=?',
		'h_delete'		=> 'DELETE FROM <DB> WHERE key=? AND field=?',
		'hlen'			=> 'SELECT COUNT(id) FROM <DB> WHERE key=?',
		'hget'			=> 'SELECT id, item FROM <DB> WHERE key=? AND field=?',
		'hincrby'		=> 'UPDATE <DB> SET item=item+? WHERE key=? AND field=?',
	);
	
    /**
     * Delete one or more hash fields
     *
     * @since 2.0.0
     * @api
     * @group hash
     * @link http://redis.io/commands/hdel HDEL
     *
     * @param string $key
     * @param string $fields (multiple)
     * @return null no documentation available
     */
    public function hdel($key, $fields) {
    	$c = 0;
    	foreach($fields as $field) {
    		$c += $this->executeStmt('h_delete', array($key, $field));
    	}
    	return $c;
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
     * @return null no documentation available
     */
    public function hexists($key, $field) {
    	if($this->hget($key, $field) === null) {
    		return 0;
    	} else {
    		return 1;
    	}
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
     * @return null no documentation available
     */
    public function hget($key, $field) {
    	$item = $this->fetchOne('hget', array($key, $field));
    	
    	return ($item) ? $item[1] : null;
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
     * @return null no documentation available
     */
    public function hgetall($key) {
    	$all = $this->fetchAll('h_select', array($key));
    	$result = array();
    	foreach($all as $row) $result[$row[0]] = $row[1];
    	return $result;
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
     * @return null no documentation available
     */
    public function hincrby($key, $field, $increment) {
    	return (int) $this->hincrbyfloat($key, $field, (int)$increment);
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
     * @return null no documentation available
     */
    public function hincrbyfloat($key, $field, $increment) {
    	$c = $this->executeStmt('hincrby', array($increment, $key, $field));
    	if($c==0) {
    		$this->executeStmt('h_insert', array($key, $field, $increment));
    		return (float) $increment;
    	}
    	return (float) $this->hget($key, $field);
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
     * @return null no documentation available
     */
    public function hkeys($key) {
    	return $this->fetchAll('h_select', array($key), 0);
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
     * @return null no documentation available
     */
    public function hlen($key) {
    	$data = $this->fetchOne('hlen', array($key));
    	return (int) $data[0];
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
     * @return null no documentation available
     */
    public function hmget($key, $fields) {
    	$result = array();
    	foreach($fields as $field) $result[] = $this->hget($key, $field);
    	return $result;
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
     * @return null no documentation available
     */
    public function hmset($key, $fields) {
    	foreach($fields as $field=>$value) {
    		$this->hset($key, $field, $value);
    	}
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
     * @return null no documentation available
     */
    public function hset($key, $field, $value) {
    	$count = $this->executeStmt('h_update', array($value, $key, $field));
    	if($count == 1) return 0;
    	
    	$this->executeStmt('h_insert', array($key, $field, $value));
    	return 1;
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
     * @return null no documentation available
     */
    public function hsetnx($key, $field, $value) {
    	if($this->hget($key, $field) !== null) return 0;
    	
    	$this->executeStmt('h_insert', array($key, $field, $value));
    	return 1;
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
     * @return null no documentation available
     */
    public function hvals($key) {
    	return $this->fetchAll('h_select', array($key), 1);
    }

}

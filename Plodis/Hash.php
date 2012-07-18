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
		'h_select'		=> 'SELECT field, item, type FROM <DB> WHERE key=? ORDER BY id',
		'hset' 			=> 'INSERT OR REPLACE INTO <DB> (key, type, field, item) VALUES (?, ?, ?, ?)',
		'h_update' 		=> 'UPDATE <DB> SET item=? WHERE key=? AND field=?',
		'h_delete'		=> 'DELETE FROM <DB> WHERE key=? AND field=?',
		'hlen'			=> 'SELECT COUNT(id) FROM <DB> WHERE key=?',
		'hget'			=> 'SELECT id, item, type FROM <DB> WHERE key=? AND field=?',
		'hincrby'		=> 'UPDATE <DB> SET item=item+? WHERE key=? AND field=?',
		'hsetnx'		=> 'INSERT OR IGNORE INTO <DB> (key, type, field, item) VALUES (?, ?, ?, ?)',
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
    	$this->proxy->generic->gc();
    	$this->proxy->generic->verify($key, 'hash');
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
    	$this->proxy->generic->gc();
    	$row = $this->fetchOne('hget', array($key, $field));
    	if($row) {
    		if($row[2] != Plodis::TYPE_HASH) throw new PlodisIncorrectKeyType;
    		return 1;
    	} else {
    		$this->proxy->generic->verify($key, 'hash');
    		return 0;
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
    	$this->proxy->generic->gc();
    	$item = $this->fetchOne('hget', array($key, $field));
    	
    	if($item) {
    		if($item[2] != Plodis::TYPE_HASH) throw new PlodisIncorrectKeyType;
    		return $item[1];
    	} else {
    		$this->proxy->generic->verify($key, 'hash');
    		return null;
    	}
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
    	$this->proxy->generic->gc();
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
    	$result = $this->hincrbyfloat($key, $field, (int)$increment);
    	if($result !== null) $result = (int) $result;
    	return $result;
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
    	$this->proxy->generic->gc();
    	$this->proxy->db->lock();
    	$c = $this->executeStmt('hincrby', array($increment, $key, $field));
    	if($c==0) {
    		$this->executeStmt('hset', array($key, Plodis::TYPE_HASH, $field, $increment));
    		$result = (float) $increment;
    	} else {
    		$result = ($this->proxy->options['return_incr_values']) ? (float) $this->hget($key, $field) : null;
    	}
    	$this->proxy->db->unlock();
    	return $result;
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
    	$this->proxy->generic->gc();
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
    	$this->proxy->generic->gc();
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
    	$this->proxy->db->lock();
    	$result = array();
    	foreach($fields as $field) $result[] = $this->hget($key, $field);
    	$this->proxy->db->unlock();
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
    	$this->proxy->db->lock();
    	foreach($fields as $field=>$value) {
    		$this->hset($key, $field, $value);
    	}
    	$this->proxy->db->unlock();
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
    	$this->proxy->db->lock();
    	/*
    	$count = $this->executeStmt('h_update', array($value, $key, $field));
    	if($count == 1) return 0;
    	
    	if($count>1) {
    		$this->proxy->db->unlock(true);
    		throw new PlodisIncorrectKeyType;
    	}
    	*/
    	$type = $this->proxy->generic->type($key);
    	if($type !== null && $type != 'hash') throw new PlodisIncorrectKeyType;
    	
    	$c = $this->executeStmt('hset', array($key, Plodis::TYPE_HASH, $field, $value));
    	$this->proxy->db->unlock();
    	return ($type === null) ? 1 : 0;
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
    	$this->proxy->generic->gc();
    	$c = $this->executeStmt('hsetnx', array($key, Plodis::TYPE_HASH, $field, $value));
    	return $c;
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
    	$this->proxy->generic->gc();
    	return $this->fetchAll('h_select', array($key), 1);
    }

}

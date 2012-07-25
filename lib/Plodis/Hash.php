<?php

require_once 'IRedis_Hash_2_4_0.php';

/**
 * Redis hash methods for version 2.4.0
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.4.0
 */
class Plodis_Hash extends Plodis_Group implements IRedis_Hash_2_4_0 {

	protected $sql = array(
		'h_select'		=> 'SELECT field, item, type FROM <DB> WHERE pkey=? ORDER BY id',
		'hset' 			=> 'REPLACE INTO <DB> (pkey, type, field, item) VALUES (?, ?, ?, ?)',
		'h_update' 		=> 'UPDATE <DB> SET item=? WHERE pkey=? AND field=?',
		'h_delete'		=> 'DELETE FROM <DB> WHERE pkey=? AND field=?',
		'hlen'			=> 'SELECT COUNT(*) FROM <DB> WHERE pkey=? AND type=?',
		'hget'			=> 'SELECT id, item, type FROM <DB> WHERE pkey=? AND field=?',
		'hincrby'		=> 'UPDATE <DB> SET item=item+? WHERE pkey=? AND field=?',
		'hsetnx'		=> 'INSERT OR IGNORE INTO <DB> (pkey, type, field, item) VALUES (?, ?, ?, ?)',
		'hsetnx_MYSQL'	=> 'INSERT IGNORE INTO <DB> (pkey, type, field, item) VALUES (?, ?, ?, ?)',
	);
	
	protected $type = 'hash';
	
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
    	$this->verify($key);
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
    	return ($this->fetchOneGCVerify($key, 'hget', array($key, $field), 2)) ? 1 : 0;
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
    	return $this->fetchOneGCVerify($key, 'hget', array($key, $field), 2, 1, null);
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
    	$data = $this->fetchAllGCVerify($key, 'h_select', array($key), 2);
    	$result = array();
    	foreach($data as $row) $result[$row[0]] = $row[1];
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
    	if((int)$increment != $increment) throw new PlodisError("ERR value is not an integer or out of range");
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
    		$result = (string) $increment;
    	} else {
    		$result = ($this->proxy->options['return_incr_values']) ? $this->hget($key, $field) : null;
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
    	return $this->fetchAllGCVerify($key, 'h_select', array($key), 2, 0);
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
    	return $this->countItems($key, 'hlen', array($key, Plodis::TYPE_HASH));
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
    	$type = $this->proxy->generic->type($key);
    	if($type !== null && $type != 'hash') {
    		$this->db->unlock();
    		throw new PlodisIncorrectKeyType;
    	}
    	
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
    	return $this->fetchAllGCVerify($key, 'h_select', array($key), 2, 1);
    }

}

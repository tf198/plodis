<?php
require_once "IRedis_Sorted_Set_2_4_0.php";
/**
 * Redis sorted_set methods for version 2.4.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.4.0
 */
class Plodis_Sorted_Set extends Plodis_Group implements IRedis_Sorted_set_2_4_0 {

	protected $sql = array(
		'zadd'		=> 'INSERT INTO <DB> (pkey, type, weight, field) VALUES (?, ?, ?, ?)',
		'zcard'		=> 'SELECT COUNT(*) FROM <DB> WHERE pkey=? AND type=?',
		'zcount'	=> 'SELECT COUNT(*) FROM <DB> WHERE pkey=? AND type=? AND weight >=? AND weight<=?',
		'zincrby'	=> 'UPDATE <DB> SET weight=weight+? WHERE pkey=? AND field=? AND type=?',
	);
	
    /**
     * Add one or more members to a sorted set, or update its score if it already exists
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zadd ZADD
     *
     * @param string $key
     * @param double $score
     * @param string $member
     * @param double $score
     * @param string $member
     * @return integer specifically
     *   
     *   * The number of elements added to the sorted sets, not including elements
     *     already existing for which the score was updated.
     *
     */
    public function zadd($key, $score, $member=null) {
    	if(is_array($score)) {
    		$pairs = $score;
    	} else {
    		$pairs = array();
    		$args = func_get_args();
    		for($i=2; $i<count($args); $i++) {
    			if($args[$i]) $pairs[$args[$i]] = $args[$i-1];
    		}
    	}
    	$this->proxy->db->lock();
    	$this->proxy->generic->verify($key, 'zset', 1);
    	$c = 0;
    	foreach($pairs as $member=>$score) {
    		try {
    			$c += $this->executeStmt('zadd', array($key, Plodis::TYPE_ZSET, $score, $member));
    		} catch(PDOException $e) {} // pass
    	}
    	$this->proxy->db->unlock();
    	return $c;
    }

    /**
     * Get the number of members in a sorted set
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zcard ZCARD
     *
     * @param string $key
     * @return integer the cardinality (number of elements) of the sorted set, or `0`
     *   if `key` does not exist.
     *
     */
    public function zcard($key) {
    	return $this->countItems('zcard', array($key, Plodis::TYPE_ZSET), $key);
    }

    /**
     * Count the members in a sorted set with scores within the given values
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zcount ZCOUNT
     *
     * @param string $key
     * @param double $min
     * @param double $max
     * @return integer the number of elements in the specified score range.
     *
     */
    public function zcount($key, $min, $max) {
    	
    	if($min == '-inf') $min = Plodis::NEG_INF;
    	if($max == 'inf') $max = Plodis::POS_INF;
    	
    	return $this->countItems('zcount', array($key, Plodis::TYPE_ZSET, $min, $max), $key);
    }

    /**
     * Increment the score of a member in a sorted set
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zincrby ZINCRBY
     *
     * @param string $key
     * @param integer $increment
     * @param string $member
     * @return string the new score of `member` (a double precision floating point
     *   number), represented as string.
     *
     */
    public function zincrby($key, $increment, $member) {
    	$this->proxy->db->lock();
    	$c = $this->executeStmt('zincrby', array($increment, $key, $member, Plodis::TYPE_ZSET));
    	if($c == 0) {
    		$this->proxy->generic->verify($key, 'zset', 1);
    		$this->executeStmt('zadd', array($key, Plodis::TYPE_ZSET, $increment, $member));
    		$result = $increment;
    	} else {
    		$result = ($this->proxy->options['return_incr_values']) ? $this->zscore($key, $member) : null;
    	}
    	$this->proxy->db->unlock();
    	return $result;
    }

    /**
     * Intersect multiple sorted sets and store the resulting sorted set in a new key
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zinterstore ZINTERSTORE
     *
     * @param string $destination
     * @param integer $numkeys
     * @param string $key (multiple)
     * @param integer $weights
     * @param string $aggregate [ SUM, MIN, MAX ]
     * @return integer the number of elements in the resulting sorted set at
     *   `destination`.
     *
     */
    public function zinterstore($destination, $numkeys, $key, $weights=null, $aggregate=null) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Return a range of members in a sorted set, by index
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrange ZRANGE
     *
     * @param string $key
     * @param integer $start
     * @param integer $stop
     * @param string $withscores [ WITHSCORES ]
     * @return multitype:string list of elements in the specified range (optionally with
     *   their scores).
     *
     */
    public function zrange($key, $start, $stop, $withscores=null) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Return a range of members in a sorted set, by score
     *
     * @since 1.0.5
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrangebyscore ZRANGEBYSCORE
     *
     * @param string $key
     * @param double $min
     * @param double $max
     * @param string $withscores [ WITHSCORES ]
     * @param multitype:integer $limit
     * @return multitype:string list of elements in the specified score range (optionally
     *   with their scores).
     *
     */
    public function zrangebyscore($key, $min, $max, $withscores=null, $limit=null) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Determine the index of a member in a sorted set
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrank ZRANK
     *
     * @param string $key
     * @param string $member
     * @return @examples
     *   
     *   ```cli
     *   ZADD myzset 1 "one"
     *   ZADD myzset 2 "two"
     *   ZADD myzset 3 "three"
     *   ZRANK myzset "three"
     *   ZRANK myzset "four"
     *   ```
     */
    public function zrank($key, $member) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Remove one or more members from a sorted set
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrem ZREM
     *
     * @param string $key
     * @param string $member (multiple)
     * @return integer specifically
     *   
     *   * The number of members removed from the sorted set, not including non existing
     *     members.
     *
     */
    public function zrem($key, $member) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Remove all members in a sorted set within the given indexes
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zremrangebyrank ZREMRANGEBYRANK
     *
     * @param string $key
     * @param integer $start
     * @param integer $stop
     * @return integer the number of elements removed.
     *
     */
    public function zremrangebyrank($key, $start, $stop) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Remove all members in a sorted set within the given scores
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zremrangebyscore ZREMRANGEBYSCORE
     *
     * @param string $key
     * @param double $min
     * @param double $max
     * @return integer the number of elements removed.
     *
     */
    public function zremrangebyscore($key, $min, $max) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Return a range of members in a sorted set, by index, with scores ordered from high to low
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrevrange ZREVRANGE
     *
     * @param string $key
     * @param integer $start
     * @param integer $stop
     * @param string $withscores [ WITHSCORES ]
     * @return multitype:string list of elements in the specified range (optionally with
     *   their scores).
     *
     */
    public function zrevrange($key, $start, $stop, $withscores=null) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Return a range of members in a sorted set, by score, with scores ordered from high to low
     *
     * @since 2.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrevrangebyscore ZREVRANGEBYSCORE
     *
     * @param string $key
     * @param double $max
     * @param double $min
     * @param string $withscores [ WITHSCORES ]
     * @param multitype:integer $limit
     * @return multitype:string list of elements in the specified score range (optionally
     *   with their scores).
     *
     */
    public function zrevrangebyscore($key, $max, $min, $withscores=null, $limit=null) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Determine the index of a member in a sorted set, with scores ordered from high to low
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zrevrank ZREVRANK
     *
     * @param string $key
     * @param string $member
     * @return @examples
     *   
     *   ```cli
     *   ZADD myzset 1 "one"
     *   ZADD myzset 2 "two"
     *   ZADD myzset 3 "three"
     *   ZREVRANK myzset "one"
     *   ZREVRANK myzset "four"
     *   ```
     */
    public function zrevrank($key, $member) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Get the score associated with the given member in a sorted set
     *
     * @since 1.2.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zscore ZSCORE
     *
     * @param string $key
     * @param string $member
     * @return string the score of `member` (a double precision floating point number),
     *   represented as string.
     *
     */
    public function zscore($key, $member) {
    	throw new PlodisNotImplemented;
    }

    /**
     * Add multiple sorted sets and store the resulting sorted set in a new key
     *
     * @since 2.0.0
     * @api
     * @group sorted_set
     * @link http://redis.io/commands/zunionstore ZUNIONSTORE
     *
     * @param string $destination
     * @param integer $numkeys
     * @param string $key (multiple)
     * @param integer $weights
     * @param string $aggregate [ SUM, MIN, MAX ]
     * @return integer the number of elements in the resulting sorted set at
     *   `destination`.
     *
     */
    public function zunionstore($destination, $numkeys, $key, $weights=null, $aggregate=null) {
    	throw new PlodisNotImplemented;
    }

}

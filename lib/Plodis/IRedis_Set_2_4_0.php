<?php
/**
 * Redis set methods for version 2.4.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.4.0
 */
interface IRedis_Set_2_4_0 {

    /**
     * Redis server version
     * @var string
     */
    const REDIS_VERSION = "2.4.0";
	
    const REDIS_GROUP = "set";

    /**
     * Add one or more members to a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sadd SADD
     *
     * @param string $key
     * @param string $member (multiple)
     * @return integer the number of elements that were added to the set, not including
     *   all the elements already present into the set.
     *
     */
    public function sadd($key, $member);

    /**
     * Get the number of members in a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/scard SCARD
     *
     * @param string $key
     * @return integer the cardinality (number of elements) of the set, or `0` if `key`
     *   does not exist.
     *
     */
    public function scard($key);

    /**
     * Subtract multiple sets
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sdiff SDIFF
     *
     * @param string $key (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sdiff($key);

    /**
     * Subtract multiple sets and store the resulting set in a key
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sdiffstore SDIFFSTORE
     *
     * @param string $destination
     * @param string $key (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sdiffstore($destination, $key);

    /**
     * Intersect multiple sets
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sinter SINTER
     *
     * @param string $key (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sinter($key);

    /**
     * Intersect multiple sets and store the resulting set in a key
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sinterstore SINTERSTORE
     *
     * @param string $destination
     * @param string $key (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sinterstore($destination, $key);

    /**
     * Determine if a given value is a member of a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sismember SISMEMBER
     *
     * @param string $key
     * @param string $member
     * @return integer specifically
     *   
     *   * `1` if the element is a member of the set.
     *   * `0` if the element is not a member of the set, or if `key` does not exist.
     *
     */
    public function sismember($key, $member);

    /**
     * Get all the members in a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/smembers SMEMBERS
     *
     * @param string $key
     * @return multitype:string all elements of the set.
     *
     */
    public function smembers($key);

    /**
     * Move a member from one set to another
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/smove SMOVE
     *
     * @param string $source
     * @param string $destination
     * @param string $member
     * @return integer specifically
     *   
     *   * `1` if the element is moved.
     *   * `0` if the element is not a member of `source` and no operation was performed.
     *
     */
    public function smove($source, $destination, $member);

    /**
     * Remove and return a random member from a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/spop SPOP
     *
     * @param string $key
     * @return string the removed element, or `null` when `key` does not exist.
     *
     */
    public function spop($key);

    /**
     * Get a random member from a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/srandmember SRANDMEMBER
     *
     * @param string $key
     * @return string the randomly selected element, or `null` when `key` does not exist.
     *
     */
    public function srandmember($key);

    /**
     * Remove one or more members from a set
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/srem SREM
     *
     * @param string $key
     * @param string $member (multiple)
     * @return integer the number of members that were removed from the set, not
     *   including non existing members.
     *
     */
    public function srem($key, $member);

    /**
     * Add multiple sets
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sunion SUNION
     *
     * @param string $key (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sunion($key);

    /**
     * Add multiple sets and store the resulting set in a key
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sunionstore SUNIONSTORE
     *
     * @param string $destination
     * @param string $key (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sunionstore($destination, $key);

}

<?php
require_once PLODIS_BASE . "/interfaces/Redis_Set_2_6_0.php";

/**
 * Redis set methods for version 2.6.0
 * This interface is automatically generated from the Redis docs on github.
 *
 *
 * @link https://github.com/antirez/redis-doc
 * @package redis
 * @author Tris Forster
 * @version 2.6.0
 */
class Plodis_Set extends Plodis_Group implements Redis_Set_2_6_0 {

	protected $sql = array(
		'sadd'		=> 'INSERT OR ABORT INTO <DB> (key, type, field) VALUES (?, ?, ?)',
		'scard' 	=> 'SELECT COUNT(*) FROM <DB> WHERE key=?',
		'smembers' 	=> 'SELECT field, type FROM <DB> WHERE key=? ORDER BY id',
		'srand'		=> 'SELECT id, field, type FROM <DB> WHERE key=? ORDER BY RANDOM() LIMIT 1',
		'srem'		=> 'DELETE FROM <DB> WHERE key=? AND field=?',
		'sismember'	=> 'SELECT 1 FROM <DB> WHERE key=? AND field=?',
	);
	
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
    public function sadd($key, $members) {
    	$this->proxy->db->lock();
    	$this->proxy->generic->verify($key, 'set');
    	
    	$stmt = $this->getStmt('sadd');
    	$c = 0;
    	foreach($members as $member) {
    		try {
    			$c += $stmt->execute(array($key, Plodis::TYPE_SET, $member));
    		} catch(PDOException $e) {} // pass
    	}
    	$this->proxy->db->unlock();
    	return $c;
    }

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
    public function scard($key) {
    	$this->proxy->generic->gc();
    	$this->proxy->db->lock();
    	$this->proxy->generic->verify($key, 'set');
    	$data = $this->fetchOne('scard', array($key));
    	$this->proxy->db->unlock();
    	return (int) $data[0];
    }

    /**
     * Subtract multiple sets
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sdiff SDIFF
     *
     * @param string $keys (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sdiff($keys) {
    	$this->proxy->generic->gc();
    	$sql = "SELECT field, type " . $this->_sdiff($keys);
    	
    	$stmt = $this->proxy->db->cachedStmt($sql);
    	$stmt->execute($keys);
    	$data = $stmt->fetchAll(PDO::FETCH_NUM);
    	if($data && $data[0][1] != Plodis::TYPE_SET) throw new PlodisIncorrectKeyType;
    	foreach($data as &$row) $row = $row[0];
    	return $data;
    }

    private function _sdiff($keys) {
    	// have to construct this depending on number of args
    	$c = count($keys);
    	$sql = "FROM <DB> WHERE key=?";
    	for($i=1; $i<$c; $i++) {
    		$sql .= " AND field NOT IN (SELECT field FROM <DB> WHERE key=?)";
    	}
    	$sql .= " ORDER BY id";
    	return $sql;
    }
    
    /**
     * Subtract multiple sets and store the resulting set in a key
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sdiffstore SDIFFSTORE
     *
     * @param string $destination
     * @param string $keys (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sdiffstore($destination, $keys) {
    	$this->proxy->generic->gc();
    	$sql = "INSERT INTO <DB> (key, field, type) SELECT ?, field, type " . $this->_sdiff($keys);
    	
    	$stmt = $this->proxy->db->cachedStmt($sql);
    	array_unshift($keys, $destination);
    	$stmt->execute($keys);
    	return $stmt->rowCount();
    }

    /**
     * Intersect multiple sets
     *
     * @since 1.0.0
     * @api
     * @group set
     * @link http://redis.io/commands/sinter SINTER
     *
     * @param string $keys (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sinter($keys) {
    	throw new PlodisNotImplementedError;
    }

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
    public function sinterstore($destination, $key) {
    	throw new PlodisNotImplementedError;
    }

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
    public function sismember($key, $member) {
    	$this->proxy->generic->gc();
    	$row = $this->fetchOne('sismember', array($key, $member));
    	if($row) return 1;
    	$this->proxy->generic->verify($key, 'set');
    	return 0;
    }

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
    public function smembers($key) {
    	$this->proxy->generic->gc();
    	$all = $this->fetchAll('smembers', array($key));
    	if($all && $all[0][1] != Plodis::TYPE_SET) throw new PlodisIncorrectKeyType;
    	foreach($all as &$row) $row = $row[0];
    	return $all;
    }

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
    public function smove($source, $destination, $member) {
    	throw new PlodisNotImplementedError;
    }

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
    public function spop($key) {
    	$this->proxy->generic->gc();
    	$this->proxy->db->lock();
    	$data = $this->fetchOne('srand', array($key));
    	if($data) {
    		if($data[2] != Plodis::TYPE_SET) throw new PlodisIncorrectKeyType;
    		$this->executeStmt('srem', array($data[1]));
    		$result = $data[1];
    	} else {
    		$result = null;
    	}
    	$this->proxy->db->unlock();
    	return $result;
    }

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
    public function srandmember($key) {
    	$this->proxy->generic->gc();
    	$data = $this->fetchOne('srand', array($key));
    	if(!$data) return null;
    	
    	if($data[2] != Plodis::TYPE_SET) throw new PlodisIncorrectKeyType;
    	return $data[1];
    }

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
    public function srem($key, $members) {
    	$this->proxy->generic->gc();
    	$this->proxy->db->lock();
    	$c = 0;
    	foreach($members as $member) {
    		$c += $this->executeStmt('srem', array($key, $member));
    	}
    	$this->proxy->db->unlock();
    	return $c;
    }

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
    public function sunion($key) {
    	throw new PlodisNotImplementedError;
    }

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
    public function sunionstore($destination, $key) {
    	throw new PlodisNotImplementedError;
    }

}

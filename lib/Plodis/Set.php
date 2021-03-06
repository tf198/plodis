<?php
require_once "IRedis_Set_2_4_0.php";

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
class Plodis_Set extends Plodis_Group implements IRedis_Set_2_4_0 {

	protected $sql = array(
		'sadd'		=> 'INSERT INTO <DB> (pkey, type, field) VALUES (?, ?, ?)',
		'scard' 	=> 'SELECT COUNT(*) FROM <DB> WHERE pkey=? AND type=?',
		'smembers' 	=> 'SELECT field, type FROM <DB> WHERE pkey=? ORDER BY id',
		'srand'		=> 'SELECT id, field, type FROM <DB> WHERE pkey=? ORDER BY RANDOM() LIMIT 1',
		'srand_MYSQL' => 'SELECT id, field, type FROM <DB> WHERE pkey=? ORDER BY RAND() LIMIT 1',
		'srem'		=> 'DELETE FROM <DB> WHERE pkey=? AND field=?',
		'sismember'	=> 'SELECT type FROM <DB> WHERE pkey=? AND field=?',
	);
	
	protected $type = 'set';
	
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
    	$this->verify($key, 1);
    	
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
    	return $this->countItems($key, 'scard', array($key, Plodis::TYPE_SET));
    }

    private function scustom($sql, $keys) {
    	$this->proxy->generic->gc();
    	$sql = "SELECT DISTINCT field, type " . $sql;
    	
    	$stmt = $this->proxy->db->cachedStmt($sql);
    	$stmt->execute($keys);
    	$data = $stmt->fetchAll(PDO::FETCH_NUM);
    	
    	if($data) {
    	  	if($data[0][1] != Plodis::TYPE_SET) throw new PlodisIncorrectKeyType;
    		foreach($data as &$row) $row = $row[0];
    		return $data;
    	} else {
    		foreach($keys as $key) $this->verify($key);
    		return array();
    	}
    }
    
    private function scustomstore($dest, $sql, $keys) {
    	$this->proxy->generic->gc();
    	$sql = "INSERT INTO <DB> (pkey, field, type) SELECT DISTINCT ?, field, type " . $sql;
    	
    	$stmt = $this->proxy->db->cachedStmt($sql);
    	array_unshift($keys, $dest);
    	$stmt->execute($keys);
    	return $stmt->rowCount();
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
    	return $this->scustom($this->_sdiff_sql($keys), $keys);
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
    	return $this->scustomstore($destination, $this->_sdiff_sql($keys), $keys);
    }
    
    private function _sdiff_sql($keys) {
    	// have to construct this depending on number of args
    	$c = count($keys);
    	$sql = "FROM <DB> WHERE pkey=?";
    	for($i=1; $i<$c; $i++) {
    		$sql .= " AND field NOT IN (SELECT field FROM <DB> WHERE pkey=?)";
    	}
    	$sql .= " ORDER BY id";
    	return $sql;
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
    	return $this->scustom($this->_sinter_sql($keys), $keys);
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
     * @param string $keys (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sinterstore($destination, $keys) {
    	return $this->scustomstore($destination, $this->_sinter_sql($keys), $keys);
    }

    private function _sinter_sql($keys) {
    	$c = count($keys);
    	$sql = "";
    	for($i=1; $i<$c; $i++) {
    		$inner = "SELECT field FROM <DB> WHERE pkey=?";
    		if($sql) $inner .= " AND field IN ({$sql})";
    		$sql = $inner;
    	}
    	return "FROM <DB> WHERE pkey=? AND field IN ({$sql}) ORDER BY id";
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
    	return ($this->fetchOneGCVerify($key, 'sismember', array($key, $member), 0)) ? 1 : 0;
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
    	return $this->fetchAllGCVerify($key, 'smembers', array($key), 1, 0);
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
    	$this->proxy->db->lock();
    	$this->verify($source, 1);
    	$c = $this->executeStmt('srem', array($source, $member));
    	if($c == 1) {
    		$this->sadd($destination, array($member));
    	}
    	$this->proxy->db->unlock();
    	return $c;
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
    		$this->verify($key, 1);
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
    	if(!$data) {
    		$this->verify($key);
    		return null;
    	}
    	
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
    	$this->verify($key, 1);
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
     * @param string $keys (multiple)
     * @return multitype:string list with members of the resulting set.
     *
     */
    public function sunion($keys) {
    	$this->proxy->generic->gc();
    	return $this->scustom($this->_sunion_sql($keys), $keys);
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
     * @param string $keys (multiple)
     * @return integer the number of elements in the resulting set.
     */
    public function sunionstore($destination, $keys) {
    	$this->proxy->generic->gc();
    	return $this->scustomstore($destination, $this->_sunion_sql($keys), $keys);
    }
    
    private function _sunion_sql($keys) {
    	$sql = "FROM <DB> WHERE pkey=?";
    	for($i=1, $c=count($keys); $i<$c; $i++) {
    		$sql .= " OR pkey=?";
    	}
    	return $sql;
    }

}

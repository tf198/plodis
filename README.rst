Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

Implemented Functions
=====================

* GET
* SET
* DEL
* EXISTS
* KEYS
* APPEND
* MSET
* MGET
* INCR
* INCRBY
* DECR
* DECRBY
* SETEX
* TTL
* EXPIRE
* EXPIREAT
* PERSIST
* PTTL
* PEXPIRE
* PEXPIREAT
* LLEN
* LPOP
* BLPOP
* RPOP
* BRPOP
* LPUSH
* RPUSH
* LINDEX
* PUBLISH
* SUBSCRIBE
* UNSUBSCRIBE
* *POLL*
* *BPOLL*

Caveats
=======
Behavior of the above commands should be identical to a redis server with the following exceptions:

:LPUSH / RPUSH:
   do not throw an error if a key exists and is not a list (ie set by SET).  The existing value will be treated as the first item
   in the list.  For efficiency they return -1 rather than the number of items in the list - set ``Redish::$strict = true;``
   for correct documented behaviour.
:MGET:
   accepts multiple arguments as documented, but you can pass it an array instead if easier.
:PEXPIRE / PEXPIREAT / PTTL:
   items are expired correctly within a PHP session but there may be a delay of up to 200ms when communicating between processes. Set
   ``Redish::$purge_frequency`` to 0 (or any other fraction of a second) on the receiver for documented behaviour.  
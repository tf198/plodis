Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

In theory you should be able to take an application running Plodis and change to Predis with only
one or two changes - will report back when 

Current Status
==============
Built against Redis 2.2.6 with certain modules disabled:

:Generic (Keys):
   Full coverage.
:Strings:
   Full coverage.
:Hashes:
   Not implemented.
:List:
   Full coverage.
:Sets:
   Not implemented.
:Sorted Sets:
   Not implemented.
:Pub/Sub:
   Full coverage.
:Transaction:
   Partial...
:Scripting:
   Not implemented.
:Connection:
   Not implemented.
:Server:
   Not implemented

Caveats
=======

:List PUSH operations:
   do not throw an error if a key exists and is not a list (ie set by SET).  The existing value will be treated as the first item
   in the list.  For efficiency they return -1 rather than the number of items in the list - set ``Plodis_List::$strict = true;``
   for correct documented behaviour.
:Multiple arguments:
   these are accepted as an ``array()``.  If it is the last argument you can pass them as separate args so ``mget(array('key1', 'key2'))``
   and ``mget('key1', 'key2')`` are identical.
:Millisecond expiry:
   items are expired correctly within a PHP session but there may be a delay of up to 200ms when communicating between processes. Set
   ``Plodis_Generic::$purge_frequency`` to 0 (or any other fraction of a second) on the receiver for documented behaviour.
:Pub / Sub:
   this is implemented as mailbox fanout using the Lists module - should be fine for everyday work but dont try and build a **twitter** with
   it.  Might look at reference fanout in the future: http://www.scribd.com/doc/16952419/Building-scalable-complex-apps-on-App-Engine
   
Performance
===========

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  These are on my AMD Phenom II x6 3.20Ghz.

====== ==== ====== ==== ======= =======================
  Mem (KB)   Time (ms)   ops/s
====== ==== ====== ==== ======= =======================
   373 373       0   0   32263  init
   374   0       0   0    1047  PDO creating from new
   587 212       2   1     631  include
   630  43     389 387       2  construct (including tables)
   630   0     390   0   11915  free
   631   0     391   1     699  PDO from existing file
   634   3     394   2     395  construct (if not exists)
   634   0     394   0   13148  Starting loop tests - 500 iterations
   657  23     521 127    3936  SET (insert)
   657   0     568  47   10536  SET (update)
   658   0     584  16   30467  SET (update, locked)
   659   0     622  37   13405  GET
   659   0     637  15   33011  GET (locked)
   739  80     668  31   15944  LPUSH
   739   0     686  17   28970  RPUSH
   741   2    1360 674     741  LPOP
   742   0    1443  83    6012  LLEN
   742   0    2003 560     892  LINDEX
   744   1    2287 283    1760  RPOP
   744   0    2287   0   14716  cleanup
====== ==== ====== ==== ======= =======================
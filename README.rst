Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

Even if you are not planning on using Redis in production, the functions provided make it trivial to
implement queues (IPC, background processing etc) and pub/sub (webchat anyone?) so it stands alone as
a package quite happily.

In theory you should be able to take an application running Plodis and change to Predis with only
one or two changes - will update when I know for sure...


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
   in the list.  For efficiency they return -1 rather than the number of items in the list - set ``Plodis_List::$return_counts = true``
   or call ``Plodis::strict()`` for correct documented behaviour.
:INCR / DECR:
   and their related BY methods do not return the new value by default.  Set ``Plodis_String::$return_values = true`` or call ``Plodis::strict()``
   for documented behavior.
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

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  These are on my AMD Phenom II x6 3.20Ghz with each loop
set at 500.  You can expect ~8K SETs per sec and ~14K GETs in standard mode but if you lock the database you can get ~32K for both.
List operations are slower with PUSH around ~6-9K, and POP (~1-2K) is particularly bad (database optomisation needed...) but all can be speeded up by locking the database. 
As you can see the memory footprint for the package is well under 512K - no need to change ``memory_limit`` in your ``php.ini``.  If you start running
into latency issues switch to a Redis server! :-)

===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================
  370  370      0    0   35848 init
  371    0      1    0    1025 PDO creating from new
  584  212      2    1     628 include
  627   43    410  407       2 construct (including tables)
  627    0    410    0   11881 free
  627    0    411    1     708 PDO from existing file
  630    3    414    2     396 construct (if not exists)
  630    0    414    0   13486 Starting loop tests - 500 iterations
  654   23    528  113    4387 SET (insert)
  654    0    574   45   10910 SET (update)
  654    0    589   15   31960 SET (update, locked)
  655    0    625   35   14089 GET
  655    0    640   15   32940 GET (locked)
  736   80    719   78    6337 LPUSH
  736    0    773   53    9280 RPUSH
  738    2   1454  681     733 LPOP
  739    0   1536   81    6144 LLEN
  739    0   2091  555     900 LINDEX
  741    1   2374  283    1765 RPOP
  741    0   2375    0   11096 cleanup
===== ==== ====== ==== ======= =======================================


Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

Even if you are not planning on using Redis in production, the redis API makes it trivial to
implement queues (IPC, background processing etc) and pub/sub (webchat anyone?) so it stands alone as
a package quite happily.

In theory you should be able to take an application running Plodis and change to Predis with only
one or two changes - will update when I've tested...

Current Status
==============
Built from Redis 2.6.0 API with certain modules disabled and a few gaps.  Wherever a method is not implemented
it will throw a ``PlodisNotImplementedError``.

:Generic (Keys):
   Partial coverage. Need to finish 2.0.0.
:Strings:
   Full coverage up to 2.0.0.
:Hashes:
   Not implemented.
:List:
   Full coverage up to 2.0.0 plus LINSERT.
:Sets:
   Not implemented.
:Sorted Sets:
   Not implemented.
:Pub/Sub:
   Full 2.6.0 coverage except PSUBSCRIBE and PUNSUBSCRIBE
:Transaction:
   Background implementation. Currently has ``lock()`` and ``unlock()`` methods available
   on the ``Plodis::db`` module though I'll probably make this API compatible very soon.
:Scripting:
   Not implemented.  Should be possible with the PHP Lua extension though...
:Connection:
   Full coverage, though AUTH performs no actual authentication.
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

Implementation
==============
Each Plodis instance is backed by a single SQLite data file with as many optomisations turned on as possible so there is the potential for data
loss in the event of a crash (it should be possible to set some guarantees using the Server module, its just a file after all, but I haven't got round
to it yet.

TODO
====

* Finish Generic, String and List modules
* Finish preprocessor directives so we can compile for a specific version
* Make sure the test suite is complete (return types?)
* Move behavior switches from classes to CONFIG GET
* Implement other modules
* Replace transactions with savepoints
* Other optomisations (VACUUM?)
* Figure out why I spent two days cloning something that was already excellent :-)
   
Performance
===========

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  These are on my AMD Phenom II x6 3.20Ghz with each loop
set at 500.  You can expect ~4-10K SETs per sec and ~14K GETs in standard mode but if you lock the database you can get 2-3 times throughput.
List operations are optomised for RPUSH/LPOP (ie FIFO) at around ~11-13K though RPOP isn't too bad if you want a stack - just avoid LPUSH if possible.
As you can see the memory footprint for the package is around 600K - no need to change ``memory_limit`` in your ``php.ini``.  

Just rememeber that if performance becomes that important to you then you should probably shift to a Redis server! :-)

===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================
  370  370      0    0   35848 init
  371    0      1    1     897 PDO creating from new
  637  265      3    2     498 include
  727   90    484  481       2 construct (including tables)
  727    0    484    0   10205 free
  727    0    488    3     250 PDO from existing file
  731    3    491    3     317 construct (if not exists)
  731    0    491    0   12336 Starting loop tests - 500 iterations
  829   98    616  124    4010 SET (insert)
  829    0    664   47   10489 SET (update)
  829    0    681   17   28715 SET (update, locked)
  830    0    715   34   14622 GET
  830    0    730   14   34298 GET (locked)
  945  115    855  124    4013 LPUSH
  945    0    891   36   13687 RPUSH
  948    2    935   44   11320 LPOP
  948    0    996   60    8253 LLEN
  948    0   1037   41   12099 LINDEX
  948    0   1093   56    8927 RPOP
  948    0   1093    0    7463 cleanup
===== ==== ====== ==== ======= =======================================


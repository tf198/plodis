Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis_ functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

Even if you are not planning on using Redis in production, the `Redis API`_ makes it trivial to
implement queues (IPC, background processing etc), pub/sub (webchat anyone?) and auto-expiring data
(output caching) so it stands alone as a package quite happily.

In theory you should be able to take an application running Plodis and change to Predis_ with only
one or two changes - will update when I've tested...

.. _Redis: http://redis.io
.. _Predis: https://github.com/nrk/predis/
.. _Redis Api: http://redis.io/commands

Current Status
==============
Built from Redis 2.6.0 API with certain modules disabled and a few gaps.  Wherever a method is not implemented
it will throw a ``PlodisNotImplementedError``.

:Generic (Keys):
   Partial coverage. Need to finish 2.0.0.
:Strings:
   Full coverage up to 2.0.0.
:Hashes:
   Full 2.6.0 coverage.
:List:
   Full 2.6.0 coverage.
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
   Full 2.6.0 coverage.
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
:AUTH:
   wont throw an error but doesn't actually do anything

Implementation
==============
Each Plodis instance is backed by a single SQLite data file with as many optomisations turned on as possible so there is the potential for data
loss in the event of a crash (it should be possible to set some guarantees using the Server module, its just a file after all, but I haven't got round
to it yet.

=======  =======  =======  =======  =======  =======
         String   List     Hash     Set      ZSet
=======  =======  =======  =======  =======  =======
id       AUTO     AUTO     AUTO     AUTO     AUTO
key      key      key      key      key      key
field    NULL     NULL     field    value    value
weight   NULL     -ve      1        NULL     -ve
item     value    value    value    NULL     NULL
=======  =======  =======  =======  =======  =======

TODO
====

* Finish Generic, String and List modules
* Finish preprocessor directives so we can compile for a specific version
* Make sure the test suite is complete (return types?)
* Move behavior switches from classes to CONFIG GET
* Implement other modules
* Other optomisations (VACUUM?)
* Figure out why I spent two days cloning something that was already excellent :-)
   
Performance
===========

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  You can expect ~4-8K SETs per sec and ~13K/s GETs in standard mode 
but if you lock the database you can get 2-3 times throughput. List operations are fairly consistent around ~9K/s, though LPUSH is a bit slower.
As you can see the memory footprint for the package is around 600K - no need to change ``memory_limit`` in your ``php.ini``.  

The benchmarks are all run on my AMD Phenom II x6 3.20Ghz using a *dirty* database - i.e. the data from previous runs is left in so it gives a good idea of real world usage
and each loop set at 1000.

Just rememeber that if performance becomes that important to you then you should probably shift to a Redis server! :-)

===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================
  369  369      0    0   24385 init (9170)
  634  265      1    1     518 include
  635    0      2    0    3953 PDO from existing data
  725   90      4    2     384 construct
  725    0      4    0   45590 Starting loop tests - 1000 iterations
  824   98    250  245    4076 SET (insert)
  824    0    384  133    7469 SET (update)
  824    0    422   38   26227 SET (update, locked)
  825    0    499   76   12988 GET
  825    0    529   29   33407 GET (locked)
  940  115    675  146    6849 LPUSH
  940    0    779  104    9548 RPUSH
  942    2    891  111    8964 LPOP
  943    0   1104  213    4690 LLEN
  943    0   1234  129    7707 LINDEX
  943    0   1343  108    9187 RPOP
  944    0   1343    0    9425 cleanup
===== ==== ====== ==== ======= =======================================



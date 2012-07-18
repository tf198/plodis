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
   Full 2.6.0 coverage.
:Hashes:
   Full 2.6.0 coverage.
:List:
   Full 2.6.0 coverage.
:Sets:
   Full 2.6.0 coverage.
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

Options
=======
By default Plodis should behave exactly as a Redis instance with the same guarantees about **atomicity** and type checking.  The following options
are provided to turn off some of the strict checking and fine tune performance options.  Once you have fully tested your app you should be able to
turn off many of these and see 2-3 times throughput on some operations, particularly LIST ops.

Use ``$plodis->setOption($name, $value)`` and ``$plodis->getOption($name)`` to modify the options. 

:return_counts (``true``):
   [ RPUSH, LPUSH, LINSERT, RPUSHX, LPUSHX ] return the size of the list after the operation which requires an additional query.  If this is set
   to ``false``, -1 is returned instead.
:return_incr_values (``true``):
   [ INCR, DECR, INCRBY, DECRBY, INCRBYFLOAT, HINCRBY, HINCRBYFLOAT ] return the new value of the item which requires an additional query.  if this is 
   set to ``false``, null is returned instead.
:validation_checks (``true``):
   many methods will throw an exception if the key type is incorrect (e.g. if you try to LPUSH to a String).  Where possible these checks are integrated
   into the operation, but sometimes a separate query is required to check the key.  Settings this to ``false`` bypasses these queries.
:poll_frequency (0.1):
   [ BRPOP, BLPOP, BRPOPLPUSH, BPOLL (PubSub) ] are not true blocking functions but poll every 100ms.  Make this faster or slower as required.
:purge_frequency (0.2):
   items are expired correctly within a PHP session but there may be a delay of up to 200ms when communicating between processes. Set
   this to 0 (or any other fraction of a second as required) on the receiver for documented behaviour.

Implementation
==============
Each Plodis instance is backed by a single SQLite data file with as many optomisations turned on as possible so there is the potential for data
loss in the event of a crash (it should be possible to set some guarantees using the Server module, its just a file after all, but I haven't got round
to it yet).

TODO
====

* Figure out what we do in the event of a crash (delete and recreate file)
* Finish Generic, String, Set and ZSet modules
* Finish preprocessor directives so we can compile for a specific version
* Make sure the test suite is complete (return types?)
* Other optomisations (VACUUM?)
* Figure out why I spent two days cloning something that was already excellent :-)
   
Performance
===========

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  You can expect ~4-6K SETs per sec and ~13K/s GETs in standard mode 
but if you lock the database you can get 2-3 times throughput. List operations are fairly consistent around ~6K/s.
As you can see the memory footprint for the package is around 700K - no need to change ``memory_limit`` in your ``php.ini``.  

The benchmarks are all run on my AMD Phenom II x6 3.20Ghz using a *dirty* database - i.e. the data from previous runs is left in so it gives a good idea of real world usage
and each loop set at 1000.  ``return_counts`` and ``validation_checks`` are both set to ``false``.

Just rememeber that if performance becomes that important to you then you should probably shift to a Redis server! :-)

===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================
  370  370      0    0   24966 init (1499)
  689  319      2    2     472 include
  690    0      2    0    3905 PDO from existing data
  798  108      5    3     326 construct
  799    0      5    0   34663 Starting loop tests - 1000 iterations
  906  107    226  221    4517 SET (insert)
  906    0    383  157    6366 SET (update)
  906    0    445   61   16351 SET (update, locked)
  907    0    524   79   12600 GET
  907    0    555   31   32115 GET (locked)
 1053  146    777  221    4508 LPUSH
 1053    0    965  187    5325 RPUSH
 1056    2   1107  142    7029 LPOP
 1057    0   1261  154    6488 LLEN
 1057    0   1438  176    5667 LINDEX
 1058    1   1570  132    7554 RPOP
 1058    0   1570    0   45590 cleanup
===== ==== ====== ==== ======= =======================================





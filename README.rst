Plodis
------

\ **P**\ HP **Lo**\ cal **Di**\ ctionary **S**\ ervice implements the most commonly used 
Redis_ functions using a PDO backend.  Intended for prototyping where you
dont have a Redis server available or deployment to hosted servers where unable to install Redis.

Even if you are not planning on using Redis in production, the `Redis API`_ makes it trivial to
implement queues (IPC, background processing etc), pub/sub (webchat anyone?) and auto-expiring data
(output caching) so it stands alone as a package quite happily.

You can take an application running with Plodis and swap it to Predis_ and a proper Redis_ instance with
no changes to your code - both the benchmarks and the unittest suite are run against both.

.. _Redis: http://redis.io
.. _Predis: https://github.com/nrk/predis/
.. _Redis Api: http://redis.io/commands

Current Status
==============
Built from Redis 2.4.0 API with certain modules disabled and a few gaps.  Wherever a method is not implemented
it will throw a ``PlodisNotImplementedError``.

:Generic (Keys):
   Full 2.4.0 coverage except OBJECT.
:Strings:
   Full 2.4.0 coverage.
:Hashes:
   Full 2.4.0 coverage.
:List:
   Full 2.4.0 coverage.
:Sets:
   Full 2.4.0 coverage.
:Sorted Sets:
   Still to do...
:Pub/Sub:
   Full 2.4.0 coverage.
:Transaction:
   Background implementation. Currently has ``lock()`` and ``unlock()`` methods available
   on the ``Plodis::db`` module though I'll probably make this API compatible very soon.
:Scripting:
   Not implemented.  Should be possible with the PHP Lua extension though...
:Connection:
   Full 2.4.0 coverage.
:Server:
   Implemented the methods that make sense...

Options
=======
By default Plodis should behave exactly as a Redis instance with the same guarantees about **atomicity** and type checking.  The following options
are provided to turn off some of the strict checking and fine tune performance options.  Once you have fully tested your app you should be able to
turn off many of these and see 2-3 times throughput on some operations, particularly LIST ops.

Use ``$plodis->setOption($name, $value)`` and ``$plodis->getOption($name)`` to modify the options 
(or ``$plodis->config_get($name)`` and ``$plodis->config_set($name, $value)`` to use the Server module). 

:return_counts (``true``):
   [ RPUSH, LPUSH, LINSERT, RPUSHX, LPUSHX ] return the size of the list after the operation which requires an additional query.  If this is set
   to ``false``, -1 is returned instead.
:return_incr_values (``true``):
   [ INCR, DECR, INCRBY, DECRBY, INCRBYFLOAT, HINCRBY, HINCRBYFLOAT ] return the new value of the item which requires an additional query.  if this is 
   set to ``false``, null is returned instead.
:validation_checks (``true``):
   many methods will throw an exception if the key type is incorrect (e.g. if you try to LPUSH to a String).  Where possible these checks are integrated
   into the operation, but sometimes a separate query is required to check the key.  Settings this to ``false`` bypasses these queries.
:predis_compatible (``false``):
	**TODO** predis converts 1|0 return values to booleans and casts INCRBYFLOAT methods to float.  Set this to ``true`` for Predis_ behavior.
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
* Finish modules: ZSet, Transaction
* Make sure the test suite is complete (return types?)
* Implement predis compatible flag for return types.
* Other optomisations (VACUUM?)
* See how difficult it would be to make the SQL run on MySQL as well.
* Figure out why I spent 2+ days cloning something that was already excellent :-)
   
Performance
===========

I thought it would be rubbish but actually it's not bad using a dedicated SQLite database.  You can expect ~4-6K SETs per sec and ~9K/s GETs in standard mode 
but if you lock the database you can get 2-3 times throughput. List operations are fairly consistent around 5K/s.
As you can see the memory footprint for the package is around 700K - no need to change ``memory_limit`` in your ``php.ini``.  

The benchmarks are all run on my AMD Phenom II x6 3.20Ghz using a *dirty* database - i.e. the data from previous runs is left in so it gives a good idea of real world usage
and each loop set at 1000.  ``return_counts`` and ``validation_checks`` are both set to ``false``.

If I run the same benchmark using Predis connecting to a Redis server running in a VM I get ~5K/s consistently for all operations and ~12K/s
for pipelining.  Running the benchmarks on the same VM against localhost gets ~15K/s.  Suggests that the transport overheads with Predis 
and the database inefficiencies of Plodis roughly balance out, not that I am suggesting you should use Plodis for high throughput 
production servers :-)  

===== ==== ====== ==== ======= =======================================
Mem (KB)   Time (ms)     Ops   Description
---------- ----------- ------- ---------------------------------------
Total Step Total  Step  ops/s
===== ==== ====== ==== ======= =======================================
  393  393      0    0   39945 init (7294)
  781  388      2    2     347 include PLODIS
  781    0      3    0    3236 PDO from existing data
  930  148     28   25      39 construct PLODIS
  930    0     28    0   31300 Starting loop tests - 1000 iterations
 1120  189    256  227    4389 SET (insert)
 1120    0    425  169    5912 SET (update)
 1120    0    493   67   14742 SET (pipelined)
 1121    0    603  110    9056 GET
 1121    0    643   40   24838 GET (pipelined)
 1295  173    878  234    4263 LPUSH
 1295    0   1074  195    5110 RPUSH
 1297    2   1232  158    6297 LPOP
 1298    0   1421  188    5295 LLEN
 1298    0   1692  270    3693 LINDEX
 1300    1   1841  148    6731 RPOP
 1390   90   2025  184    5413 HSET
 1392    1   2086   60   16556 HGET
 1509  117   2213  126    7880 SADD (RAND 10)
 1509    0   2339  126    7919 SADD (RAND 100)
 1509    0   2339    0   41943 cleanup
===== ==== ====== ==== ======= =======================================






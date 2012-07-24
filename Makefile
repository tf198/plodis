REDIS_VERSION=2_4_0

REDIS_GROUPS=Connection Server Generic String List Hash Set Sorted_Set Pubsub

GROUP_INTERFACES=$(addprefix lib/Plodis/IRedis_,$(addsuffix _$(REDIS_VERSION).php,$(REDIS_GROUPS)))

all: $(GROUP_INTERFACES) lib/Plodis.php

src/redis-doc:
	git clone git://github.com/tf198/redis-doc.git $@ || true

lib/Plodis/IRedis_%_$(REDIS_VERSION).php: src/generate_interface.php src/generate_common.php src/redis-doc
	php $< $(REDIS_VERSION) $* > $@
	
lib/Plodis.php: src/generate_proxy.php src/generate_common.php src/redis-doc $(GROUP_INTERFACES)
	php $< $(REDIS_VERSION) $(REDIS_GROUPS) > $@
	
%.profile:
	php -d xdebug.profiler_enable=1 -d xdebug.profiler_output_dir=profile $*
	
src/predis:
	git clone git://github.com/nrk/predis.git $@ || true
	
predis.phar: src/predis
	php -d phar.readonly=0 $</bin/create-phar.php
	mv predis_*.phar $@

plodis.phar:
	php -d phar.readonly=0 src/generate_phar.php $@

%.check:
	phpunit -c phpunit_$*.xml

plodis.check:
	phpunit

check: plodis.check predis.check mysql.check
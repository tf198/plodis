REDIS_VERSION=2_6_0

REDIS_GROUPS=Connection Generic String List Hash Pubsub

GROUP_INTERFACES=$(addprefix interfaces/Redis_,$(addsuffix _$(REDIS_VERSION).php,$(REDIS_GROUPS)))

all: $(GROUP_INTERFACES) Plodis.php

src/redis-doc:
	git clone git://github.com/tf198/redis-doc.git $@ || true

interfaces/Redis_%_$(REDIS_VERSION).php: src/generate_interface.php src/generate_common.php src/redis-doc
	php $< $(REDIS_VERSION) $* > $@
	
Plodis.php: src/generate_proxy.php src/generate_common.php src/redis-doc $(GROUP_INTERFACES)
	php $< $(REDIS_VERSION) $(REDIS_GROUPS) > $@
	
%.profile:
	php -d xdebug.profiler_enable=1 -d xdebug.profiler_output_dir=profile $*
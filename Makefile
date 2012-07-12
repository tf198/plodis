REDIS_VERSION=2_6_0

REDIS_GROUPS=Generic String List Pubsub

GROUP_INTERFACES=$(addprefix interfaces/Redis_,$(addsuffix _$(REDIS_VERSION).php,$(REDIS_GROUPS)))

all: $(GROUP_INTERFACES) Plodis.php
	
interfaces/Redis_%_$(REDIS_VERSION).php: src/generate_interface.php src/redis-doc
	php $< $(REDIS_VERSION) $* > $@
	
Plodis.php: src/generate_proxy.php src/redis-doc
	php $< $(REDIS_VERSION) $(REDIS_GROUPS) > $@
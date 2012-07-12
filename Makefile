REDIS_INTERFACES=1_0_0 1_2_0 2_0_0

interfaces: $(addprefix src/Redis_,$(addsuffix .php,$(REDIS_INTERFACES)))

src/Redis_%.php: src/generate_stubs.php src/redis-doc
	php $< $* > $@
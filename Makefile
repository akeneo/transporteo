#=== Akeneo PIM Migration tool helper ===

# Styles
YELLOW=$(shell echo "\033[00;33m")
RED=$(shell echo "\033[00;31m")
RESTORE=$(shell echo "\033[0m")

CURRENT_DIR := $(shell pwd)

.PHONY: list
list:
	@echo ""
	@echo "Akeneo PIM Migration tool available targets:"
	@echo ""
	@echo "  $(YELLOW)fix-style$(RESTORE)     > run the PHP-CS-FIXER"
	@echo ""
	@echo ""
	@echo "  $(YELLOW)install$(RESTORE)      > install vendors"
	@echo "  $(YELLOW)update(RESTORE)        > update vendors"
	@echo "  $(YELLOW)run$(RESTORE)          > run the tool"
	@echo "  $(YELLOW)clean$(RESTORE)        > removes the vendors"

.PHONY: fix-style
fix-style:
	docker-compose run php vendor/bin/php-cs-fixer fix --config=./.php_cs.php

.PHONY: install
install:
	docker-compose run php composer install

.PHONY: update
update:
	docker-compose run php composer update

.PHONY: clean
clean:
	rm -rf vendor

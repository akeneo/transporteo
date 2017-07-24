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
	@echo "  $(YELLOW)test$(RESTORE)          > run All tests"
	@echo "  $(YELLOW)phpspec-run$(RESTORE)   > run All PHPSpec tests"
	@echo "  $(YELLOW)phpspec(RESTORE)        > run PHPSpec"
	@echo "  $(YELLOW)phpunit$(RESTORE)       > run All PHPUnit"
	@echo "  $(YELLOW)enter$(RESTORE)         > enter in the PHP container"
	@echo "  $(YELLOW)launch(RESTORE)         > Launch the tool"
	@echo ""
	@echo ""
	@echo "  $(YELLOW)composer$(RESTORE)      > run composer"
	@echo "  $(YELLOW)install$(RESTORE)       > install vendors"
	@echo "  $(YELLOW)update$(RESTORE)        > update vendors"
	@echo "  $(YELLOW)run$(RESTORE)           > run the tool"
	@echo "  $(YELLOW)clean$(RESTORE)         > removes the vendors"

.PHONY: fix-style
fix-style:
	docker-compose run php vendor/bin/php-cs-fixer fix --config=./.php_cs.php

.PHONY: enter
enter:
	docker-compose run php /bin/bash

.PHONY: launch
launch:
	docker-compose run php php MigrationTool.php akeneo-pim:migrate

.PHONY: test
test: | phpspec-run phpunit

.PHONY: phpspec-run
phpspec-run:
	docker-compose run php ./vendor/bin/phpspec run ${ARGS}

.PHONY: phpunit
phpunit:
	docker-compose run php ./vendor/bin/phpunit ${ARGS}

.PHONY: phpspec
phpspec:
	docker-compose run php ./vendor/bin/phpspec ${ARGS}

.PHONY: composer
composer:
	docker-compose run php composer ${ARGS}

.PHONY: install
install:
	docker-compose run php composer install

.PHONY: update
update:
	docker-compose run php composer update

.PHONY: clean
clean:
	rm -rf vendor

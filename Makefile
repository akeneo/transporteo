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
	@echo "  $(YELLOW)commit$(RESTORE)             > run pre commit stuff"
	@echo "  $(YELLOW)fix-style$(RESTORE)          > run the PHP-CS-FIXER"
	@echo "  $(YELLOW)test$(RESTORE)               > run All tests"
	@echo "  $(YELLOW)phpspec-run$(RESTORE)        > run All PHPSpec tests"
	@echo "  $(YELLOW)phpspec$(RESTORE)            > run PHPSpec"
	@echo "  $(YELLOW)phpunit$(RESTORE)            > run All PHPUnit"
	@echo "  $(YELLOW)launch$(RESTORE)             > Launch the tool"
	@echo "  $(YELLOW)dump-state-machine$(RESTORE) > Dump the State Machine"
	@echo ""
	@echo ""
	@echo "  $(YELLOW)composer$(RESTORE)      > run composer"
	@echo "  $(YELLOW)install$(RESTORE)       > install vendors"
	@echo "  $(YELLOW)update$(RESTORE)        > update vendors"
	@echo "  $(YELLOW)run$(RESTORE)           > run the tool"
	@echo "  $(YELLOW)clean$(RESTORE)         > removes the vendors"

.PHONY: commit
commit: | fix-style test dump-state-machine

.PHONY: fix-style
fix-style:
	vendor/bin/php-cs-fixer fix --config=./.php_cs.php

.PHONY: launch
launch:
	php MigrationTool.php akeneo-pim:migrate

.PHONY: dump-state-machine
dump-state-machine:
	php MigrationTool.php state-machine:dump
	dot -Tpng stateMachineMigrationTool.dot -o stateMachineMigrationTool.png

.PHONY: test
test: | phpspec-run phpunit

.PHONY: phpspec-run
phpspec-run:
	./vendor/bin/phpspec run ${ARGS}

.PHONY: phpunit
phpunit:
	./vendor/bin/phpunit ${ARGS}

.PHONY: phpspec
phpspec:
	./vendor/bin/phpspec ${ARGS}

.PHONY: composer
composer:
	composer ${ARGS}

.PHONY: install
install:
	composer install

.PHONY: update
update:
	composer update

.PHONY: clean
clean:
	rm -rf vendor

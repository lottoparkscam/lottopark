docker_compose:=docker-compose
ifeq ($(shell which docker-compose),)
	ifneq ($(shell which docker),)
		docker_compose:=docker compose
	endif
endif

help:
	@echo "This file is intended to simplify the DEVELOPMENT of this project"
	@echo ""
	@echo "Available commands:"
	@echo ""
	@echo "check       - phpstan, cs, tests, should be called before each git-push"
	@echo "  stan     -  phpstan validation base on phpstan.neon file"
	@echo "  phpcs     - checks violations of psr-12 standard"
	@echo "  phpcs_fix - attempts to fix violations of psr-12 standard"
	@echo ""
	@echo "build       - brings up docker setup, recommended to run for the first project run"
	@echo "up          - brings up project when already built"
	@echo "down"
	@echo "prune       - [DANGEROUS] cleans up the build & project files, all untracked changes will be lost"
	@echo ""
	@echo "tests       - runs unit & feature test suites at once"
	@echo "  tests_unit"
	@echo "  tests_feature"
	@echo "  tests_e2e"
	@echo ""
	@echo "tests_fail	- runs all failed tests (random or skipped)"
	@echo "  tests_unit_fail"
	@echo "  tests_feature_fail"
	@echo ""
	@echo "composer_install"
	@echo "composer_dump			- calls dump-autoload"
	@echo "composer_update_lock		- update composer state to locked file, use it when invalid conflicts on remote branch appears"
	@echo ""
	@echo "oil command='r cache:reset'         - allows to run any oil command"
	@echo "forge_migration NAME=migration_name - creates new migration file with given name"

# --- DOCKER ---
build:
	sudo ./docker/docker.sh
	@echo ""
	@echo "IMPORTANT!"
	@echo "Create chown.sh file and follow the instructions: https://gginternational.slite.com/app/channels/4mtH3PN_5R/notes/S~CQ_EnADf#42429a47"

up:
	$(docker_compose) up -d

down:
	$(docker_compose) down

prune:
	git clean -df
	git checkout -- .
	git reset
	$(docker_compose) down --volumes
	docker system prune -f

# --- QUALITY TOOLS ---
check: composer_dump stan phpcs tests
stan:
	$(docker_compose) exec php composer --working-dir=platform phpstan
phpcs:
	$(docker_compose) exec php composer --working-dir=platform phpcs
phpcs_fix:
	$(docker_compose) exec php composer --working-dir=platform phpcbf
phpcs_fuel_fix:
	$(docker_compose) exec php composer --working-dir=platform phpcs-fuel
phpcs_autofix: phpcs_fix phpcs_fuel_fix

# --- TESTS ---
tests: tests_unit tests_feature

tests_unit:
	$(docker_compose) exec php composer --working-dir=platform tests:unit
tests_feature:
	$(docker_compose) exec php composer --working-dir=platform tests:feature
tests_e2e:
	$(docker_compose) exec php composer --working-dir=platform tests:e2e
tests_selenium:
	$(docker_compose) exec php composer --working-dir=platform tests:selenium
tests_react:
	$(docker_compose) exec php npm run react-tests
tests_js:
	$(docker_compose) exec php npm run js-tests

tests_fail: tests_unit_fail tests_feature_fail
tests_unit_fail:
	$(docker_compose) exec php composer --working-dir=platform tests:unit:fail
tests_feature_fail:
	$(docker_compose) exec php composer --working-dir=platform tests:feature:fail

# Groups
tests_group_fixture:
	$(docker_compose) exec php composer --working-dir=platform tests:unit:fixture
	$(docker_compose) exec php composer --working-dir=platform tests:feature:fixture

# --- COMMANDS ---
cache_reset:
	$(docker_compose) exec php composer --working-dir=platform cache_reset
migration_fresh:
	$(docker_compose) exec php composer --working-dir=platform migration:fresh
migrate:
	$(docker_compose) exec php composer --working-dir=platform migrate

oil:
	$(docker_compose) exec php composer --working-dir=platform oil $(COMMAND)

# Forge
NAME=new_migration
forge_migration:
	$(docker_compose) exec php composer --working-dir=platform forge:migration $(NAME)

# --- UTILS ---
composer_dump:
	$(docker_compose) exec php composer --working-dir=platform dump-autoload

composer_install:
	$(docker_compose) exec php composer --working-dir=platform install

composer_update_lock:
	$(docker_compose) exec php composer --working-dir=platform update --lock

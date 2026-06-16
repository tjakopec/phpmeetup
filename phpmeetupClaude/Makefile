.PHONY: help install test test-unit test-api stan cs cs-fix fixtures migrate setup

PHP = php
COMPOSER = /opt/homebrew/bin/composer
CONSOLE = $(PHP) bin/console
PHPUNIT = $(PHP) vendor/bin/phpunit
PHPSTAN = $(PHP) vendor/bin/phpstan
CS_FIXER = $(PHP) vendor/bin/php-cs-fixer

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	$(COMPOSER) install

setup: install migrate fixtures ## Full setup: install + migrate + seed fixtures

migrate: ## Run database migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures: ## Load data fixtures (zones, tariffs, postal codes)
	$(CONSOLE) doctrine:fixtures:load --no-interaction

test: ## Run all tests
	$(PHPUNIT)

test-unit: ## Run unit tests only
	$(PHPUNIT) --testsuite Unit

test-api: ## Run API functional tests only
	$(PHPUNIT) --testsuite Api

stan: ## Run PHPStan static analysis
	$(PHPSTAN) analyse

cs: ## Check code style (dry-run)
	$(CS_FIXER) fix --dry-run --diff

cs-fix: ## Fix code style
	$(CS_FIXER) fix

cache-clear: ## Clear Symfony cache
	$(CONSOLE) cache:clear

db-reset: ## Drop and recreate the database, run migrations and fixtures
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:migrations:migrate --no-interaction
	$(CONSOLE) doctrine:fixtures:load --no-interaction

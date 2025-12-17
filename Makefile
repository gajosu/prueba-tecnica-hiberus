.PHONY: help setup up down install migrate fixtures test clean build dev

help: ## Show this help
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Run complete setup script
	@chmod +x setup.sh
	@./setup.sh

up: ## Start Docker containers
	@docker-compose up -d
	@echo "Containers started. Waiting for MySQL to be ready..."
	@sleep 5

down: ## Stop and remove Docker containers
	@docker-compose down

stop: ## Stop containers without removing them
	@docker-compose stop

restart: ## Restart containers
	@docker-compose restart

install: ## Install dependencies (composer + npm)
	@echo "Installing Composer dependencies..."
	@docker-compose exec php composer install
	@echo "Installing npm dependencies..."
	@docker-compose exec php npm install

migrate: ## Run Doctrine migrations
	@docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff: ## Create new migration
	@docker-compose exec php php bin/console doctrine:migrations:diff

fixtures: ## Load fixtures (if any)
	@if docker-compose exec php test -f "src/DataFixtures"; then \
		docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction; \
	else \
		echo "No fixtures configured yet"; \
	fi

test: ## Run unit tests
	@docker-compose exec php php bin/phpunit

test-coverage: ## Run tests with coverage
	@docker-compose exec php php bin/phpunit --coverage-html var/coverage

clean: ## Clear cache and logs
	@docker-compose exec php php bin/console cache:clear
	@docker-compose exec php rm -rf var/cache/*
	@docker-compose exec php rm -rf var/log/*

build: ## Build assets with Vite
	@docker-compose exec php npm run build

dev: ## Start Vite development server
	@docker-compose exec php npm run dev

logs: ## View Docker logs
	@docker-compose logs -f

shell: ## Open shell in PHP container
	@docker-compose exec php bash

db-shell: ## Open PostgreSQL shell
	@docker-compose exec database psql -U app -d app

reset: ## Reset everything (stop, clean volumes, reinstall)
	@docker-compose down -v
	@make clean
	@make up
	@sleep 10
	@make install
	@make migrate


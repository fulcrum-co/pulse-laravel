.PHONY: help build up down restart logs shell install fresh migrate seed test

# Colors
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)

help: ## Show this help
	@echo ''
	@echo 'Usage:'
	@echo '  ${YELLOW}make${RESET} ${GREEN}<target>${RESET}'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  ${YELLOW}%-15s${RESET} %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## View Docker logs
	docker-compose logs -f

shell: ## Open shell in app container
	docker-compose exec app bash

install: ## First-time setup: build, install Laravel, install dependencies
	@echo "Building containers..."
	docker-compose build
	@echo "Starting containers..."
	docker-compose up -d
	@echo "Waiting for containers to be ready..."
	sleep 5
	@echo "Installing Laravel..."
	docker-compose exec -T app composer create-project laravel/laravel temp --prefer-dist
	docker-compose exec -T app sh -c 'mv temp/* temp/.* . 2>/dev/null || true && rmdir temp'
	@echo "Installing MongoDB package..."
	docker-compose exec -T app composer require mongodb/laravel-mongodb
	@echo "Installing additional packages..."
	docker-compose exec -T app composer require laravel/socialite livewire/livewire barryvdh/laravel-dompdf
	@echo "Copying environment file..."
	docker-compose exec -T app cp .env.example .env
	@echo "Generating application key..."
	docker-compose exec -T app php artisan key:generate
	@echo "Creating storage link..."
	docker-compose exec -T app php artisan storage:link
	@echo ""
	@echo "${GREEN}Installation complete!${RESET}"
	@echo "Visit: http://localhost:8000"

fresh: ## Fresh install: down, remove volumes, install
	docker-compose down -v
	$(MAKE) install

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

test: ## Run tests
	docker-compose exec app php artisan test

composer: ## Run composer command (usage: make composer cmd="require package-name")
	docker-compose exec app composer $(cmd)

artisan: ## Run artisan command (usage: make artisan cmd="make:model User")
	docker-compose exec app php artisan $(cmd)

tinker: ## Open Laravel Tinker
	docker-compose exec app php artisan tinker

queue: ## Start queue worker
	docker-compose exec app php artisan queue:work

mongodb-shell: ## Open MongoDB shell
	docker-compose exec mongodb mongosh -u pulse_user -p pulse_secret --authenticationDatabase admin pulse

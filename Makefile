SHELL := /bin/sh

.PHONY: up dev down restart logs ps migrate seed key

up:
	docker compose up -d --build

dev:
	docker compose up -d --build
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d vite

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d --build

logs:
	docker compose logs -f --tail=100

ps:
	docker compose ps

key:
	docker compose exec app php artisan key:generate

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

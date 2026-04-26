# OpenTogG Makefile

SSH_HOST    = testing
REMOTE_PATH = /var/www/html/opentogg
RSYNC_FLAGS = -rltvz --delete --no-perms --no-owner --no-group --exclude-from='.rsync-exclude'
DC          = docker compose

.PHONY: up down restart logs shell migrate fresh seed test npm-dev npm-build deploy sync post-deploy env dry-run

## ── Docker Compose ───────────────────────────────────────────

up:
	$(DC) up -d

down:
	$(DC) down

restart:
	$(DC) down && $(DC) up -d

logs:
	$(DC) logs -f

shell:
	$(DC) exec app bash

## ── Database ─────────────────────────────────────────────────

migrate:
	$(DC) exec app php artisan migrate

fresh:
	$(DC) exec app php artisan migrate:fresh --seed

seed:
	$(DC) exec app php artisan db:seed

## ── Dev ──────────────────────────────────────────────────────

npm-dev:
	$(DC) exec app npm run dev

npm-build:
	$(DC) exec app npm run build

test:
	$(DC) exec app php artisan test

## ── Deploy ───────────────────────────────────────────────────

deploy: npm-build sync env post-deploy

sync:
	rsync $(RSYNC_FLAGS) ./ $(SSH_HOST):$(REMOTE_PATH)/

post-deploy:
	ssh $(SSH_HOST) 'cd $(REMOTE_PATH) && \
		php artisan migrate --force && \
		php artisan config:cache && \
		php artisan route:cache && \
		php artisan view:cache && \
		php artisan event:cache'

env:
	scp .env.testing $(SSH_HOST):$(REMOTE_PATH)/.env

dry-run:
	rsync $(RSYNC_FLAGS) --dry-run ./ $(SSH_HOST):$(REMOTE_PATH)/

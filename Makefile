SHELL := /bin/bash

COMPOSE := docker compose
APP := $(COMPOSE) run --rm app

.PHONY: help dev prod-build prod-up prod-down install test integration-test analyse format format-check quality publisher subscriber frontend frontend-build shell logs clean

help:
	@echo "make dev            Start local infrastructure"
	@echo "make install        Install Composer dependencies"
	@echo "make test           Run unit/feature Pest tests"
	@echo "make integration-test Test real MQTT broker round-trip"
	@echo "make analyse        Run PHPStan"
	@echo "make format         Fix PHP formatting"
	@echo "make format-check   Check formatting"
	@echo "make quality        Format + PHPStan + tests"
	@echo "make publisher      Run MQTT publisher"
	@echo "make subscriber     Run MQTT subscriber"
	@echo "make frontend       Start React realtime gauge"
	@echo "make prod-build     Build production images"
	@echo "make prod-up        Start production stack"
	@echo "make prod-down      Stop production stack"

dev:
	$(COMPOSE) -f docker-compose.yml up -d --build mqtt redis soketi

install:
	$(APP) composer install --no-interaction --prefer-dist

test:
	$(APP) composer test

analyse:
	$(APP) composer analyse

format:
	$(APP) composer format

format-check:
	$(APP) composer format:check

quality:
	$(APP) composer quality

publisher:
	COMPOSER_PROCESS_TIMEOUT=0 $(APP) composer publisher

subscriber:
	COMPOSER_PROCESS_TIMEOUT=0 $(APP) composer subscriber

frontend:
	$(COMPOSE) up -d --build frontend

frontend-build:
	$(COMPOSE) run --rm frontend npm run build

integration-test:
	$(COMPOSE) up -d --build mqtt redis soketi
	$(COMPOSE) run --rm -e MQTT_INTEGRATION=1 app composer test:integration

shell:
	$(APP) bash

logs:
	$(COMPOSE) logs -f --tail=100

prod-build:
	$(COMPOSE) -f docker-compose.prod.yml build --pull

prod-up:
	$(COMPOSE) -f docker-compose.prod.yml up -d

prod-down:
	$(COMPOSE) -f docker-compose.prod.yml down

clean:
	$(COMPOSE) down -v --remove-orphans

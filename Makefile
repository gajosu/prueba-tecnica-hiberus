.PHONY: help setup up down install migrate fixtures test clean build dev

help: ## Mostrar esta ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Ejecutar script de setup completo
	@chmod +x setup.sh
	@./setup.sh

up: ## Levantar contenedores Docker
	@docker-compose up -d
	@echo "Contenedores levantados. Esperando a que MySQL esté listo..."
	@sleep 5

down: ## Detener contenedores Docker
	@docker-compose down

stop: ## Detener contenedores sin eliminarlos
	@docker-compose stop

restart: ## Reiniciar contenedores
	@docker-compose restart

install: ## Instalar dependencias (composer + npm)
	@echo "Instalando dependencias de Composer..."
	@composer install
	@echo "Instalando dependencias de npm..."
	@npm install

migrate: ## Ejecutar migraciones de Doctrine
	@php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff: ## Crear nueva migración
	@php bin/console doctrine:migrations:diff

fixtures: ## Cargar fixtures (si existen)
	@if [ -f "src/DataFixtures" ]; then \
		php bin/console doctrine:fixtures:load --no-interaction; \
	else \
		echo "No hay fixtures configuradas aún"; \
	fi

test: ## Ejecutar pruebas unitarias
	@php bin/phpunit

test-coverage: ## Ejecutar pruebas con cobertura
	@php bin/phpunit --coverage-html var/coverage

clean: ## Limpiar cache y logs
	@php bin/console cache:clear
	@rm -rf var/cache/*
	@rm -rf var/log/*

build: ## Construir assets con Vite
	@npm run build

dev: ## Iniciar servidor de desarrollo Vite
	@npm run dev

logs: ## Ver logs de Docker
	@docker-compose logs -f

shell: ## Abrir shell en contenedor PHP
	@docker-compose exec php bash

db-shell: ## Abrir shell de MySQL
	@docker-compose exec database mysql -u app -papp app

reset: ## Resetear todo (detener, limpiar volúmenes, reinstalar)
	@docker-compose down -v
	@make clean
	@make up
	@sleep 10
	@make install
	@make migrate


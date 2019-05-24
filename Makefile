#!make
include .env
export
.DEFAULT_GOAL := help

help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: chown-logs ## Start all containers
	docker-compose up -d --build

down: ## Stop all containers
	docker-compose down

restart: down up ## Restart all started containers

install: ## Execute install script
	docker exec ${CONTAINER_NAME_PHPFPM} /bin/sh -c "cd /var/www/ && chmod +x first_install.sh && ./first_install.sh"

reinstall: restart install ## Restart containers and execute install script

reinstall-full: down clear-mysql up install ## Delete MySQL database, restart containers and execute install script

set-webhook-good: ## Set webhook with a good SSL certificate
	curl -s -k https://${NGINX_SERVER_NAME}/api/telegram/setWebhook?secret_admin_password=${SECRET_ADMIN_PASSWORD}

set-webhook-self: ## Set webhook with a self-signed SSL certificate
	curl -s -F "url=https://${NGINX_SERVER_NAME}/api/telegram/webhook" -F "certificate=@${SSL_LOCAL_CA_FILE}" https://api.telegram.org/bot${TELEGRAMBOT_BOT_TOKEN}/setWebhook | json_pp

webhook-info: ## Get webhook info
	curl -s https://api.telegram.org/bot${TELEGRAMBOT_BOT_TOKEN}/getWebhookInfo | json_pp

it-nginx: ## Get into the Nginx container terminal
	docker exec -it ${CONTAINER_NAME_NGINX} bash

it-php: ## Get into the php-fpm container terminal
	docker exec -it ${CONTAINER_NAME_PHPFPM} bash

it-mysql: ## Get into the MySQL container terminal
	docker exec -it ${CONTAINER_NAME_MYSQL} bash

it-phpmyadmin: ## Get into the Php-MyAdmin container terminal
	docker exec -it ${CONTAINER_NAME_PHPMYADMIN} sh

it-cron: ## Get into the CRON container terminal
	docker exec -it ${CONTAINER_NAME_CRON} bash

it-python: ## Get into the Python container terminal
	docker exec -it ${CONTAINER_NAME_PYTHON} bash

chown-logs: ## Set needed access rights to the logs directory
	chown -R 999:999 logs/

composer-dump: ## Composer dump-autoload
	docker exec ${CONTAINER_NAME_PHPFPM} /bin/sh -c "composer dump-autoload"

composer-update: ## Composer update
	docker exec ${CONTAINER_NAME_PHPFPM} /bin/sh -c "composer update"

composer-install: ## Composer install
	docker exec ${CONTAINER_NAME_PHPFPM} /bin/sh -c "composer install"

clear-mysql: down ## Clear all files inside the mysql directory.
	rm -r mysql_files/*

git-pull: ## Clean temporary changes and git pull from origin
	git checkout . && git clean -d -f && git pull

update: down git-pull up install ## Install a new update from remote git

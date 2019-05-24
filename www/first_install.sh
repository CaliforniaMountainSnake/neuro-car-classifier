#! /bin/bash
# Это скрипт установки приложения.
# Выполнять из-под рута в контейнере PHP-FPM.
echo 'Начинаем первичную установку...'
cd /var/www

echo 'Разбираемся с правами доступа...'
# Установим юзера вебсервера www-data в качестве владельца всех файлов:
chown -R www-data:www-data /var/www/
# Поставим права 777, чтобы рут мог писать в папки и файлы юзера www-data.
chmod -R 777 /var/www/
echo 'Пользователь www-data установлен в качестве владельца директорий и файлов, права 777.'


echo 'Загружаем зависимости composer...'
/usr/local/bin/composer install
echo 'Зависимости composer загружены.'


echo "Копируем настройки из docker's .env в laravel's .env..."
source .env.docker
php -r "if(file_exists('.env')){unlink('.env');}"
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan env:set SECRET_ADMIN_PASSWORD ${SECRET_ADMIN_PASSWORD}
php artisan env:set APP_URL https://${NGINX_SERVER_NAME}
php artisan env:set APP_URL_HTTP http://${NGINX_SERVER_NAME}
php artisan env:set APP_TIMEZONE ${CONTAINER_TIMEZONE_PHPFPM}

php artisan env:set TELEGRAMBOT_BOT_TOKEN ${TELEGRAMBOT_BOT_TOKEN}
php artisan env:set TELEGRAMBOT_USERNAME ${TELEGRAMBOT_USERNAME}
php artisan env:set TELEGRAMBOT_ADMIN_ID ${TELEGRAMBOT_ADMIN_ID}

php artisan env:set SSL_PHPFPM_CRT_FILE ${SSL_PHPFPM_CRT_FILE}
php artisan env:set PYTHON_CONTAINER_PORT ${PYTHON_CONTAINER_PORT}

php artisan env:set MYSQL_HOST ${MYSQL_HOST}
php artisan env:set MYSQL_PORT ${MYSQL_PORT}
php artisan env:set MYSQL_ROOT_USERNAME ${MYSQL_ROOT_USERNAME}
php artisan env:set MYSQL_ROOT_PASSWORD ${MYSQL_ROOT_PASSWORD}

php artisan env:set MYSQL_DATABASE ${MYSQL_DATABASE}
php artisan env:set MYSQL_USERNAME ${MYSQL_USERNAME}
php artisan env:set MYSQL_PASSWORD ${MYSQL_PASSWORD}
echo "Настройки скопированы из docker's .env в laravel's .env."

echo 'Генерируем ключ приложения Laravel...'
php artisan key:generate
php artisan config:cache
php artisan key:generate
php artisan config:cache
echo 'Ключ приложения Laravel сгенерирован.'

echo 'Выполняем миграции БД...'
php artisan migrate
echo 'Миграции БД выполнены.'

echo 'Устанавливаем webhook...'
curl -s -F "url=https://${NGINX_SERVER_NAME}/api/telegram/webhook" -F "certificate=@${SSL_PHPFPM_CRT_FILE}" https://api.telegram.org/bot${TELEGRAMBOT_BOT_TOKEN}/setWebhook | json_pp
echo 'Webhook установлен.'

echo ''
echo 'Установка выполнена.'

# Telegram-бот для идентификации автомобилей по одиночным снимкам.

## Установка:
1. Установить docker и docker-compose.
```bash
clear && curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh && curl -L "https://github.com/docker/compose/releases/download/1.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && chmod +x /usr/local/bin/docker-compose
```
2. Запустить docker-контейнеры командой `make up`

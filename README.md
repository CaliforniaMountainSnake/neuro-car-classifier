# Telegram-бот для идентификации автомобилей по одиночным снимкам.

## Установка:
1. Установить docker и docker-compose.
```bash
clear && curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh && curl -L "https://github.com/docker/compose/releases/download/1.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && chmod +x /usr/local/bin/docker-compose
```
2. Склонировать git-репозиторий:
```bash
git clone https://github.com/CaliforniaMountainSnake/neuro-car-classifier.git && cd neuro-car-classifier && git config core.fileMode false && chown -R 999:999 logs/
```
3. Задать настройки в env-файле. SSL-сертификат генерируется следующей командой:
```bash
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.crt -nodes -days 3650 -subj '/CN=domain.com'
```
4. Запустить docker-контейнеры командой `make reinstall`

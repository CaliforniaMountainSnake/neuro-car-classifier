# Changelog
The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security


## [1.0.0] - 2018-05-15
### Added
- Сделал более-менее нормальный api-сервис по выдаче предсказаний класса изображения с помощью нейросети. Нейросеть загружается в память 1 раз и далее используется многократно без перезагрузки.
- В отдельном docker-контейнере запускается небольшой web-фреймворк (с вебсервером) на python, который подгружает нейросеть перед началом работы и далее готов принимать запросы на предсказания.
- Все готово для разработки самого Telegram-бота. Самая главная нейросетевая часть работает.

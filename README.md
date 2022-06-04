# Кейс классификация СИ и прогнозирование потребности в них

## Общее описание решения

Проект является цифровым решением в рамках конкурса Цифровой Прорыв. Сезон: искусственный Интеллект, соответствующиим задаче "Классификация Средств Измерения
и прогнозирование потребности в них" (https://hacks-ai.ru/hackathons/755859)

> На основе текстовых документов и базы
данных, с применением технологий искусственного интеллекта, создать комплексное ИИ-решение для извлечения данных
(характеристик СИ) из текстовых документов для формирования единой базы данных и построения на её основе аналитических решений по изменениям структуры
российского рынка СИ

## Требования к окружению для запуска продукта
Платформа: кроссплатформенное решение

Требуемое ПО: Docker (https://www.docker.com/get-started/)

## Команды для разворачивания:
```
docker-compose build
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php yii migrate --interactive=0
```

## Доступ к интерфейсу WebUI
```
http://localhost:8888/
```

## Примеры использования
- Загрузить zip архив с папкой "Разметка" с pdf файлами
- Кликнуть "извлечь сведения из файлов"
- Использовать фильтры и сортировки для просмотра результирующих таблиц

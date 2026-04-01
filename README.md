# Online Store

Современный интернет-магазин на Laravel 12 с пользовательской и административной частями, мультиязычностью, заказами, уведомлениями, отзывами и аналитикой.

## О проекте

`Online Store` — это pet/commercial-ready проект для портфолио, где реализован полный e-commerce поток:
- каталог товаров и карточка товара;
- корзина и оформление заказа;
- личный кабинет пользователя;
- административная панель для управления магазином;
- базовая аналитика продаж и активности.

Проект ориентирован на чистую структуру, понятную поддержку и адаптивный интерфейс.

## Ключевые возможности

- **Каталог и поиск**: просмотр товаров, карточка товара, поиск по каталогу.
- **Корзина и checkout**: добавление/удаление товаров, оформление заказа.
- **Заказы**: история заказов, страница деталей заказа, статусы.
- **Отзывы**: отправка отзывов пользователями и модерация в админке.
- **Уведомления**: отображение событий для пользователя.
- **Админ-панель**: управление товарами, категориями, заказами, пользователями и способами доставки.
- **Мультиязычность**: RU/UA/EN, переключатель языка в шапке (frontend + admin), локализация UI и данных.
- **Дашборд**: визуализация статистики с помощью Chart.js, фильтры по году/месяцу и корректная агрегация продаж.
- **UI/UX**: кастомные CSS-стили и адаптивная верстка без зависимости от Tailwind-классов в шаблонах.

## Последние обновления

- **Docker Desktop**: `docker-compose` (PHP-FPM, Nginx на `:8080`, MySQL на `:3307` с хоста), скрипты в `scripts/` для быстрого старта.
- **Демо-изображения**: симлинк `public/storage` → `storage/app/public`; для демо-товаров загружается по **5 фото в галерею** с [Unsplash](https://unsplash.com) (лицензия [Unsplash License](https://unsplash.com/license)); команда `php artisan products:fetch-gallery`.
- **Локализация карточек товаров**: заполнение полей `name_en` / `name_uk` и описаний для сидов; команда `php artisan products:sync-demo-locales`.
- Языковая панель (RU/UA/EN), график продаж в админ-дашборде с фильтрами по году/месяцу, кастомные CSS без Tailwind в шаблонах.

## Технологический стек

**Backend**
- PHP 8.2+
- Laravel 12
- Eloquent ORM
- Laravel Sanctum
- Spatie Laravel Permission
- srmklive/paypal

**Frontend**
- Blade
- Vanilla JS
- Vite
- Chart.js
- Custom CSS (`app.css`, `admin.css`, `auth.css`)

**Инфраструктура**
- MySQL / SQLite (для локальной разработки можно SQLite)
- Docker (PHP 8.3-FPM, Nginx, MySQL 8.4) — см. раздел ниже
- Queue / Session / Cache через стандартные драйверы Laravel

## Структура проекта

```text
app/
  Http/Controllers/      # пользовательские и админ-контроллеры
  Models/                # доменные модели
  Services/              # вспомогательная логика (напр. загрузка демо-галереи)
resources/
  views/                 # Blade-шаблоны (frontend + admin)
  css/                   # кастомные стили
routes/
  web.php               # основные маршруты
```

## Быстрый старт

1. Клонируй репозиторий:
```bash
git clone <repo_url>
cd online_store
```

2. Установи зависимости:
```bash
composer install
npm install
```

3. Настрой окружение:
```bash
cp .env.example .env
php artisan key:generate
```

4. Настрой базу данных в `.env`, затем выполни:
```bash
php artisan migrate --seed
```

5. Запусти проект:
```bash
composer run dev
```

Или отдельно:
```bash
php artisan serve
npm run dev
```

## Запуск через Docker Desktop

1. Скопируй окружение и сгенерируй ключ:
```bash
cp .env.example .env
```

2. В `.env` задай URL приложения и параметры БД для контейнера MySQL:
```env
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=online_store
DB_USERNAME=online_store
DB_PASSWORD=secret
```

3. Собери и подними контейнеры:
```bash
docker compose up -d --build
```

4. Первичная инициализация БД, симлинка `storage`, демо-локалей и галереи (удобно под Windows):
```bat
scripts\docker-init.bat
```

Или вручную из корня проекта:
```bash
docker compose exec app php artisan key:generate
docker compose exec app sh -c "if [ ! -L public/storage ]; then rm -rf public/storage && php artisan storage:link; fi"
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:ensure-product-images --force
docker compose exec app php artisan products:sync-demo-locales
docker compose exec app php artisan products:fetch-gallery --force
```

`storage:link` нужен, чтобы по URL `/storage/...` отдавались файлы из `storage/app/public`.  
`products:fetch-gallery` скачивает демо-фото с Unsplash в `storage/app/public/product_images/` (требуется доступ в интернет).

5. Открой проект в браузере:
```text
http://localhost:8080
```

Дополнительно:
- остановка: `docker compose down`
- остановка с удалением тома БД: `docker compose down -v`

### Полезные Artisan-команды (демо)

| Команда | Назначение |
|--------|------------|
| `php artisan storage:ensure-product-images` | Создать недостающие файлы-заглушки для путей `products/*.jpg` в `storage` |
| `php artisan storage:ensure-product-images --force` | То же + перезаписать демо-заглушки |
| `php artisan products:sync-demo-locales` | Заполнить EN/UK поля для демо-товаров по списку сидера |
| `php artisan products:fetch-gallery` | Скачать по 5 фото в галерею (пропуск, если галерея уже есть) |
| `php artisan products:fetch-gallery --force` | Пересоздать галерею и обновить главное фото товара |

### Docker dev-режим (Vite hot-reload)

Если нужен live-reload фронтенда в Docker Desktop:

1. Подними базовые сервисы:
```bash
docker compose up -d --build
```

2. Запусти Vite в отдельном контейнере:
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d vite
```

3. Открой проект:
```text
http://localhost:8080
```

Vite dev server слушает порт из `.env` (`VITE_PORT`, в `.env.example` — `5173`; при конфликте порта смени порт и `VITE_DEV_SERVER_URL`).

### Быстрые команды (одной командой)

**Windows (.bat)**
- `scripts\docker-up.bat` — собрать и поднять контейнеры.
- `scripts\docker-dev.bat` — поднять контейнеры + Vite hot-reload.
- `scripts\docker-init.bat` — сгенерировать ключ и выполнить `migrate --seed`.
- `scripts\docker-down.bat` — остановить контейнеры.

**Makefile (Linux/macOS/Git Bash/WSL)**
- `make up` — собрать и поднять контейнеры.
- `make dev` — поднять контейнеры + Vite hot-reload.
- `make key` — сгенерировать `APP_KEY`.
- `make migrate` / `make seed` — миграции и сиды.
- `make down` — остановить контейнеры.

## Скриншоты

Добавь сюда скриншоты для портфолио:
- Главная страница
- Карточка товара
- Корзина/Checkout
- Личный кабинет
- Админ-панель (дашборд, товары, заказы)

## Что можно улучшить дальше

- покрыть бизнес-критичные сценарии feature-тестами;
- добавить CI-пайплайн (lint + test + build);
- вынести конфигурацию ролей/прав в отдельный слой;
- продакшен-деплой (отдельный compose/образ, очереди, бэкапы БД).

## Автор

**Володимир Чернишев**
- GitHub: [github.com/Vladimir-Create-cyber](https://github.com/Vladimir-Create-cyber)
- LinkedIn: [linkedin.com/in/володимир-чернишев-0a76a834a](https://www.linkedin.com/in/%D0%B2%D0%BE%D0%BB%D0%BE%D0%B4%D0%B8%D0%BC%D0%B8%D1%80-%D1%87%D0%B5%D1%80%D0%BD%D0%B8%D1%88%D0%B5%D0%B2-0a76a834a/)

---

Если проект полезен — поставьте ⭐ в репозитории.

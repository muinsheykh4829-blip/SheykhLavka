# Быстрый деплой backend без домена (по IP)

Этот набор файлов поможет быстро поднять Laravel backend на чистом Ubuntu (22.04+) и открыть доступ по IP (HTTP). SSL/домен можно подключить позже без ломки.

## Что внутри
- `setup_server.sh` — устанавливает Nginx + PHP 8.2 + Composer, подготавливает систему.
- `nginx_dastovka.conf` — шаблон конфигурации Nginx (root на `backend/public`).
- `deploy_app.sh` — развёртывает приложение: зависимости, миграции, права, symlink storage.
- `../.env.prod.example` — пример `.env` для продакшна (по IP).

## Шаги на сервере (Ubuntu)
1. Скопируйте код в `/var/www/dastovka` (папка `backend` должна быть по пути `/var/www/dastovka/backend`).
2. Скопируйте файлы из `deploy/` на сервер и запустите:
   - `bash setup_server.sh` — установит Nginx, PHP-FPM 8.2, Composer.
   - Отредактируйте `nginx_dastovka.conf` (при необходимости) и положите в `/etc/nginx/sites-available/dastovka.conf`, создайте symlink в `sites-enabled` и перезапустите Nginx.
3. В папке `backend`:
   - Скопируйте `.env.prod.example` в `.env` и заполните значения БД/ключа и пр.
   - Запустите `bash deploy_app.sh` — установит зависимости, сгенерирует ключ, выполнит миграции, создаст storage:link, выставит права.

## Замечания
- Доступ будет по IP (например, `http://123.123.123.123`).
- Позже для домена добавьте второй server block с 80/443 и сертификат Let’s Encrypt, поменяйте `APP_URL`.
- Если используете очереди — добавьте Supervisor-конфиг для `php artisan queue:work`.

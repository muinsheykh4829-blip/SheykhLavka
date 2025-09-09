#!/usr/bin/env bash
set -e

APP_DIR=/var/www/dastovka/backend

echo "[1/6] Move to app dir: $APP_DIR"
cd "$APP_DIR"

echo "[2/6] Install PHP deps"
composer install --no-dev --prefer-dist --optimize-autoloader

if [ ! -f .env ]; then
  echo "[3/6] Copy .env from example"
  cp .env.prod.example .env
fi

echo "[4/6] App key"
php artisan key:generate --force

echo "[5/6] Migrate & cache"
php artisan migrate --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true
php artisan storage:link || true

echo "[6/6] Permissions"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache

echo "Deploy complete"

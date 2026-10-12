#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -f composer.json ] && [ ! -d vendor ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
  if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
  fi
  mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R ug+rwX storage bootstrap/cache
fi

exec "$@"

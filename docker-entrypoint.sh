#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" --skip-ssl --silent; do
  sleep 2
done
echo "MySQL is up and running!"

# Setup application if vendor directory is missing
if [ ! -d "vendor" ]; then
    echo "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader

    echo "Running init..."
    php init --env=Production --overwrite=y
fi

# In Docker, always ensure .env has correct DB_DSN (host=mysql, not 127.0.0.1)
# Env vars from docker-compose can be overridden by .env in some setups
if [ ! -f ".env" ] || [ -n "$DB_HOST" ]; then
    echo "Creating/updating .env for Docker..."
    cat > .env << EOF
DB_DSN=mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_NAME:-yii2api}
DB_USER=${DB_USER:-yii2api}
DB_PASSWORD=${DB_PASSWORD:-secret}
DB_CHARSET=utf8mb4
DEMO_USER_PASSWORD=${DEMO_USER_PASSWORD:-demo_secret_password}
APP_BASE_URL=${APP_BASE_URL:-http://localhost:8088}
EOF
fi

echo "Running migrations..."
php yii migrate --interactive=0

echo "Running seeders..."
USER_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" --skip-ssl -ss -e "SELECT COUNT(*) FROM user;" 2>/dev/null)
USER_COUNT=${USER_COUNT:-1}
if [ "$USER_COUNT" -eq "0" ]; then
    echo "Database is empty, running seed/all..."
    php yii seed/all
else
    echo "Database already seeded."
fi

echo "Starting PHP-FPM..."
exec "$@"

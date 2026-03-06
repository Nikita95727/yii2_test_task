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

if [ ! -f ".env" ]; then
    echo "Creating .env from example..."
    cp .env.example .env
    # We will override these settings using environment variables from docker-compose
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

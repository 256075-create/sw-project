#!/bin/sh
set -e

echo "==> UMS Backend Starting..."

# Wait for MySQL to be ready
echo "==> Waiting for MySQL..."
while ! php -r "try { new PDO('mysql:host=${DB_HOST};port=3306', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'ok'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done
echo "==> MySQL is ready!"

# Install dependencies if vendor is missing
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "==> Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

# Create storage directories
echo "==> Setting up storage..."
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear and cache config
echo "==> Caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> UMS Backend Ready! Starting PHP-FPM..."
exec php-fpm

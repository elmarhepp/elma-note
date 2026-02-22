#!/bin/sh
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "→ vendor/ fehlt, führe composer install aus..."
    composer install --no-interaction --no-progress --quiet
fi

if [ ! -f /var/www/html/.env ]; then
    echo "→ .env fehlt, kopiere .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# APP_KEY generieren falls leer
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "→ APP_KEY fehlt, generiere..."
    php artisan key:generate --no-interaction
fi

# DB_HOST auf Docker-internen Service-Namen setzen
# (php artisan serve übergibt $_ENV an Subprozesse, nicht getenv() –
#  deshalb muss der Wert direkt in .env stehen)
sed -i 's|^DB_HOST=.*|DB_HOST=mysql|' /var/www/html/.env

exec "$@"

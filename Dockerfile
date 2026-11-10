
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache

RUN php -i | grep -i "extended SSL"

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]

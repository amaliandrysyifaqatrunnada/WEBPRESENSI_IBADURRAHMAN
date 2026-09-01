FROM dunglas/frankenphp:php8.3-bookworm

WORKDIR /app

RUN install-php-extensions `
    pdo_mysql `
    mbstring `
    xml `
    curl `
    zip `
    bcmath `
    intl `
    gd `
    exif `
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /app

RUN composer install `
    --no-dev `
    --optimize-autoloader `
    --no-interaction `
    --prefer-dist

RUN mkdir -p `
    storage/framework/cache `
    storage/framework/sessions `
    storage/framework/views `
    storage/logs `
    bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

RUN php artisan config:clear

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh `
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV SERVER_NAME=:80
ENV MYSQL_ATTR_SSL_CA=/app/ca.pem

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=80"]

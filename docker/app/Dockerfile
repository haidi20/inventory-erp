FROM serversideup/php:8.3-fpm-alpine AS base

USER root

RUN apk add --no-cache mysql-client bash git \
    && install-php-extensions bcmath gd intl zip \
    && adduser -D -u 1000 www-data 2>/dev/null || true

COPY docker/app/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
WORKDIR /var/www/html

COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM base AS dev

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]

FROM base AS prod

COPY src/ /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install \
    --no-interaction \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
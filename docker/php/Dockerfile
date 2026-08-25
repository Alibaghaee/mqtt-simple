FROM php:8.4-cli-bookworm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pcntl sockets \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM base AS dependencies
COPY composer.json ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

FROM dependencies AS development
COPY . .
RUN composer dump-autoload --optimize
CMD ["php", "bin/subscriber.php"]

FROM dependencies AS production
COPY . .
RUN composer dump-autoload --classmap-authoritative --no-dev
USER www-data
CMD ["php", "bin/subscriber.php"]

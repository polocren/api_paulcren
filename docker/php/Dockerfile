FROM php:8.4-fpm-alpine

# Paquets système nécessaires au projet Laravel.
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo_mysql bcmath intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# On copie d'abord les fichiers Composer pour mieux utiliser le cache Docker.
COPY composer.json composer.lock ./

# On installe les dépendances avant de copier tout le projet pour profiter du cache Docker.
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .

# On génère le manifest Laravel des packages sans dépendre du .env local de la machine.
RUN cp .env.docker.example .env \
    && php artisan package:discover --ansi \
    && rm .env \
    # On prépare les dossiers utilisés par Laravel au runtime.
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

# PHP-FPM écoute sur le port 9000 dans le réseau Docker.
CMD ["php-fpm"]

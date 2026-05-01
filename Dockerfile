# ─── Stage 1: Build frontend assets ─────────────────────────────────────────
FROM node:22-slim AS frontend

WORKDIR /app

COPY package*.json bun.lock* ./
RUN npm ci --prefer-offline

COPY resources/ resources/
COPY public/ public/
COPY vite.config.js ./
COPY .env.docker .env

RUN npm run build

# ─── Stage 2: Install PHP dependencies ───────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ─── Stage 3: Final production image ─────────────────────────────────────────
FROM php:8.3-fpm-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip supervisor \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP config for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=55M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/memory.ini

WORKDIR /var/www/html

# Copy source code
COPY --from=vendor /app /var/www/html

# Copy built frontend assets
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy entrypoint & supervisor config
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod +x /usr/local/bin/entrypoint.sh

# Fix permissions
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

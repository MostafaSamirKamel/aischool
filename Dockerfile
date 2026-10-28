# syntax=docker/dockerfile:1
# ==========================================
# Stage 1: Install PHP Dependencies (Composer)
# ==========================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# ==========================================
# Stage 2: Build Frontend Assets (Vite)
# ==========================================
FROM node:22-bookworm-slim AS frontend-builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/
# Copy vendor from composer-builder so Filament CSS references can be resolved by Vite
COPY --from=composer-builder /app/vendor ./vendor

RUN npm run build

# ==========================================
# Stage 3: Production Runtime (PHP-FPM + Nginx)
# ==========================================
FROM php:8.3-fpm-bookworm AS runtime

LABEL maintainer="AI School DevOps" \
      description="Production image for AI School School Management System on Railway"

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    gettext-base \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install official PHP extension installer
RUN curl -sSLf -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions

# Install required PHP extensions
RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

# Setup Nginx template directory
RUN mkdir -p /etc/nginx/templates /etc/nginx/conf.d

# Copy server and PHP configurations
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy production PHP vendor from composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy compiled Vite assets from node stage
COPY --from=frontend-builder /app/public/build ./public/build

# Copy Student PWA Frontend into public/student-portal
COPY AI_School_Student_Frontend/ ./public/student-portal/

# Create required directories and set proper ownership
RUN mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Default port expected by Railway or fallback to 8080
ENV PORT=8080 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

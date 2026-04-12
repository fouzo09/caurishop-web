# ═══════════════════════════════════════════════════════════════
# Stage 1 — Node : build Vite assets
# ═══════════════════════════════════════════════════════════════
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts

COPY resources/ resources/
COPY vite.config.* ./
COPY tailwind.config.* ./

RUN npm run build

# ═══════════════════════════════════════════════════════════════
# Stage 2 — Composer : install PHP dependencies
# ═══════════════════════════════════════════════════════════════
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ═══════════════════════════════════════════════════════════════
# Stage 3 — Production image
# ═══════════════════════════════════════════════════════════════
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="CauriShop" \
      org.opencontainers.image.title="CauriShop Web" \
      org.opencontainers.image.description="Laravel 12 application"

# ── System dependencies ────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    bash \
    shadow

# ── PHP extensions ─────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    bcmath \
    opcache \
    pcntl

# ── Copy configs ───────────────────────────────────────────────
COPY docker/php/php.ini         /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/php-fpm.conf    /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/default.conf  /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ── Application ────────────────────────────────────────────────
WORKDIR /var/www/html

COPY --from=vendor /app/vendor    ./vendor
COPY --from=vendor /app           .
COPY --from=assets /app/public/build ./public/build

# ── Permissions ────────────────────────────────────────────────
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Nginx needs to read public/
RUN chown -R www-data:www-data public

# Remove default nginx config
RUN rm -f /etc/nginx/conf.d/default.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

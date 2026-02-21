FROM composer:2.7 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM node:20-bookworm-slim AS frontend

WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

FROM php:8.3-fpm-bookworm AS app

RUN apt-get update && apt-get install -y \
    ffmpeg \
    nginx \
    supervisor \
    gettext-base \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpq-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    pcntl \
    pdo_pgsql \
    pdo_mysql \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisor/web.conf /etc/supervisor/conf.d/web.conf
COPY docker/scripts/start-web.sh /usr/local/bin/start-web
COPY docker/scripts/start-service.sh /usr/local/bin/start-service

RUN chmod +x /usr/local/bin/start-web /usr/local/bin/start-service \
    && mkdir -p /run/php /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
CMD ["/usr/local/bin/start-service"]

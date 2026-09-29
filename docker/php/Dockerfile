FROM node:20-alpine AS frontend-builder

WORKDIR /var/www

COPY backend/package*.json ./
RUN npm ci || npm install

COPY backend/resources/js ./resources/js
COPY backend/resources/css ./resources/css
COPY backend/resources/views ./resources/views
COPY backend/vite.config.ts ./
COPY backend/tsconfig*.json ./
COPY backend/postcss.config.js ./
COPY backend/tailwind.config.js ./

RUN npm run build

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    supervisor \
    nginx \
    curl \
    && docker-php-ext-install pdo_pgsql zip opcache bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY backend/composer.json ./
COPY backend/composer.lock ./ || true
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || composer update --no-dev --no-scripts --no-autoloader --prefer-dist

COPY backend/ ./
RUN composer dump-autoload --optimize --no-dev

COPY --from=frontend-builder /var/www/public/build ./public/build

RUN mkdir -p /var/www/storage/framework/{views,sessions,cache} \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY docker/supervisor/horizon.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && supervisord -c /etc/supervisor/conf.d/supervisord.conf && nginx -g 'daemon off;'"]

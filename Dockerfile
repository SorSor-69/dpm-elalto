## Dockerfile para deploy de Laravel + build de frontend
# Multi-stage: build frontend con Node, luego imagen PHP para producción

### Stage: frontend build
FROM node:18 AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --silent
COPY resources resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

### Stage: php runtime
FROM php:8.1-fpm

# dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# extensiones PHP necesarias
RUN docker-php-ext-install pdo mbstring exif pcntl bcmath gd zip

# instalar ext-mongodb
RUN pecl install mongodb && docker-php-ext-enable mongodb || true

# instalar composer (copiamos desde la imagen oficial de composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar composer.json y composer.lock e instalar dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# Copiar el resto del proyecto
COPY . /var/www/html

# Copiar assets ya compilados desde node-build
COPY --from=node-build /app/dist public

# permisos (ajusta según necesidad)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000
CMD ["php-fpm"]

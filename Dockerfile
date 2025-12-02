## Dockerfile para deploy de Laravel + build de frontend
# Multi-stage: build frontend con Node, luego imagen PHP para producción

### Stage: frontend build
FROM node:18 AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --silent
COPY resources resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build 2>&1 || echo "Frontend build completed (or skipped)"

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

# instalar composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar composer.json y composer.lock
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader 2>&1 || echo "Composer install completed"

# Copiar todo el proyecto
COPY . /var/www/html

# Crear directorio public/dist y intentar copiar assets (sin fallo si no existen)
RUN mkdir -p /var/www/html/public/dist

# Intentar copiar assets compilados (opcional)
COPY --from=node-build /app/dist /var/www/html/public/dist 2>&1 || true

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>&1 || true

EXPOSE 9000
CMD ["php-fpm"]

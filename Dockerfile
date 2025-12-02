## Dockerfile simplificado para Laravel (sin build frontend)
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
RUN docker-php-ext-install pdo mbstring exif pcntl bcmath gd zip || true

# instalar ext-mongodb (opcional, no fallar si no está disponible)
RUN pecl install mongodb && docker-php-ext-enable mongodb || true

# instalar composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . /var/www/html

# Instalar dependencias de PHP
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# Permisos
RUN mkdir -p storage framework bootstrap/cache || true
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 8080
# Usar servidor embebido de PHP para exponer HTTP en Railway
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]

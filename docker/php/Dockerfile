FROM php:8.2-fpm

# Zainstaluj zależności
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Zainstaluj rozszerzenia PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Pobierz i zainstaluj Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Domyślna komenda
CMD bash -c "if [ -f composer.json ]; then composer install --no-interaction --optimize-autoloader; fi && php-fpm"
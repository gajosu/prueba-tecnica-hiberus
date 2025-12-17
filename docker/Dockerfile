FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && docker-php-ext-enable pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Exponer puerto
EXPOSE 8000

# Comando por defecto (servidor de desarrollo de PHP)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]


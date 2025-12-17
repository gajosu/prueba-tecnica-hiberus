FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && docker-php-ext-enable pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy configuration files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port
EXPOSE 8000

# Default command (PHP development server)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]


FROM php:8.4-cli

# System deps
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libpq-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Ensure permissions and create symbolic link
RUN mkdir -p storage/app/public/profile-photos \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# Set environment variables for production
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

CMD ["bash", "-c", "rm -rf public/storage && php artisan storage:link && php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]

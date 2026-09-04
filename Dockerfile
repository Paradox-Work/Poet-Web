FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    nginx \
    supervisor \
    sqlite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create .env file if it doesn't exist
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Create SQLite database file
RUN touch database/database.sqlite

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend
RUN npm install
RUN npm run build

# Set permissions
RUN chmod -R 775 storage bootstrap/cache database

# Generate application key
RUN php artisan key:generate --force

# Run migrations
RUN php artisan migrate --force

# Expose port
EXPOSE 10000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=10000
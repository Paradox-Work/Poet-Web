FROM php:8.4-fpm-alpine

# Install system dependencies (include sqlite and sqlite-dev)
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
    sqlite \
    sqlite-dev

# Install PHP extensions (include pdo_sqlite)
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create .env file with SQLite configuration
RUN echo "APP_NAME=Laravel" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=https://your-app-url.onrender.com" >> .env && \
    echo "APP_LOCALE=en" >> .env && \
    echo "APP_FALLBACK_LOCALE=en" >> .env && \
    echo "APP_FAKER_LOCALE=en_US" >> .env && \
    echo "APP_MAINTENANCE_DRIVER=file" >> .env && \
    echo "BCRYPT_ROUNDS=12" >> .env && \
    echo "LOG_CHANNEL=stack" >> .env && \
    echo "LOG_STACK=single" >> .env && \
    echo "LOG_DEPRECATIONS_CHANNEL=null" >> .env && \
    echo "LOG_LEVEL=debug" >> .env && \
    echo "DB_CONNECTION=sqlite" >> .env && \
    echo "DB_DATABASE=/var/www/html/database/database.sqlite" >> .env && \
    echo "SESSION_DRIVER=database" >> .env && \
    echo "SESSION_LIFETIME=120" >> .env && \
    echo "SESSION_ENCRYPT=false" >> .env && \
    echo "SESSION_PATH=/" >> .env && \
    echo "SESSION_DOMAIN=null" >> .env && \
    echo "BROADCAST_CONNECTION=log" >> .env && \
    echo "FILESYSTEM_DISK=local" >> .env && \
    echo "QUEUE_CONNECTION=database" >> .env && \
    echo "CACHE_STORE=database" >> .env && \
    echo "REDIS_CLIENT=phpredis" >> .env && \
    echo "REDIS_HOST=redis" >> .env && \
    echo "REDIS_PASSWORD=null" >> .env && \
    echo "REDIS_PORT=6379" >> .env && \
    echo "MAIL_MAILER=smtp" >> .env && \
    echo "MAIL_SCHEME=null" >> .env && \
    echo "MAIL_HOST=mailpit" >> .env && \
    echo "MAIL_PORT=1025" >> .env && \
    echo "MAIL_USERNAME=null" >> .env && \
    echo "MAIL_PASSWORD=null" >> .env && \
    echo "MAIL_FROM_ADDRESS=hello@example.com" >> .env && \
    echo "MAIL_FROM_NAME=Laravel" >> .env && \
    echo "VITE_APP_NAME=Laravel" >> .env

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
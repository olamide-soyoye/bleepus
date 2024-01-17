# Use an official PHP image as a parent image
FROM php:8.0-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql exif pcntl bcmath gd zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup web user
RUN useradd --groups www-data --uid 1000 --home /home/laravel laravel

# Setup composer & site folders
RUN mkdir --parents /home/laravel/.composer && \
    mkdir /home/laravel/site && \
    chown --recursive laravel:laravel /home/laravel

# Set working directory
WORKDIR /var/www

# Ensure our user is not root
USER laravel

# Copy Laravel application files from the src folder
COPY src/ .

# Run Composer install
RUN composer install --no-dev

# Run other Artisan commands
RUN php artisan migrate:fresh --force && \
    php artisan db:seed --class=UserTypeSeeder --force && \
    php artisan optimize:clear && \
    php artisan l5-swagger:generate && \
    php artisan storage:link

# Set ownership of the Laravel application files to the laravel user
RUN chown -R laravel:laravel /var/www

RUN chown -R laravel:laravel /var/www/storage

RUN chmod -R 775 /var/www/storage

# Copy Virtual host configuration to sites avalible
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache modules and configure MPM
RUN a2dismod mpm_event && a2enmod mpm_prefork && a2enmod rewrite

# Set Apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose port 80
EXPOSE 80

# The default CMD for the official PHP Apache image is to start Apache
CMD ["apache2-foreground"]

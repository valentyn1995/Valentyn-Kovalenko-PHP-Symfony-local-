FROM php:8.2-fpm

# Install necessary dependencies
# Install the development files for the libzip library
# Install the zip utility
# Install the unzip utility
RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip unzip \
      git && \
      docker-php-ext-install pdo pdo_pgsql pgsql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Download Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory to /app/
WORKDIR /app/

# Copy the contents of the current directory to the /app/ directory in the container
COPY . /app/

# Install the project dependencies using Composer
RUN composer install

# Set permissions
RUN chmod -R 777 /app

# Install the Xdebug extension
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug


COPY ./docker/xdebug/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  
COPY ./docker/xdebug/conf.d/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini  

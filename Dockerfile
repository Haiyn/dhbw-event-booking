# Define base image
FROM php:7.1-apache

# Update
RUN apt-get update

# Enable rewrite
RUN a2enmod rewrite
RUN service apache2 restart

# Enable debugging
RUN pecl install xdebug-2.5.5 && docker-php-ext-enable xdebug
RUN echo 'zend_extension="/usr/local/lib/php/extensions/no-debug-non-zts-20151012/xdebug.so"' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_port=9000' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_connect_back=1' >> /usr/local/etc/php/php.ini

# Install Postgre PDO
RUN apt-get install -y libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copy source code and optional additional configs
COPY /src /var/www/html

# Create upload directory
RUN mkdir /data
RUN chmod 777 /data
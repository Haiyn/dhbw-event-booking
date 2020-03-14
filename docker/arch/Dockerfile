# Define base image
FROM php:7.4-apache

# Update
RUN apt-get update

# Install Postgre PDO
RUN apt-get install -y libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copy source code and optional additional configs
RUN cd ../
COPY /src /srv/httpd
RUN cd ./docker

# Create upload directory
RUN mkdir /data
RUN chmod 777 /data
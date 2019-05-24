FROM php:7.3.1-fpm

# Wait dynamic arguments:
ARG CONTAINER_TIMEZONE_PHPFPM
ARG SSL_LOCAL_CRT_FILE
ARG SSL_PHPFPM_CRT_FILE

# Install base soft:
RUN apt-get update && apt-get install -y \
    wget \
    curl \
    zip \
    nano \
    htop \
    git \
    libzip-dev

# Install php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer:
RUN curl --silent --show-error https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Set timezone:
RUN ln -snf /usr/share/zoneinfo/$CONTAINER_TIMEZONE_PHPFPM /etc/localtime && echo $CONTAINER_TIMEZONE_PHPFPM > /etc/timezone

# Copy SSL certificate file:
ADD $SSL_LOCAL_CRT_FILE $SSL_PHPFPM_CRT_FILE

# Set workdir to PHP:
WORKDIR /var/www

# Launch the container:
# From documentation: The main purpose of a CMD is to provide defaults for an executing container.
# These defaults can include an executable, or they can omit the executable,
# in which case you must specify an ENTRYPOINT instruction as well.
CMD ["php-fpm"]

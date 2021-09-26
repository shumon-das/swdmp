FROM php:fpm-alpine3.13

RUN apk add --no-cache tzdata
ENV TZ Europe/London

ENV COMPOSER_HOME=/

RUN apk update && apk add  zip libzip-dev icu-dev

# Install packages and remove default server definition
RUN apk --no-cache add \
  curl \
  supervisor

RUN apk --no-cache add
RUN apk --no-cache add gnupg haveged tini

RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install exif && \
    docker-php-ext-install zip && \
    rm -rf /usr/src/php*

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php8 /usr/bin/php

# Create Composer directory (cache and auth files) & Get Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY . /var/www

# Configure PHP-FPM
COPY docker/conf/fpm-pool.conf /etc/php8/php-fpm.d/www.conf
COPY docker/conf/php.ini /etc/php8/conf.d/custom.ini

# Configure supervisord
RUN mkdir -p /etc/supervisor/conf.d

COPY docker/conf/supervisord.conf /etc/supervisor
COPY docker/conf/supervisord-programs.conf /etc/supervisor/conf.d/app.conf

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

WORKDIR /var/www

RUN composer install --no-progress --profile --prefer-dist
EXPOSE 9009
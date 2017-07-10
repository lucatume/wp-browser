FROM php:7.0-fpm

# Install required PHP extensions
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libjpeg-dev \
        libpng12-dev \
        libpng12-0 \
        libjpeg62-turbo; \
    \
    docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
    docker-php-ext-install gd mysqli opcache

# Install and enable Xdebug and Opcache
RUN pecl install xdebug-2.5.0 \
    && docker-php-ext-enable opcache xdebug

# Memcached
RUN buildDeps=" \
            zlib1g-dev \
            libjpeg-dev \
    " \
    && rm -r /var/lib/apt/lists/* \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $buildDeps

# set the PHP user, (www-data), to the UID 1000 to allow it to rwx on the folders
RUN usermod -u 1000 www-data

COPY entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint

# Configure extensions
COPY conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug-conf.ini
COPY conf/opcache.ini /usr/local/etc/php/conf.d/opcache-conf.ini

ENTRYPOINT ["entrypoint"]

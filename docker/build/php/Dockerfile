FROM php:7.0-fpm

# Install required PHP extensions
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libjpeg-dev \
        libpng12-dev; \
    \
    docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
    docker-php-ext-install gd mysqli opcache

# Install and enable Xdebug and Opcache
RUN pecl install xdebug-2.5.0 \
    && docker-php-ext-enable opcache xdebug

# Configure opcache
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configure Xdebug
RUN { \
        echo 'xdebug.remote_enable=1'; \
        echo 'xdebug.remote_connect_back=1'; \
        echo 'xdebug.remote_autostart=1'; \
        echo 'xdebug.remote_port=9001'; \
        echo 'xdebug.scream=1'; \
        echo 'xdebug.cli_color=1'; \
        echo 'xdebug.show_local_vars=1'; \
    } > /usr/local/etc/php/conf.d/xdebug.ini

# Memcached
RUN buildDeps=" \
            zlib1g-dev \
            libjpeg-dev \
            libpng12-dev \
    " \
    && rm -r /var/lib/apt/lists/* \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $buildDeps

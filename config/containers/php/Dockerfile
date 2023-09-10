ARG PHP_VERSION=8.0
ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} AS composer

FROM wordpress:6.2.1-php${PHP_VERSION}-apache

ARG USER_UID=33
ARG USER_GID=33
ARG USER_NAME=www-data
ARG TARGET=wordpress

# Install wp-cli
ADD https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp
RUN chmod a+rx /usr/local/bin/wp

# Install required extensions, remove the ini file that would enable uopz for Apache: it will be loaded on the
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod a+x /usr/local/bin/install-php-extensions && \
    chmod -R a+rwx /usr/local/etc/php/conf.d && \
    install-php-extensions gd xdebug pdo pdo_mysql mysqli zip pcntl sockets

# If the target is `codeception`, then install the uopz extension.
RUN if [ "${TARGET}" = "codeception" ]; then install-php-extensions uopz; fi

# Install required utils
RUN apt-get update &&  \
    apt-get install -y default-mysql-client curl git zip unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    rm /usr/local/bin/install-php-extensions

# Set up PHP defaults
COPY php.ini /usr/local/etc/php/conf.d/999-php-custom.ini
# Make the PHP configuration world-accessible.

# Copy over Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY bashrc_scripts.sh /etc/bashrc_scripts.sh
RUN chmod a+x /etc/bashrc_scripts.sh && echo ". /etc/bashrc_scripts.sh" >> /etc/bash.bashrc
COPY xdebug-on.sh /usr/local/bin/xdebug-on
COPY xdebug-off.sh /usr/local/bin/xdebug-off
RUN chmod a+x /usr/local/bin/xdebug-on && chmod a+x /usr/local/bin/xdebug-off

# Ensure the gropup and user exist.
RUN getent group ${USER_GID} || groupadd -g ${USER_GID} ${USER_NAME}
RUN getent passwd ${USER_UID} || useradd -u ${USER_UID} -g ${USER_GID} -m -s /bin/bash ${USER_NAME}

# Create a Chrome mock where expected.
RUN if [ "${TARGET}" = "codeception" ]; then \
    mkdir -p /usr/bin && \
    echo '#!/bin/bash' > /usr/bin/google-chrome && \
    echo 'echo 'Google Chrome 116.0.5845.96'' >> /usr/bin/google-chrome && \
    chmod a+x /usr/bin/google-chrome; \
    fi

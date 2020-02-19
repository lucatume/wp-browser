ARG PHP_VERSION=7.2

FROM php:${PHP_VERSION}-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
 	apt-get install -y --no-install-recommends curl git zip unzip && \
    rm -rf /var/lib/apt/lists/*

RUN composer global require hirak/prestissimo

# Neat helper to install the required PHP extensions.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

# Install required PHP extensions.
RUN install-php-extensions gd zip

WORKDIR /project

ENTRYPOINT ["composer"]

FROM wordpress:latest

LABEL maintainer="Luca Tumedei <luca@theaveragedev.com>"

# Install required PHP extensions.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug

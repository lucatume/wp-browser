FROM wordpress:latest

# Install required PHP extensions.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug

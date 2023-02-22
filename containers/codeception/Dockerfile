ARG PHP_VERSION=5.6

FROM wp-browser-wordpress:php${PHP_VERSION:-5.6}-apache
RUN install-php-extensions uopz
COPY uopz.ini /usr/local/etc/php/conf.d/999-uopz.ini
CMD tail -f /dev/null

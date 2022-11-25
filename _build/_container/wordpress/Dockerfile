ARG WORDPRESS_BASE_IMAGE=wordpress/apache

FROM ${WORDPRESS_BASE_IMAGE}
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod a+x /usr/local/bin/install-php-extensions && install-php-extensions xdebug
COPY xdebug-on.sh /usr/local/bin/xdebug-on
COPY xdebug-off.sh /usr/local/bin/xdebug-off
RUN chmod a+x /usr/local/bin/xdebug-on && \
    chmod a+x /usr/local/bin/xdebug-off && \
    xdebug-off \
    echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    chmod -R a+rwx /usr/local/etc/php/conf.d

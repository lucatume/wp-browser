FROM php:5.6 as builder

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=finalgene/box-builder:latest /usr/local/bin/box /usr/local/bin/box

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer

RUN set -xe \
  && echo "phar.readonly=Off" > ${PHP_INI_DIR}/conf.d/phar.ini \
  && echo "date.timezone=${PHP_TIMEZONE:-UTC}" > ${PHP_INI_DIR}/conf.d/date_timezone.ini \
  && curl -fssL -o parallel-lint.tar.gz $(curl -s https://api.github.com/repos/JakubOnderka/PHP-Parallel-Lint/tags | grep "tarball_url" | cut -d "\"" -f 4 | head -n 1) \
  && tar xzvf parallel-lint.tar.gz --directory /tmp \
  && cd /tmp/$(ls /tmp | grep "PHP-Parallel-Lint") \
  && composer install --no-dev --prefer-dist --no-progress --no-interaction --no-suggest --optimize-autoloader \
  && box build \
  && mv parallel-lint.phar /usr/local/bin/parallel-lint \
  && php -v \
  && box --version \
  && parallel-lint --version

FROM php:5.6
LABEL maintainer="jens.kohl@milchundzucker.de"

COPY --from=builder /usr/local/bin/parallel-lint /usr/local/bin/parallel-lint

VOLUME [ "/app" ]
ENTRYPOINT [ "/usr/local/bin/parallel-lint"]
CMD [ "--version" ]
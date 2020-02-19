ARG CODECEPTION_CONTAINER_VERSION="3.1.0"

FROM codeception/codeception:${CODECEPTION_CONTAINER_VERSION}

LABEL maintainer="Luca Tumedei <luca@theaveragedev.com>"

# Install required PHP extensions.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions pdo_mysql gd mysqli

# Set up the `wait-then` command.
COPY wait-then.sh /usr/local/bin/wait-then
RUN chmod +x /usr/local/bin/wait-then

ENTRYPOINT ["wait-then", "codecept"]

ARG PHP_VERSION=8.0

FROM composer:2 AS composer

FROM php:${PHP_VERSION}

ARG USER_UNAME=dev
ARG USER_UID=1000
ARG USER_GID=1000
ARG CONTAINER_NAME=wp-browser_php@${PHP_VERSION}

ADD https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp
RUN chmod a+rx /usr/local/bin/wp
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod a+x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd xdebug pdo pdo_mysql mysqli zip uopz pcntl sockets && \
    apt-get update && apt-get install -y \
    default-mysql-client curl git zip unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY php.ini /usr/local/etc/php/conf.d/docker-php-defaults.ini
COPY docker-php-ext-uopz.ini /usr/local/etc/php/conf.d/docker-php-ext-uopz.ini
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN chmod a+x /usr/local/bin/composer
RUN [ $(getent group $USER_GID) ] || addgroup --gid $USER_GID $USER_UNAME && \
    [ $(id -u ${USER_UID}) ] || adduser --uid $USER_UID --gid $USER_GID --home /home/$USER_UNAME --shell /bin/sh --disabled-password --gecos "" $USER_UNAME && \
    mkdir -p /home/$USER_UNAME && \
    echo "PS1=\"[\\u@$CONTAINER_NAME] \\w\\n ~ \"" >> /home/$USER_UNAME/.bashrc && \
    echo "PS1=\"[\\u@$CONTAINER_NAME] \\w\\n ~ \"" >> /root/.bashrc && \
    echo "source /home/$USER_UNAME/bashrc_scripts.sh" >> /home/$USER_UNAME/.bashrc && \
    echo "source /home/$USER_UNAME/bashrc_scripts.sh" >> /root/.bashrc && \
    curl -SsL https://github.com/boxboat/fixuid/releases/download/v0.5.1/fixuid-0.5.1-linux-amd64.tar.gz | tar -C /usr/local/bin -xzf - && \
    chown root:root /usr/local/bin/fixuid && \
    chmod 4755 /usr/local/bin/fixuid && \
    mkdir -p /etc/fixuid
COPY fixuid.yml /etc/fixuid/config.yml
COPY bashrc_scripts.sh /home/$USER_UNAME/bashrc_scripts.sh
RUN chmod -R a+rwx /usr/local/etc/php/conf.d
RUN sed -i '/^zend_extension/ s/zend_extension/;zend_extension/g' "/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod a+x /usr/local/bin/entrypoint.sh
ENTRYPOINT /usr/local/bin/entrypoint.sh
USER $USER_UNAME:$USER_UNAME

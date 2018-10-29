FROM wordpress:php{{php_version}}-apache

# Install our local dependencies
RUN apt-get update \
	&& apt-get install git curl wget mysql-client zip unzip vim gnupg2 less sendmail golang-go gettext -y

# Cleanup the sources
RUN apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*

# Install XDebug; the version is specified as newer versions will run on PHP 7.0+ only.
RUN pecl install xdebug{{xdebug_version}}

# Install mhsendmail to send mails to/via Mailhog
RUN mkdir -p /temp/gocode \
	&& export GOPATH=/temp/gocode \
	&& go get github.com/mailhog/mhsendmail \
	&& cp /temp/gocode/bin/mhsendmail /usr/local/bin/mhsendmail

# Install the mysql extensions for Codeception
RUN docker-php-ext-install {{php_extensions}}

# Make sure we fetch all repositories over SSH
RUN	git config --global url."https://github.com".insteadOf git://github.com \
	&& git config --global url."https://github.com/".insteadOf git@github.com:

# install wp-cli in the container
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp \
	&& chmod +x /usr/local/bin/wp

WORKDIR /var/www/html

# copy over WP-CLI configuration file template
COPY wp-cli.yml /temp/wp-cli.yml.tpl

# install Composer in the container
COPY composer-install.sh /temp/composer-install.sh
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet --install-dir=/usr/local/bin \
	&& mv /usr/local/bin/composer.phar /usr/local/bin/composer \
	&& composer --version \
	&& chown www-data:www-data /usr/local/bin/composer \
	&& composer global require hirak/prestissimo

# copy over the help pages
COPY stack /temp/stack

# copy over our own version of the entrypoint
COPY entrypoint.sh /usr/local/bin/

# Since the plugins, and themes, folder will be overridden by the docker-compose volumes
# we store the plugins and themes we need in /temp/plugins and /temp/themes folder.
# The  entrpoint script will move them in place afte the WordPress container started.
# Copy third-party plugins in the container
RUN mkdir -p /temp/plugins
COPY plugins /temp/plugins
# Copy third-party themes in the container
RUN mkdir -p /temp/themes
COPY themes /temp/themes

WORKDIR /project

ENTRYPOINT entrypoint.sh

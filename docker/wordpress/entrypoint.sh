#!/bin/bash

# Setup some default values
COMPOSER_UPDATE=${COMPOSER_UPDATE:-1}

copy_required_plugins(){
	if [ "$(ls -A /temp/plugins)" ]; then
		for plugin in /temp/plugins/*/; do
			echo "Copying plugin $plugin to plugins folder..."
			cp -r $plugin /var/www/html/wp-content/plugins
		done

		echo ""
		echo "Final plugins folder contents:"
		echo "=============================="
		ls /var/www/html/wp-content/plugins
		echo ""
	else
		echo "No required plugins found."
	fi
}

copy_required_themes(){
	if [ "$(ls -A /temp/themes)" ]; then
		for theme in /temp/themes/*/; do
			echo "Copying theme $theme to themes folder..."
			cp -r $theme /var/www/html/wp-content/themes
		done

		echo ""
		echo "Final themes folder contents:"
		echo "=============================="
		ls /var/www/html/wp-content/themes
		echo ""
	else
		echo "No required themes found."
	fi
}

config_wp_cli(){
    cat /temp/wp-cli.yml.tpl | envsubst > /var/www/html/wp-cli.yml
    echo "wp-cli.yml config file contents:"
    echo "================================"
    cat /var/www/html/wp-cli.yml
}

update_composer_dependencies(){
	# Update composer dependencies for the project
	(cd /project; composer update)
}

setup_path(){
	# Allow commands in the vendor/bin folder to be execute from the project root
	echo "export PATH=vendor/bin:$PATH" >> ~/.bashrc
}

setup_xdebug(){
	if [ -z "$1" ]; then
		echo "XDebug disabled."
	else
		echo "Enabling XDebug extension with remote host $1"

		echo "[XDebug]
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=$1
xdebug.remote_port=9001" > /usr/local/etc/php/conf.d/xdebug.ini
	fi
}

create_utility_pages(){
	cat /temp/stack/welcome.html.tpl | envsubst > /temp/stack/welcome.html
	mkdir -p /var/www/html/stack
	cp -r /temp/stack /var/www/html
}

wait_for_db(){
    tries=0
    at_most_tries=10

    echo "Checking if db is online..."

	while ! mysqladmin ping -hdb --silent; do

        if [ ${tries} -ge ${at_most_tries} ]; then
            exit 1
        fi

		echo "Db not ready yet..."

        ((tries++))

		sleep 1
	done

    echo "Db online!"
}

install_wordrpess(){
    echo "/var/www/hmtl contents"
    echo "======================"
	ls /var/www/html

	cd /var/www/html

    if [ ! -f ./wp-load.php ]; then
        echo "WordPress files not found in $(pwd): re-downloading..."
        wp core download --allow-root
    fi

	wp config create \
        --allow-root \
	    --dbname=wordpress \
	    --dbuser=root \
	    --dbpass=root \
	    --dbhost=db \
	    --dbprefix=wp_ \
	    --force \
	    --skip-check \
	    --extra-php <<PHP
/* Set the site home and URL to the one that's being visited. */
if ( filter_has_var( INPUT_SERVER, 'HTTP_HOST' ) ) {
    \$host = 'http://' . \$_SERVER['HTTP_HOST'];
	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', \$host );
	}
	if ( ! defined( 'WP_SITEURL' ) ) {
		define( 'WP_SITEURL', \$host);
	}
}
PHP

    cat wp-config.php

    wait_for_db

	wp db reset --yes --allow-root

	wp core multisite-install \
	    --allow-root \
        --url=http://localhost:${WP_PORT:-8081} \
        --base=/ \
        --title=Test \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@ci.dev \
        --skip-email

    wp rewrite flush --allow-root

    cat <<HTACCESS > /var/www/html/.htaccess
# BEGIN WordPress
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php\$ - [L]

# uploaded files
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) wp-includes/ms-files.php?file=\$2 [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin\$ \$1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^[_0-9a-zA-Z-]+/(wp-(content|admin|includes).*) \$1 [L]
RewriteRule ^[_0-9a-zA-Z-]+/(.*\.php)\$ \$1 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS

    wp site empty --yes --uploads --allow-root
    wp db export /project/tests/_data/dump.sql --allow-root
}

create_test_db(){
    cd /var/www/html
	wp db query "create database if not exists tests;" --allow-root
}

call_original_entrypoint(){
	# just in case get back to the /var/www/html folder where the default entrypoint script expects to be
	cd /var/www/html
	# call the original container entrypoint starting the Apache webserver
	source /usr/local/bin/docker-entrypoint.sh apache2-foreground
}

copy_required_plugins
copy_required_themes
config_wp_cli
if [ ${COMPOSER_UPDATE} -eq 1 ]; then
    update_composer_dependencies
fi
install_wordrpess
wait_for_db
create_test_db

echo "+=CI Stack==========================================================+"
echo ""
echo "    Visit http://localhost:$WP_PORT/stack/welcome.html to start"
echo ""
echo "+===================================================================+"

create_utility_pages
setup_path
setup_xdebug ${XDEBUG_REMOTE_HOST}
call_original_entrypoint

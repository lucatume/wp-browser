#!/usr/bin/env sh

xdebug_config_file="$(php --ini | grep xdebug | cut -d, -f1)"
sed -i '/^;zend_extension/ s/;zend_extension/zend_extension/g' "$xdebug_config_file"
# Kill the oldest php-fpm process, the manager.
pkill -o -USR2 php-fpm
# Restart the php-fpm server and php-fpm when used as a module, ignore errors if not running.
/etc/init.d/apache2 reload >/dev/null 2>&1
php -v

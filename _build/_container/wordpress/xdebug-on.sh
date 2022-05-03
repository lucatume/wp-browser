#!/bin/sh
xdebug_config_file=$(php --ini | grep xdebug | cut -d, -f1)
sed -i '/^;zend_extension/ s/;zend_extension/zend_extension/g' "$xdebug_config_file"
php -v

alias c="vendor/bin/codecept"
alias cr="vendor/bin/codecept run"
function xon(){
  xdebug_config_file="$(php --ini | grep xdebug)"
  sed  '/^;zend_extension/ s/;zend_extension/zend_extension/g' "$(php --ini | grep xdebug)" > xdebug_config.tmp
  mv xdebug_config.tmp "$xdebug_config_file"
  rm -f xdebug_config.tmp
  php -v
}
function xoff(){
  xdebug_config_file="$(php --ini | grep xdebug)"
  sed  '/^zend_extension/ s/zend_extension/;zend_extension/g' "$(php --ini | grep xdebug)" > xdebug_config.tmp
  mv xdebug_config.tmp "$xdebug_config_file"
  rm -f xdebug_config.tmp
  php -v
}

xoff

alias c="vendor/bin/codecept"
alias cr="vendor/bin/codecept run"

function xdebug_config_file(){
  echo "$(php --ini | grep xdebug | cut -d, -f1)"
}

function xon(){
  xdebug_config_file=$(xdebug_config_file)
  sed -i '/^;zend_extension/ s/;zend_extension/zend_extension/g' "$xdebug_config_file"
  php -v
}
function xoff(){
  xdebug_config_file=$(xdebug_config_file)
  sed -i '/^zend_extension/ s/zend_extension/;zend_extension/g' "$xdebug_config_file"
  php -v
}

xoff

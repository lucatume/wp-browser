<?php
/** Plugin Name: Some Plugin */

function some_plugin_main(){}

register_activation_hook( __FILE__, 'some_plugin_activation' );
function some_plugin_activation(){
    update_option('some_plugin_activated', 1);
}

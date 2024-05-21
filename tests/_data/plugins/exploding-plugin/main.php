<?php
/** Plugin Name: Exploding Plugin */

function exploding_plugin_main(){}

register_activation_hook( __FILE__, 'exploding_plugin_main_activation' );
function exploding_plugin_main_activation(){
    update_option('exploding_plugin_activated', 1);
    throw new \RuntimeException('Boom');
}

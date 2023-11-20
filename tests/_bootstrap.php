<?php
// This is global bootstrap for autoloading.
use Codeception\Events;
use Codeception\Util\Autoload;

use function tad\WPBrowser\addListener;
use function tad\WPBrowser\Tests\Support\createTestDatabasesIfNotExist;

createTestDatabasesIfNotExist();

// Make sure traits can be autoloaded from tests/_support/Traits
Autoload::addNamespace('\lucatume\WPBrowser\Tests\Traits', codecept_root_dir('tests/_support/Traits'));

// If the `uopz` extension is installed, then ensure `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}

//addListener( Events::SUITE_BEFORE, function ( Codeception\Event\SuiteEvent $event ) {
//  $suiteName = $event->getSuite()->getName();
//
//  if ( $suiteName !== 'wploader_plugin_silent_activation' ) {
//      return;
//  }
//
//  $wpRootDir = realpath( getenv( 'WORDPRESS_ROOT_DIR' ) );
//
//  if ( ! is_dir( $wpRootDir ) ) {
//      throw new \RuntimeException( "The WORDPRESS_ROOT_DIR is not a valid directory." );
//  }
//
//  $wpRootDir = rtrim( $wpRootDir, '/\\' );
//
//  foreach (
//      [
//          codecept_data_dir( 'plugins/doing-it-wrong-1' ) => $wpRootDir . '/wp-content/plugins/doing-it-wrong-1',
//          codecept_data_dir( 'plugins/doing-it-wrong-2' ) => $wpRootDir . '/wp-content/plugins/doing-it-wrong-2',
//      ] as $source => $destination
//  ) {
//      if ( ! copy(
//          $source,
//          $destination
//      ) ) {
//          throw new \RuntimeException( "Could not copy the plugin to the WordPress plugins directory." );
//      }
//  }
//} );

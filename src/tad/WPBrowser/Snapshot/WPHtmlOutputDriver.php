<?php

namespace tad\WPBrowser\Snapshot;

/**
 * Class WPHtmlOutputDriver
 *
 * A WordPress specific HTML output comparison driver for PHPUnit Snapshot Assertions that
 * will normalize WordPress nonce and time-dependent values and full URLs.
 * Example usage, presuming the current WordPress URL is `http://foo.bar` and the original
 * snapshots were generated on a site where WordPress was served at another URL:
 *
 *    $currentOutput = $objectRenderingHtmlUsingWp->renderHtml();
 *    $currentUrl = getenv('WP_URL');
 *    $snapshotUrl = 'http://wp.dev';
 *    $this->assertMatchesSnapshot( $currentOutput, new WPOutput($currentUrl, $snapshotUrl) );
 *
 * @package tad\WPBrowser\Snapshot
 *
 * @see     https://github.com/spatie/phpunit-snapshot-assertions
 * @see     https://packagist.org/packages/spatie/phpunit-snapshot-assertions
 * @see     https://github.com/lucatume/wp-snapshot-assertions
 * @see     https://packagist.org/packages/lucatume/wp-snapshot-assertions
 */
class WPHtmlOutputDriver extends \tad\WP\Snapshots\WPHtmlOutputDriver {}

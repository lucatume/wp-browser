<?php

namespace tad\WPBrowser\Snapshot;

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Drivers\VarDriver;

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
 *    $this->assertMatchesSnapshot( $currentOutput, new WPOutput($currentUrl) );
 *
 * @package tad\WPBrowser\Snapshot
 * @see     https://github.com/spatie/phpunit-snapshot-assertions
 * @see     https://packagist.org/packages/spatie/phpunit-snapshot-assertions
 */
class WPHtmlOutputDriver extends VarDriver {

	/**
	 * @var array An array of attributes that will contain full URLs in the
	 *            snapshot code.
	 */
	protected $urlAttributes = ['href', 'src'];

	/**
	 * @var array An array of keys used in `id` and `name` attributes by WordPress
	 *            to identify time-dependent values like nonces.
	 */
	protected $timeDependentKeys = ['_wpnonce'];

	/**
	 * @var string  The current WordPress installation full URL,
	 *              e.g. `http://example.com`
	 */
	protected $currentUrl;

	/**
	 * WPHtmlOutputDriver constructor.
	 *
	 * @param string $currentUrl The current WordPress full URL,
	 *                           e.g. `http://example.com`
	 */
	public function __construct(string $currentUrl = '') {
		$this->currentUrl = $currentUrl;
	}

	/**
	 * Match an expectation with a snapshot's actual contents. Should throw an
	 * `ExpectationFailedException` if it doesn't match. This happens by
	 * default if you're using PHPUnit's `Assert` class for the match.
	 *
	 * @param mixed $expected
	 * @param mixed $actual
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 */
	public function match($expected, $actual) {
		$evaluated = $this->evalCode($expected);

		Assert::assertEquals(
			$this->normalizeHtml($this->removeTimeValues($this->replaceUrls($evaluated))),
			$this->normalizeHtml($this->removeTimeValues($actual))
		);
	}

	/**
	 * A utility method that will evaluate a snapshot format file into formed HTML.
	 *
	 * @param string $snapshotCode
	 *
	 * @return string The rendered HTML
	 */
	public function evalCode(string $snapshotCode): string {
		return eval(substr($snapshotCode, strlen('<?php ')));
	}

	protected function normalizeHtml(string $input): string {
		$doc = \phpQuery::newDocument($input);

		return $doc->__toString();
	}

	protected function removeTimeValues(string $input): string {
		$doc = \phpQuery::newDocument($input);

		foreach ($this->timeDependentKeys as $name) {
			$doc->find("#{$name},*[name='{$name}'],.{$name}")->each(function (\DOMElement $t) {
				$t->setAttribute('value', '');
			});
		}

		return $this->normalizeHtml($doc->__toString());
	}

	protected function replaceUrls(string $input): string {
		$doc = \phpQuery::newDocument($input);

		foreach ($this->urlAttributes as $name) {
			$doc->find("*[{$name}]")->each(function (\DOMElement $t) use ($name) {
				$current     = $t->getAttribute($name);
				$snapshotUrl = sprintf('%s://%s',
					parse_url($current, PHP_URL_SCHEME),
					parse_url($current, PHP_URL_HOST)
				);

				if ($port = parse_url($current, PHP_URL_PORT)) {
					$snapshotUrl .= ":{$port}";
				}

				$t->setAttribute($name, str_replace($snapshotUrl, $this->currentUrl, $current));
			});
		}

		$html = $doc->__toString();

		return $this->normalizeHtml($html);
	}

	/**
	 * Returns an array that the driver will use to identify and void
	 * by `id`, `class` and`name` attributes time-dependent values like
	 * nonces.
	 *
	 * @return array
	 */
	public function getTimeDependentKeys(): array {
		return $this->timeDependentKeys;
	}

	/**
	 * Sets the array that the driver will use to identify and void
	 * by `id`, `class` and`name` attributes time-dependent values like
	 * nonces.
	 *
	 * @param array $timeDependentKeys
	 */
	public function setTimeDependentKeys(array $timeDependentKeys) {
		$this->timeDependentKeys = $timeDependentKeys;
	}

	/**
	 * Returns the array of attributes the driver will modify to replace
	 * full URLs in the snapshot.
	 *
	 * @return array
	 */
	public function getUrlAttributes(): array {
		return $this->urlAttributes;
	}

	/**
	 * Sets the array of attributes the driver will modify to replace
	 * full URLs in the snapshot.
	 *
	 * @param array $urlAttributes
	 */
	public function setUrlAttributes(array $urlAttributes) {
		$this->urlAttributes = $urlAttributes;
	}
}
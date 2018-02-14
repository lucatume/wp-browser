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
 *    $snapshotUrl = 'http://wp.dev';
 *    $this->assertMatchesSnapshot( $currentOutput, new WPOutput($currentUrl, $snapshotUrl) );
 *
 * @package tad\WPBrowser\Snapshot
 * @see     https://github.com/spatie/phpunit-snapshot-assertions
 * @see     https://packagist.org/packages/spatie/phpunit-snapshot-assertions
 */
class WPHtmlOutputDriver extends VarDriver {

	/**
	 * @var array An array of strings that will be considered tolerable differences.
	 */
	protected $tolerableDifferences = [];

	/**
	 * @var array The list of prefixes that should be used to find and replace tolerable differences.
	 */
	protected $prefixes = [];

	/**
	 * @var array The list of postfixes that should be used to find and replace tolerable differences.
	 */
	protected $postfixes = [];

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
	 * @var string
	 */
	protected $snapshotUrl;

	/**
	 * WPHtmlOutputDriver constructor.
	 *
	 * @param string      $currentUrl  The current WordPress full URL,
	 *                                 e.g. `http://example.com`
	 * @param string|null $snapshotUrl The WordPress URL used in the snapshot file; this
	 *                                 will narrow down the replacement to WordPress generated
	 *                                 URLs only.
	 */
	public function __construct(string $currentUrl = '', string $snapshotUrl = null) {
		$this->currentUrl  = $currentUrl;
		$this->snapshotUrl = $snapshotUrl;
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

		$normalizedExpected = $this->normalizeHtml($this->removeTimeValues($this->replaceUrls($evaluated)));
		$normalizedActual   = $this->normalizeHtml($this->removeTimeValues($actual));

		if (!empty($this->tolerableDifferences)) {
			$normalizedActual = $this->applyTolerableDifferences($normalizedExpected, $normalizedActual);
		}

		Assert::assertEquals($normalizedExpected, $normalizedActual);
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
			$selector = empty($this->snapshotUrl) ?
				"*[{$name}]"
				: "*[{$name}^='{$this->snapshotUrl}']";
			$doc->find($selector)->each(function (\DOMElement $t) use ($name) {
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

	protected function applyTolerableDifferences(string $expected, string $actual): string {
		$expectedDelims = array_unique(preg_split('/[A-Za-z0-9]/', $expected));
		$actualDelims = array_unique(preg_split('/[A-Za-z0-9]/', $actual));

		$expectedNoDelims = preg_replace('/[^A-Za-z0-9]/', ' ', str_replace($expectedDelims, ' ', $expected));
		$actualNoDelims = preg_replace('/[^A-Za-z0-9]/', ' ', str_replace($actualDelims, ' ', $actual));

		$expectedFrags = array_map('trim', array_filter(explode(' ', $expectedNoDelims)));
		$actualFrags   = array_map('trim', array_filter(explode(' ', $actualNoDelims)));

		$tolerated = [];
		foreach ($actualFrags as $i => $value) {
			if (!isset($expectedFrags[$i])) {
				continue;
			}
			if ($actualFrags[$i] == $expectedFrags[$i]) {
				continue;
			}

			if (in_array($actualFrags[$i], $this->tolerableDifferences)) {
				$tolerated[$actualFrags[$i]] = $expectedFrags[$i];
			}
		}

		foreach ($tolerated as $find => $replace) {
			$prefixes = array_map(function ($pre) {
				return preg_quote($pre, '/');
			}, $this->prefixes);

			$postfixes = array_map(function ($post) {
				return preg_quote($post, '/');
			}, $this->postfixes);

			$findPattern = '(' . implode('|', $prefixes) . ')*'
						   . preg_quote($find, '/')
						   . '(' . implode('|', $postfixes) . ')*';

			$pattern = '/(?<![\\w\\d])' . $findPattern . '(?![\\w\\d])/';

			$replacement = '${1}' . $replace . '${2}';

			$actual = preg_replace($pattern, $replacement, $actual);
		}

		return $actual;
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

	/**
	 * Sets the current list of strings that are considered as tolerable differences.
	 *
	 * @return array
	 */
	public function getTolerableDifferences(): array {
		return $this->tolerableDifferences;
	}

	/**
	 * Returns the current list of strings that are considered as tolerable differences.
	 *
	 * @param array $tolerableDifferences
	 */
	public function setTolerableDifferences(array $tolerableDifferences) {
		$this->tolerableDifferences = $tolerableDifferences;
	}

	/**
	 * Returns the list of prefixes that should be used to find and replace tolerable differences.
	 *
	 * @return array
	 */
	public function getTolerableDifferencesPrefixes(): array {
		return $this->prefixes;
	}

	/**
	 * Sets the list of prefixes that should be used to find and replace tolerable differences.
	 *
	 * @param array $prefixes
	 */
	public function setTolerableDifferencesPrefixes(array $prefixes) {
		$this->prefixes = $prefixes;
	}

	/**
	 * Returns the list of postfixes that should be used to find and replace tolerable differences.
	 *
	 * @return array
	 */
	public function getTolerableDifferencesPostfixes(): array {
		return $this->postfixes;
	}

	/**
	 * Sets the list of postfixes that should be used to find and replace tolerable differences.
	 *
	 * @param array $postfixes
	 */
	public function setTolerableDifferencesPostfixes(array $postfixes) {
		$this->postfixes = $postfixes;
	}
}

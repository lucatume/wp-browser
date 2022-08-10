<?php
/**
 * A Codeception module offering specific WordPress browsing methods.
 *
 * @package Codeception\Module
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module\PhpBrowser;
use Facebook\WebDriver\Cookie as FacebookWebdriverCookie;
use lucatume\WPBrowser\Utils\Codeception;
use Symfony\Component\BrowserKit\Cookie;

//phpcs:disable
Codeception::checkModuleRequirements('WPBrowser', [ 'PhpBrowser' ]);
//phpcs:enable

/**
 * Class WPBrowser
 *
 * @package Codeception\Module
 */
class WPBrowser extends PhpBrowser
{
    use WPBrowserMethods;

    /**
     * The module required fields, to be set in the suite .yml configuration file.
     *
     * @var array<string>
     */
    protected array $requiredFields = ['adminUsername', 'adminPassword', 'adminPath'];

    /**
     * Returns all the cookies whose name matches a regex pattern.
     *
     * @example
     * ```php
     * $I->loginAs('customer','password');
     * $I->amOnPage('/shop');
     * $cartCookies = $I->grabCookiesWithPattern("#^shop_cart\\.*#");
     * ```
     * @param string $cookiePattern The regular expression pattern to use for the matching.
     *
     * @return array|null An array of cookies matching the pattern.
     */
    public function grabCookiesWithPattern(string $cookiePattern): ?array
    {
        /**
         * @var array<FacebookWebdriverCookie|Cookie>
         */
        $cookies = $this->client->getCookieJar()->all();

        if (!$cookies) {
            return null;
        }
        $matchingCookies = array_filter($cookies, static function ($cookie) use ($cookiePattern): int|bool {
            return preg_match($cookiePattern, $cookie->getName());
        });
        $cookieList = array_map(static function ($cookie): string {
            return sprintf('{"%s": "%s"}', $cookie->getName(), $cookie->getValue());
        }, $matchingCookies);

        $this->debug('Cookies matching pattern ' . $cookiePattern . ' : ' . implode(', ', $cookieList));

        return count($matchingCookies) ? $matchingCookies : null;
    }

    /**
     * In the plugin administration screen activates a plugin clicking the "Activate" link.
     *
     * The method will **not** handle authentication to the admin area.
     *
     * @example
     * ```php
     * // Activate a plugin.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->activatePlugin('hello-dolly');
     * // Activate a list of plugins.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->activatePlugin(['hello-dolly','another-plugin']);
     * ```
     *
     * @param  string|array<string> $pluginSlug The plugin slug, like "hello-dolly" or a list of plugin slugs.
     */
    public function activatePlugin(string|array $pluginSlug): void
    {
        foreach ((array)$pluginSlug as $plugin) {
            $this->checkOption('//*[@data-slug="' . $plugin . '"]/th/input');
        }
        $this->selectOption('action', 'activate-selected');
        $this->click("#doaction");
    }

    /**
     * In the plugin administration screen deactivate a plugin clicking the "Deactivate" link.
     *
     * The method will **not** handle authentication and navigation to the plugins administration page.
     *
     * @example
     * ```php
     * // Deactivate one plugin.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->deactivatePlugin('hello-dolly');
     * // Deactivate a list of plugins.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->deactivatePlugin(['hello-dolly', 'my-plugin']);
     * ```
     *
     * @param  string|array<string> $pluginSlug The plugin slug, like "hello-dolly", or a list of plugin slugs.
     */
    public function deactivatePlugin(string|array $pluginSlug): void
    {
        foreach ((array) $pluginSlug as $plugin) {
            $this->checkOption('//*[@data-slug="' . $plugin . '"]/th/input');
        }
        $this->selectOption('action', 'deactivate-selected');
        $this->click('#doaction');
    }

    /**
     * Validates the module configuration.
     *
     *
     * @throws ModuleConfigException|ModuleException If there's any issue.
     */
    protected function validateConfig(): void
    {
        $this->configBackCompat();

        parent::validateConfig();
    }
}

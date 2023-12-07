<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use function preg_match;

class WpConfigFileGenerator
{
    /**
     * @var string
     */
    private $wpConfigFileContents;
    /**
     * @var string
     */
    private $relativePathRoot;

    /**
     * @throws InstallationException
     */
    public function __construct(string $wpRootDir, string $relativePathRoot = null)
    {
        if (!is_dir($wpRootDir)) {
            throw new InstallationException(
                'The WordPress root directory does not exist.',
                InstallationException::ROOT_DIR_NOT_FOUND
            );
        }

        $wpConfigSampleFile = rtrim($wpRootDir, '\\/') . '/wp-config-sample.php';

        if (!is_file($wpConfigSampleFile)
            || ($wpConfigSampleFileContents = file_get_contents($wpConfigSampleFile)) === false
        ) {
            throw new InstallationException(
                "$wpConfigSampleFile not found or not readable.",
                InstallationException::WP_CONFIG_SAMPLE_FILE_NOT_FOUND
            );
        }

        if ($relativePathRoot && !is_dir($relativePathRoot)) {
            throw new InstallationException(
                'The relative path root directory does not exist.',
                InstallationException::RELATIVE_PATH_ROOT_NOT_FOUND
            );
        }

        $this->relativePathRoot = $relativePathRoot ?? $wpRootDir;

        $this->wpConfigFileContents = $wpConfigSampleFileContents;
    }

    private function setDbCredentials(DatabaseInterface $db): self
    {
        $this->wpConfigFileContents = str_replace(
            [
                'database_name_here',
                'username_here',
                'password_here',
                'localhost',
            ],
            [
                $db->getDbName(),
                $db->getDbUser(),
                $db->getDbPassword(),
                $db->getDbHost()
            ],
            $this->wpConfigFileContents
        );

        $tablePrefix = $db->getTablePrefix();
        $this->wpConfigFileContents = (string)preg_replace(
            '/\$table_prefix\s*=\s*\'wp_\';/',
            "\$table_prefix = '$tablePrefix';",
            $this->wpConfigFileContents,
            1
        );

        return $this;
    }

    private function setSalts(ConfigurationData $configurationData): self
    {
        $saltDefinitionPattern = "/define\( '(?<const>(?:AUTH|SECURE_AUTH|LOGGED_IN|NONCE)_(?:KEY|SALT))'," .
            "\\s*'put your unique phrase here' \);/u";
        $this->wpConfigFileContents = (string)preg_replace_callback(
            $saltDefinitionPattern,
            static function (array $matches) use ($configurationData): string {
                switch ($matches['const']) {
                    case 'AUTH_KEY':
                        $value = $configurationData->getAuthKey();
                        break;
                    case 'SECURE_AUTH_KEY':
                        $value = $configurationData->getSecureAuthKey();
                        break;
                    case 'LOGGED_IN_KEY':
                        $value = $configurationData->getLoggedInKey();
                        break;
                    case 'NONCE_KEY':
                        $value = $configurationData->getNonceKey();
                        break;
                    case 'AUTH_SALT':
                        $value = $configurationData->getAuthSalt();
                        break;
                    case 'SECURE_AUTH_SALT':
                        $value = $configurationData->getSecureAuthSalt();
                        break;
                    case 'LOGGED_IN_SALT':
                        $value = $configurationData->getLoggedInSalt();
                        break;
                    case 'NONCE_SALT':
                        $value = $configurationData->getNonceSalt();
                        break;
                    default:
                        throw new RuntimeException("Unknown constant {$matches['const']}.");
                }
                return str_replace('put your unique phrase here', $value, $matches[0]);
            },
            $this->wpConfigFileContents
        );

        return $this;
    }

    /**
     * @throws InstallationException
     */
    private function setMultisite(int $multisite = InstallationStateInterface::SINGLE_SITE): self
    {
        if ($multisite === InstallationStateInterface::SINGLE_SITE) {
            return $this;
        }

        $subdomainInstallString = $multisite === InstallationStateInterface::MULTISITE_SUBDOMAIN ? 'true' : 'false';
        $multisiteConstantsBlock = <<< PHP
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', $subdomainInstallString );
define( 'DOMAIN_CURRENT_SITE', \$_SERVER['HTTP_HOST'] ?? 'example.com' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

PHP;

        return $this->setExtraPHP($multisiteConstantsBlock);
    }

    public function getContents(): string
    {
        return $this->wpConfigFileContents;
    }

    /**
     * @throws InstallationException
     */
    public function produce(
        DatabaseInterface $db,
        ?ConfigurationData $configurationData = null,
        int $multisite = InstallationStateInterface::SINGLE_SITE
    ): string {
        $configurationData = $configurationData ?? new ConfigurationData();
        $extraPHP = $configurationData->getExtraPhp();

        if ($db instanceof SQLiteDatabase) {
            $relativeDbDir = FS::relativePath($this->relativePathRoot, $db->getDbDir());
            $relativeDbDir = $relativeDbDir ? "__DIR__ . '/$relativeDbDir'" : '__DIR__';
            $extraPHP = <<< PHP
define( 'DB_DIR', {$relativeDbDir} );
define( 'DB_FILE', '{$db->getDbFile()}' );
$extraPHP
PHP;
        }

        $this->setDbCredentials($db)
            ->setSalts($configurationData)
            ->setMultisite($multisite)
            ->setExtraPHP($extraPHP)
            ->removeExcessBlankLines();
        return $this->wpConfigFileContents;
    }

    /**
     * @throws InstallationException
     */
    private function setExtraPHP(?string $extraPHP = null): self
    {
        if (!$extraPHP) {
            return $this;
        }


        $placeholderPattern = '/^\\/\\*\\s*?That\'s all, stop editing!.*?$/um';
        if (!preg_match($placeholderPattern, $this->wpConfigFileContents, $placeholderMatches)) {
            throw new InstallationException(
                "Could not find the placeholder string in the wp-config.php file contents.",
                InstallationException::WP_CONFIG_FILE_MISSING_PLACEHOLDER
            );
        }

        $this->wpConfigFileContents = (string)preg_replace(
            $placeholderPattern,
            $extraPHP . PHP_EOL . $placeholderMatches[0],
            $this->wpConfigFileContents
        );

        return $this;
    }

    private function removeExcessBlankLines(): self
    {
        // Normalize line endings.
        $this->wpConfigFileContents = (string)str_replace("\r\n", "\n", $this->wpConfigFileContents);
        // Replace 2 ore more blank lines with 1 blank line.
        $this->wpConfigFileContents = (string)preg_replace("/\n{2,}/", "\n\n", $this->wpConfigFileContents);

        return $this;
    }
}

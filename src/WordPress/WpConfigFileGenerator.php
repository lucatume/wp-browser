<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;

class WpConfigFileGenerator
{
    private string $wpConfigFileContents;

    /**
     * @throws InstallationException
     */
    public function __construct(string $wpRootDir)
    {
        if (!is_dir($wpRootDir)) {
            throw new InstallationException(
                'The WordPress root directory does not exist.',
                InstallationException::ROOT_DIR_NOT_FOUND
            );
        }

        $wpConfigSampleFile = rtrim($wpRootDir, '\\/') . '/wp-config-sample.php';

        if (
            !is_file($wpConfigSampleFile)
            || ($wpConfigSampleFileContents = file_get_contents($wpConfigSampleFile)) === false
        ) {
            throw new InstallationException(
                "$wpConfigSampleFile not found or not readable.",
                InstallationException::WP_CONFIG_SAMPLE_FILE_NOT_FOUND
            );
        }

        $this->wpConfigFileContents = $wpConfigSampleFileContents;
    }

    private function setDbCredentials(Db $db): self
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
        $this->wpConfigFileContents = preg_replace(
            '/\$table_prefix\s*=\s*\'wp_\';/',
            "\$table_prefix = '$tablePrefix';",
            $this->wpConfigFileContents,
            1
        );

        return $this;
    }

    private function setSalts(ConfigurationData $configurationData): self
    {
        $this->wpConfigFileContents = preg_replace_callback(
            "/define\( '(?<const>(?:AUTH|SECURE_AUTH|LOGGED_IN|NONCE)_(?:KEY|SALT))',\\s*'put your unique phrase here' \);/u",
            static function (array $matches) use ($configurationData): string {
                $value = match ($matches['const']) {
                    'AUTH_KEY' => $configurationData->getAuthKey(),
                    'SECURE_AUTH_KEY' => $configurationData->getSecureAuthKey(),
                    'LOGGED_IN_KEY' => $configurationData->getLoggedInKey(),
                    'NONCE_KEY' => $configurationData->getNonceKey(),
                    'AUTH_SALT' => $configurationData->getAuthSalt(),
                    'SECURE_AUTH_SALT' => $configurationData->getSecureAuthSalt(),
                    'LOGGED_IN_SALT' => $configurationData->getLoggedInSalt(),
                    'NONCE_SALT' => $configurationData->getNonceSalt(),
                };
                return str_replace('put your unique phrase here', $value, $matches[0]);
            },
            $this->wpConfigFileContents);

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
define( 'DOMAIN_CURRENT_SITE', \$_SERVER['HTTP_HOST'] );
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
        Db $db,
        ConfigurationData $configurationData,
        int $multisite = InstallationStateInterface::SINGLE_SITE
    ): string {
        $this->setDbCredentials($db)
            ->setSalts($configurationData)
            ->setMultisite($multisite)
            ->setExtraPHP($configurationData->getExtraPhp())
            ->removeExcessBlankLines();
        return $this->wpConfigFileContents;
    }

    private function setExtraPHP(?string $extraPHP = null): self
    {
        if (!$extraPHP) {
            return $this;
        }

        $placeholder = '/* That\'s all, stop editing! Happy publishing. */';

        if (!\str_contains($this->wpConfigFileContents, $placeholder)) {
            throw new InstallationException(
                "Could not find the placeholder string in the wp-config.php file contents.",
                InstallationException::WP_CONFIG_FILE_MISSING_PLACEHOLDER
            );
        }

        $this->wpConfigFileContents = str_replace(
            $placeholder,
            $extraPHP . PHP_EOL . $placeholder,
            $this->wpConfigFileContents
        );

        return $this;
    }

    private function removeExcessBlankLines(): self
    {
        // Normalize line endings.
        $this->wpConfigFileContents = str_replace("\r\n", "\n", $this->wpConfigFileContents);
        // Replace 2 ore more blank lines with 1 blank line.
        $this->wpConfigFileContents = preg_replace("/\n{2,}/", "\n\n", $this->wpConfigFileContents);

        return $this;
    }
}
<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;

class Version
{
    use WordPressChecks;

    /**
     * @var string
     */
    private $versionFile;
    /**
     * @var string
     */
    private $wpVersion;
    /**
     * @var string
     */
    private $wpDbVersion;
    /**
     * @var string
     */
    private $tinymceVersion;
    /**
     * @var string
     */
    private $requiredPhpVersion;
    /**
     * @var string
     */
    private $requiredMySqlVersion;

    /**
     * @throws InstallationException
     */
    public function __construct(string $wpRootDir)
    {
        $wpRootDir = $this->checkWPRootDir($wpRootDir);

        $this->versionFile = rtrim($wpRootDir, '\\/') . '/' . 'wp-includes/version.php';

        if (!file_exists($this->versionFile)) {
            throw new InstallationException(
                'The WordPress installation directory does not contain a version.php file.',
                InstallationException::VERSION_FILE_NOT_FOUND
            );
        }

        $this->readVersion();
    }

    /**
     * @throws InstallationException
     */
    private function readVersion(): void
    {
        include $this->versionFile;

        /** @noinspection IssetArgumentExistenceInspection Defined in the WordPress version file. */
        if (!isset($wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version)) {
            throw new InstallationException(
                "The WordPress version file $this->versionFile does not contain all the expected information.",
                InstallationException::VERSION_FILE_MISSING_INFO
            );
        }

        $this->wpVersion = $wp_version;
        $this->wpDbVersion = $wp_db_version;
        $this->tinymceVersion = $tinymce_version;
        $this->requiredPhpVersion = $required_php_version;
        $this->requiredMySqlVersion = $required_mysql_version;

        unset($wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version);
    }

    public function getWpVersion(): string
    {
        return $this->wpVersion;
    }

    public function getWpDbVersion(): string
    {
        return $this->wpDbVersion;
    }

    public function getTinymceVersion(): string
    {
        return $this->tinymceVersion;
    }

    public function getRequiredPhpVersion(): string
    {
        return $this->requiredPhpVersion;
    }

    public function getRequiredMySqlVersion(): string
    {
        return $this->requiredMySqlVersion;
    }

    /**
     * @return array{
     *     wpVersion: string,
     *     wpDbVersion: string,
     *     tinymceVersion: string,
     *     requiredPhpVersion: string,
     *     requiredMySqlVersion: string
     * }
     */
    public function toArray(): array
    {
        return [
            'wpVersion' => $this->getWpVersion(),
            'wpDbVersion' => $this->getWpDbVersion(),
            'tinymceVersion' => $this->getTinymceVersion(),
            'requiredPhpVersion' => $this->getRequiredPhpVersion(),
            'requiredMySqlVersion' => $this->getRequiredMySqlVersion()
        ];
    }
}

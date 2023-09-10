<?php

namespace lucatume\WPBrowser\Tests\FSTemplates;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\Source;

class BedrockProject
{
    public function __construct(private MysqlDatabase $db, private string $home)
    {
    }

    public function scaffold(string $dir, string $version = 'latest'): string
    {
        FS::recurseCopy(codecept_data_dir('fs-templates/bedrock'), $dir);

        $envContents = file_get_contents($dir . '/.env');

        if ($envContents === false) {
            throw new RuntimeException('Could not read .env file.');
        }

        $replacements = [
            'DB_NAME' => $this->db->getDbName(),
            'DB_USER' => $this->db->getDbUser(),
            'DB_PASSWORD' => $this->db->getDbPassword(),
            'DB_HOST' => $this->db->getDbHost(),
            'DB_PREFIX' => $this->db->getTablePrefix(),
            'WP_HOME' => $this->home,
            'AUTH_KEY' => Random::salt(),
            'SECURE_AUTH_KEY' => Random::salt(),
            'LOGGED_IN_KEY' => Random::salt(),
            'NONCE_KEY' => Random::salt(),
            'AUTH_SALT' => Random::salt(),
            'SECURE_AUTH_SALT' => Random::salt(),
            'LOGGED_IN_SALT' => Random::salt(),
            'NONCE_SALT' => Random::salt(),
        ];

        $envContents = str_replace(
            array_map(fn(string $key) => '{{' . $key . '}}', array_keys($replacements)),
            $replacements,
            $envContents
        );

        if (!file_put_contents($dir . '/.env', $envContents, LOCK_EX)) {
            throw new RuntimeException('Could not write .env file.');
        }

        FS::recurseCopy(Source::getForVersion($version), $dir . '/web/wp');

        return $dir;
    }
}

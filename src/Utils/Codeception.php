<?php

namespace lucatume\WPBrowser\Utils;

use Codeception\Codecept;

class Codeception
{
    public static function dataDir(string $rootDir = null): string
    {
        $relDataPath = version_compare(Codecept::VERSION, '5.0.0', '>=')
            ? implode(DIRECTORY_SEPARATOR, ['tests', 'Support', 'Data'])
            : 'tests' . DIRECTORY_SEPARATOR . '_data';

        return $rootDir ? rtrim($rootDir, '\\/') . DIRECTORY_SEPARATOR . $relDataPath
            : $relDataPath;
    }

    public static function supportDir(string $rootDir = null): string
    {
        $relSupportPath = version_compare(Codecept::VERSION, '5.0.0', '>=')
            ? implode(DIRECTORY_SEPARATOR, ['tests', 'Support'])
            : 'tests' . DIRECTORY_SEPARATOR . '_support';

        return $rootDir ? rtrim($rootDir, '\\/') . DIRECTORY_SEPARATOR . $relSupportPath
            : $relSupportPath;
    }
}

<?php


namespace Unit\lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class InstallationTest extends \Codeception\Test\Unit
{
    /**
     * It should read version from files
     *
     * @test
     */
    public function should_read_version_from_files()
    {
        $wpRoot = FS::tmpDir();
        Installation::scaffold($wpRoot, '4.9.8');

        $installation = new Installation($wpRoot);

        $this->assertEquals([
            'wpVersion' => '4.9.8',
            'wpDbVersion' => '38590',
            'tinymceVersion' => '4800-20180716',
            'requiredPhpVersion' => '5.2.4',
            'requiredMySqlVersion' => '5.0'
        ], $installation->getVersion()->toArray());
    }
}

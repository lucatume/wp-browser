<?php
namespace lucatume\WPBrowser\Project;

use Exception;
use JsonException;
use lucatume\WPBrowser\Command\DevInfo;
use lucatume\WPBrowser\Command\DevRestart;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;

trait SetupTemplateTrait
{
    /**
     * @throws JsonException
     */
    protected function addChromedriverDevDependency(): void
    {
        $this->sayInfo('Adding Chromedriver binary as a development dependency ...');
        $composer = new Composer($this->workDir . '/composer.json');
        $composer->requireDev(['webdriver-binary/binary-chromedriver' => '*']);
        $composer->allowPluginsFromPackage('webdriver-binary/binary-chromedriver');
        $composer->update('webdriver-binary/binary-chromedriver');
    }
}

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

    protected function getFreeLocalhostPort(
        string $docRoot
    ): int {
        try {
            $process = new Process(['php', '-S', 'localhost:0', '-t', $docRoot]);
            $process->start();
            do {
                if (!$process->isRunning() && $process->getExitCode() !== 0) {
                    throw new RuntimeException($process->getErrorOutput() ?: $process->getOutput());
                }
                $output = $process->getErrorOutput();
                $port = preg_match('~localhost:(\d+)~', $output, $matches) ? $matches[1] : null;
            } while ($port === null);
            return (int)$port;
        } catch (Exception $e) {
            throw new RuntimeException(
                'Could not start PHP built-in server to find free localhost port: ' . $e->getMessage()
            );
        } finally {
            if (isset($process)) {
                $process->stop();
            }
        }
    }

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
        $this->say();
    }
}

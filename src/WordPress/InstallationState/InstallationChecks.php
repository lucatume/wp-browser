<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\DbException;
use Throwable;

trait InstallationChecks
{
    /**
     * @throws Throwable
     * @throws DbException
     * @throws WorkerException
     * @throws ProcessException
     */
    protected function isInstalled(bool $multisite): bool
    {
        if (!($this->wpRootDir && $this->db->exists())) {
            return false;
        }

        $siteurl = $this->db->getOption('siteurl');

        if (!(is_string($siteurl) && $siteurl !== '')) {
            return false;
        }

        $host = parse_url($siteurl, PHP_URL_HOST);

        if (!$host) {
            return false;
        }

        $codeExecutionFactory = new CodeExecutionFactory($this->wpRootDir, $host);
        $result = Loop::executeClosure($codeExecutionFactory->toCheckIfWpIsInstalled($multisite));
        $returnValue = $result->getReturnValue();

        return (Arr::firstFrom($returnValue, false) === true);
    }
}
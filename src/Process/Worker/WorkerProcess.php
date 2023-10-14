<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Utils\Property;

class WorkerProcess extends Process
{
    /**
     * @var resource|null
     */
    private $stdoutStream;
    /**
     * @var resource|null
     */
    private $stderrStream;

    /**
     * @return resource
     * @throws ProcessException
     */
    public function getStdoutStream()
    {
        if (is_resource($this->stdoutStream)) {
            return $this->stdoutStream;
        }

        $stream = Property::readPrivate($this, 'stdout');

        if (!is_resource($stream)) {
            throw new ProcessException('Could not get the process stdout stream.');
        }

        $this->stdoutStream = $stream;

        return $stream;
    }

    /**
     * @return resource
     * @throws ProcessException
     */
    public function getStdErrStream()
    {
        if (is_resource($this->stderrStream)) {
            return $this->stderrStream;
        }

        $stream = Property::readPrivate($this, 'stderr');

        if (!is_resource($stream)) {
            throw new ProcessException('Could not get the process stderr stream.');
        }

        $this->stderrStream = $stream;

        return $stream;
    }
}

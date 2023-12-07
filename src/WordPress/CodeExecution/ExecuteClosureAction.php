<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;

class ExecuteClosureAction implements CodeExecutionActionInterface
{
    /**
     * @var \lucatume\WPBrowser\WordPress\FileRequests\FileRequest
     */
    private $request;

    public function __construct(FileRequest $request, string $wpRootDir, Closure $closure)
    {
        $request
            ->runInFastMode($wpRootDir)
            ->setTargetFile($wpRootDir . '/wp-load.php')
            ->addAfterLoadClosure($closure);
        $this->request = $request;
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request) {
            $returnValues = $request->execute();

            if (count($returnValues)) {
                return $returnValues[0];
            }

            return null;
        };
    }
}

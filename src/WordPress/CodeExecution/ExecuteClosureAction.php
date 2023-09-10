<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;

class ExecuteClosureAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(FileRequest $request, string $wpRootDir, Closure $closure)
    {
        $request
            ->setTargetFile($wpRootDir . '/wp-load.php')
            ->addAfterLoadClosure($closure);
        $this->request = $request;
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            $returnValues = $request->execute();

            if (count($returnValues)) {
                return $returnValues[0];
            }

            return null;
        };
    }
}

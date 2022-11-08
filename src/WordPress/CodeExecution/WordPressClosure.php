<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use Opis\Closure\SerializableClosure;

class WordPressClosure implements \Serializable
{
    private string $wpRootDir;

    /**
     * This property will be a SerializableClosure in the loop, and will be set to a Closure in the worker.
     *
     * @var SerializableClosure|Closure
     */
    private SerializableClosure|Closure $closure;

    /**
     * WordPressClosure constructor.
     *
     * since TBD
     *
     * @param Closure $closure The closure to wrap.
     */
    public function __construct(string $wpRootDir, Closure $closure)
    {
        $this->wpRootDir = $wpRootDir;
        $this->closure = new SerializableClosure($closure);
    }

    public function serialize()
    {
        return serialize([
            'wpRootDir' => $this->wpRootDir,
            'closure' => $this->closure,
        ]);
    }

    public function unserialize(string $data)
    {
        $unserialized = unserialize($data, ['allowed_classes' => true]);
        $this->wpRootDir = $unserialized['wpRootDir'];
        $this->closure = $unserialized['closure'];
    }

    public function execute(): mixed
    {
        // @todo: SHORTINIT?!
        $_SERVER['HTTP_HOST'] = 'localhost';
        require_once $this->wpRootDir . '/wp-load.php';
        return ($this->closure)();
    }
}

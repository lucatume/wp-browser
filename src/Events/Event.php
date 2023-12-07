<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Events;

class Event extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array<(string | int), mixed>
     */
    private $context;
    /**
     * @var mixed
     */
    private $origin = null;
    /**
     * @param array<string|int,mixed> $context
     * @param mixed $origin
     */
    public function __construct(string $name, array $context, $origin = null)
    {
        $this->name = $name;
        $this->context = $context;
        $this->origin = $origin;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default)
    {
        return $this->context[$key] ?? $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}

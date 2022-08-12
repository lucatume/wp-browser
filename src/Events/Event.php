<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Events;

class Event extends \Symfony\Contracts\EventDispatcher\Event
{
    public function __construct(private string $name, private array $context, private mixed $origin = null)
    {
    }

    public function get(string $key, mixed $default): mixed
    {
        return $this->context[$key] ?? $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrigin(): mixed
    {
        return $this->origin;
    }
}

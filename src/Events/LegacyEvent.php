<?php

namespace lucatume\WPBrowser\Events;

if (class_exists('Symfony\Component\EventDispatcher\Event')) {
    class LegacyEvent extends \Symfony\Component\EventDispatcher\Event
    {
        /**
         * @param array<string|int,mixed> $context
         */
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
}

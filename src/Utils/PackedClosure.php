<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use Closure;

final class PackedClosure
{
    private static ?Packer $packer = null;

    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getClosure(): Closure
    {
        return $this->closure;
    }

    /**
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return ($this->closure)(...$args);
    }

    /**
     * @return array{packed: string}
     *
     * @throws PackerException
     */
    public function __serialize(): array
    {
        return ['packed' => self::packer()->pack($this->closure)];
    }

    /**
     * @param array{packed: string} $data
     *
     * @throws PackerException
     */
    public function __unserialize(array $data): void
    {
        if (!isset($data['packed']) || !is_string($data['packed'])) {
            throw new PackerException('Invalid PackedClosure payload: missing "packed" string.');
        }

        $unpacked = self::packer()->unpack($data['packed']);

        if (!$unpacked instanceof Closure) {
            throw new PackerException('Unpacked value is not a Closure.');
        }

        $this->closure = $unpacked;
    }

    private static function packer(): Packer
    {
        return self::$packer ??= new Packer();
    }
}

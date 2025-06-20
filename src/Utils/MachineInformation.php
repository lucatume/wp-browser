<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class MachineInformation
{
    public const OS_DARWIN = 'darwin';
    public const OS_LINUX = 'linux';
    public const OS_WINDOWS = 'windows';
    public const OS_UNKNOWN = 'unknown';
    public const ARCH_X86_64 = 'x86_64';
    public const ARCH_ARM64 = 'arm64';
    public const ARCH_UNKNOWN = 'unknown';
    /**
     * @var string
     */
    private $operatingSystem;
    /**
     * @var string
     */
    private $architecture;

    public function __construct(?string $operatingSystem = null, ?string $architecture = null)
    {
        if ($operatingSystem === null) {
            switch (strtolower(substr(php_uname('s'), 0, 3))) {
                case 'dar':
                    $this->operatingSystem = self::OS_DARWIN;
                    break;
                case 'lin':
                    $this->operatingSystem = self::OS_LINUX;
                    break;
                case 'win':
                    $this->operatingSystem = self::OS_WINDOWS;
                    break;
                default:
                    $this->operatingSystem = self::OS_UNKNOWN;
                    break;
            }
        } else {
            $this->operatingSystem = $operatingSystem;
        }

        if ($architecture === null) {
            switch (strtolower(php_uname('m'))) {
                case 'x86_64':
                case 'amd64':
                    $this->architecture = self::ARCH_X86_64;
                    break;
                case 'arm64':
                case 'aarch64':
                    $this->architecture = self::ARCH_ARM64;
                    break;
                default:
                    $this->architecture = self::ARCH_UNKNOWN;
                    break;
            }
        } else {
            $this->architecture = $architecture;
        }
    }

    public function getOperatingSystem(): string
    {
        return $this->operatingSystem;
    }

    public function getArchitecture(): string
    {
        return $this->architecture;
    }

    public function isWindows():bool
    {
        return $this->operatingSystem === self::OS_WINDOWS;
    }
}

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
    private string $operatingSystem;
    private string $architecture;

    public function __construct(string $operatingSystem = null, string $architecture = null)
    {
        $this->operatingSystem = $operatingSystem ?? match (strtolower(substr(php_uname('s'), 0, 3))) {
            'dar' => self::OS_DARWIN,
            'lin' => self::OS_LINUX,
            'win' => self::OS_WINDOWS,
            default => self::OS_UNKNOWN
        };

        $this->architecture = $architecture ?? match (strtolower(php_uname('m'))) {
            'x86_64', 'amd64' => self::ARCH_X86_64,
            'arm64', 'aarch64' => self::ARCH_ARM64,
            default => self::ARCH_UNKNOWN
        };
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

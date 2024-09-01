<?php

namespace unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\MachineInformation;

class MachineInformationTest extends Unit
{
    use UopzFunctions;

    public function testConstructorDataProvider(): array
    {
        return [
            [MachineInformation::OS_LINUX, MachineInformation::ARCH_X86_64],
            [MachineInformation::OS_WINDOWS, MachineInformation::ARCH_X86_64],
            [MachineInformation::OS_DARWIN, MachineInformation::ARCH_X86_64],
            [MachineInformation::OS_LINUX, MachineInformation::ARCH_ARM64],
            [MachineInformation::OS_WINDOWS, MachineInformation::ARCH_ARM64],
            [MachineInformation::OS_DARWIN, MachineInformation::ARCH_ARM64],
        ];
    }

    /**
     * @dataProvider testConstructorDataProvider
     */
    public function testConstructor(string $os, string $arch): void
    {
        $machineInformation = new MachineInformation($os, $arch);

        $this->assertEquals($os, $machineInformation->getOperatingSystem());
        $this->assertEquals($arch, $machineInformation->getArchitecture());
    }

    public function testGetOperatingSystemDataProvider(): array
    {
        return [
            ['Linux', MachineInformation::OS_LINUX],
            ['Windows', MachineInformation::OS_WINDOWS],
            ['Darwin', MachineInformation::OS_DARWIN],
            ['Unknown', MachineInformation::OS_UNKNOWN],
        ];
    }

    /**
     * @dataProvider testGetOperatingSystemDataProvider
     */
    public function testGetOperatingSystem(string $uname, string $expected): void
    {
        $this->setFunctionReturn('php_uname', function ($arg) use ($uname) {
            return $arg === 's' ? $uname : php_uname($arg);
        }, true);

        $machineInformation = new MachineInformation();

        $this->assertEquals($expected, $machineInformation->getOperatingSystem());
    }

    public function testGetArchitectureDataProvider(): array
    {
        return [
            ['x86_64', MachineInformation::ARCH_X86_64],
            ['amd64', MachineInformation::ARCH_X86_64],
            ['arm64', MachineInformation::ARCH_ARM64],
            ['aarch64', MachineInformation::ARCH_ARM64],
            ['Unknown', MachineInformation::ARCH_UNKNOWN],
        ];
    }

    /**
     * @dataProvider testGetArchitectureDataProvider
     */
    public function testGetArchitecture(string $uname, string $expected): void
    {
        $this->setFunctionReturn('php_uname', function ($arg) use ($uname) {
            return $arg === 'm' ? $uname : php_uname($arg);
        }, true);

        $machineInformation = new MachineInformation();

        $this->assertEquals($expected, $machineInformation->getArchitecture());
    }

    public function testIsWindows(): void
    {
        $mockUname = 'linux';
        $this->setFunctionReturn('php_uname', function ($arg) use (&$mockUname) {
            return $arg === 's' ? $mockUname : php_uname($arg);
        }, true);

        $machineInformation = new MachineInformation();

        $this->assertFalse($machineInformation->isWindows());

        $mockUname = 'windows';
        $machineInformation = new MachineInformation();

        $this->assertTrue($machineInformation->isWindows());
    }
}

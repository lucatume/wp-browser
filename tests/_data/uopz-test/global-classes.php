<?php

class SomeGlobalClassOne
{
    const EXISTING_CONSTANT = 'test-constant';

    public function getValueOne(): string
    {
        return 'original-value-one';
    }

    public function getValueTwo(): string
    {
        return 'original-value-two';
    }

    public function getValueThree(): string
    {
        return 'original-value-three';
    }

    public static function getStaticValueOne(): string
    {
        return 'original-static-value-one';
    }

    public static function getStaticValueTwo(): string
    {
        return 'original-static-value-two';
    }

    public static function getStaticValueThree(): string
    {
        return 'original-static-value-three';
    }

    public function modifyValueByReference(array &$input): void
    {
        $input[] = 'foo';
    }

    public static function modifyStaticValueByReference(array &$input): void
    {
        $input[] = 'foo';
    }

    public static function someStaticHeaderProxy(
        string $header,
        bool $replace = true,
        int $http_response_code = 0
    ): void {
        header($header, $replace, $http_response_code);
    }

    public function someHeaderProxy(string $header, bool $replace = true, int $http_response_code = 0): void
    {
        header($header, $replace, $http_response_code);
    }
}

class SomeGlobalClassTwo
{
    public function getValueOne(): string
    {
        return 'another-value';
    }
}

final class SomeGlobalFinalClass
{
    public function someMethod(): int
    {
        return 23;
    }
}

class SomeGlobalClassWithFinalMethods
{
    public final function someFinalMethod(): int
    {
        return 23;
    }

    public static final function someStaticFinalMethod(): int
    {
        return 89;
    }
}

class SomeGlobalClassWithoutMethods
{
    private int $number = 23;
    private static string $name = 'Luca';
}

class SomeGlobalClassWithStaticVariables
{
    public function theCounter(): int
    {
        static $counter = 0;
        static $step = 2;
        $oldValue = $counter;
        $counter += $step;
        return $oldValue;
    }

    public static function theStaticCounter(): int
    {
        static $counter = 0;
        static $step = 2;
        $oldValue = $counter;
        $counter += $step;
        return $oldValue;
    }
}

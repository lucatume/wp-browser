<?php

namespace {

    const EXISTING_CONSTANT = 'test-constant';

    function someTestFunction(): string
    {
        return 'test-test-test';
    }

    function someConcatenation(string $arg1, string $arg2): string
    {
        return $arg1 . $arg2;
    }

    function someReferenceFunction(array &$input): void
    {
        $input[] = 'foo';
    }

    function someHeaderProxy(string $header, bool $replace = true, int $http_response_code = 0): void
    {
        header($header, $replace, $http_response_code);
    }

    function withStaticVariable(): int
    {
        static $counter = 0;
        static $step = 2;
        $oldValue = $counter;
        $counter += $step;
        return $oldValue;
    }

    function withStaticVariableTwo(): int
    {
        static $counter = 0;
        static $step = 2;
        $oldValue = $counter;
        $counter += $step;
        return $oldValue;
    }
}

namespace lucatume\WPBrowser\Acme\Project {

    const EXISTING_CONSTANT = 'test-constant';

    function testFunction(): string
    {
        return 'test-test-test';
    }

    function testConcatenation(string $arg1, string $arg2): string
    {
        return $arg1 . $arg2;
    }

    function someReferenceFunction(array &$input): void
    {
        $input[] = 'foo';
    }

    function someHeaderProxy(string $header, bool $replace = true, int $http_response_code = 0): void
    {
        header($header, $replace, $http_response_code);
    }

    function withStaticVariable(): int
    {
        static $counter = 0;
        static $step = 2;
        $oldValue = $counter;
        $counter += $step;
        return $oldValue;
    }
}

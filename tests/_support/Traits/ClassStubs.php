<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Tests\StubFactory;

trait ClassStubs
{
    /**
     * @after
     */
    public function tearDownClassStubs(): void
    {
        StubFactory::tearDown();
    }

    protected function makeEmptyClass(string $class, array $parameters): string
    {
        return StubFactory::makeEmptyClass($class, $parameters);
    }
}

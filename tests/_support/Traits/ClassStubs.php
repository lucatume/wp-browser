<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Tests\StubClassFactory;

trait ClassStubs
{
    /**
     * @after
     */
    public function tearDownClassStubs(): void
    {
        StubClassFactory::tearDown();
    }

    protected function makeEmptyClass(string $class, array $parameters): string
    {
        return StubClassFactory::makeEmptyClass($class, $parameters);
    }
}

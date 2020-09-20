<?php

namespace Codeception\Lib\Generator;

interface GeneratorInterface
{
    /**
     * Produces the rendered template.
     *
     * @return string
     */
    public function produce();
}

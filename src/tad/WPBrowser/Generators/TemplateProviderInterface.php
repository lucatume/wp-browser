<?php

namespace tad\WPBrowser\Generators;

interface TemplateProviderInterface
{

    public function __construct(array $data = [ ]);

    public function getContents();
}

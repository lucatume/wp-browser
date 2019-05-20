<?php

namespace tad\WPBrowser\Generators;

use Handlebars\Handlebars;

interface TemplateProviderInterface
{

    public function __construct(Handlebars $handlebars, array $data = [ ]);

    public function getContents();
}

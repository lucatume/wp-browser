<?php

namespace Codeception\Lib\Generator;


class AcceptanceSuiteConfig extends AbstractGenerator implements GeneratorInterface
{
    public static $requiredSettings = ['className', 'namespace', 'actor'];

    protected $template = <<< YAML
class_name: {{className}}
modules:
    enabled:
        - \\{{namespace}}Helper\\{{actor}}
        - WPBrowser:
            url: 'http://wp.local'
            adminUsername: admin
            adminPassword: password
            adminPath: /wp-admin
YAML;
}
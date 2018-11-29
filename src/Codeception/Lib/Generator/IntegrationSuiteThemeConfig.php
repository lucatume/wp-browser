<?php

namespace Codeception\Lib\Generator;

class IntegrationSuiteThemeConfig extends AbstractGenerator implements GeneratorInterface
{
    public static $requiredSettings = ['className', 'namespace', 'actor'];

    protected $template = <<< YAML
class_name: {{className}}
modules:
    enabled:
        - \\{{namespace}}Helper\\{{actor}}
        - WPLoader
    config:
        WPLoader:
            wpRootFolder: {{wpRootFolder}}
            dbName: {{dbName}}
            dbHost: {{dbHost}}
            dbUser: {{dbUser}}
            dbPassword: {{dbPassword}}
            tablePrefix: {{integrationTablePrefix}}
            domain: {{domain}}
            adminEmail: {{adminEmail}}
            title: WP Tests
            theme: {{theme}}
            plugins: {{plugins}}
            activatePlugins: {{plugins}}
            bootstrapActions: []
YAML;
}

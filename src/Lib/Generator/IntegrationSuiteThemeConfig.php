<?php

namespace lucatume\WPBrowser\Lib\Generator;

class IntegrationSuiteThemeConfig extends AbstractGenerator
{
    /**
     * @var array<string>
     */
    public static array $requiredSettings = ['className', 'namespace', 'actor'];

    /**
     * @var string
     */
    protected string $template = <<< YAML
actor: {{className}}
modules:
    enabled:
        - \\{{namespace}}Helper\\{{actor}}
        - \\lucatume\\WPBrowser\\Module\\WPLoader
    config:
        \\lucatume\\WPBrowser\\Module\\WPLoader:
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

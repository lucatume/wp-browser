<?php

namespace lucatume\WPBrowser\Lib\Generator;

class AcceptanceSuiteConfig extends AbstractGenerator
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
        - \\lucatume\\WPBrowser\\Module\\WPDb
        - \\lucatume\\WPBrowser\\Module\\WPBrowser
    config:
        \\lucatume\\WPBrowser\\Module\\WPDb:
            dsn: 'mysql:host={{dbHost}};dbname={{dbName}}'
            user: {{dbUser}}
            password: {{dbPassword}}
            dump: tests/_data/dump.sql
            populate: true
            cleanup: true
            url: '{{url}}'
            tablePrefix: {{tablePrefix}}
        \\lucatume\\WPBrowser\\Module\\WPBrowser:
            url: '{{url}}'
            adminUsername: {{adminUsername}}
            adminPassword: {{adminPassword}}
            adminPath: {{adminPath}}
YAML;
}

<?php

namespace Codeception\Lib\Generator;

class AcceptanceSuiteConfig extends AbstractGenerator implements GeneratorInterface
{
    /**
     * @var array<string>
     */
    public static $requiredSettings = ['className', 'namespace', 'actor'];

    /**
     * @var string
     */
    protected $template = <<< YAML
class_name: {{className}}
modules:
    enabled:
        - \\{{namespace}}Helper\\{{actor}}
        - WPDb
        - WPBrowser
    config:
        WPDb:
            dsn: 'mysql:host={{dbHost}};dbname={{dbName}}'
            user: {{dbUser}}
            password: {{dbPassword}}
            dump: tests/_data/dump.sql
            populate: true
            cleanup: true
            url: '{{url}}'
            tablePrefix: {{tablePrefix}}
        WPBrowser:
            url: '{{url}}'
            adminUsername: {{adminUsername}}
            adminPassword: {{adminPassword}}
            adminPath: {{adminPath}}
YAML;
}

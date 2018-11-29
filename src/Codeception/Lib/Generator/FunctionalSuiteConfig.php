<?php

namespace Codeception\Lib\Generator;

class FunctionalSuiteConfig extends AbstractGenerator implements GeneratorInterface
{
    public static $requiredSettings = ['className', 'namespace', 'actor'];

    protected $template = <<< YAML
class_name: {{className}}
modules:
    enabled:
        - \\{{namespace}}Helper\\{{actor}}
        - Filesystem
        - WPDb
        - WordPress
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
        WordPress:
            depends: WPDb
            wpRootFolder: {{wpRootFolder}}
            adminUsername: {{adminUsername}}
            adminPassword: {{adminPassword}}
YAML;
}

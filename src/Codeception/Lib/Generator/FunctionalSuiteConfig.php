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
            dsn: 'mysql:host={{dbHost}};dbname=wordpress-tests'
            user: root
            password: root
            dump: tests/_data/dump.sql
            populate: true
            cleanup: true
            url: 'http://wp.local'
            tablePrefix: wp_
        WordPress:
            depends: WPDb
            wpRootFolder: /var/www/wordpress
            adminUsername: admin
            adminPassword: password
YAML;
}
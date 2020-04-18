<?php namespace Codeception\Template;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use tad\WPBrowser\Utils\Map;

class WpbrowserTest extends \Codeception\Test\Unit
{

    public function dbInstallationDataProvider()
    {
        yield 'default' => [
            [],
            [
                'TEST_SITE_DSN'         => 'mysql:host=localhost;dbname=test',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => 'localhost',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];

        yield 'MySQL on localhost' => [
            [
            'testSiteDbHost'     => 'localhost',
            'testSiteDbName'     => 'wp',
            'testSiteDbUser'     => 'root',
            'testSiteDbPassword' => '',
            'testDbHost'         => 'localhost',
            'testDbName'         => 'wpTests',
            'testDbUser'         => 'root',
            'testDbPassword'     => '',
            ],
            [
                'TEST_SITE_DSN'         => 'mysql:host=localhost;dbname=wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => '',
                'TEST_DB_HOST'          => 'localhost',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => ''
            ]
        ];

        yield 'MySQL on IP Address' => [
            [
                'testSiteDbHost'     => '1.2.3.4',
                'testSiteDbName'     => 'wp',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => '',
                'testDbHost'         => '1.2.3.4',
                'testDbName'         => 'wpTests',
                'testDbUser'         => 'root',
                'testDbPassword'     => '',
            ],
            [
                'TEST_SITE_DSN'         => 'mysql:host=1.2.3.4;dbname=wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => '',
                'TEST_DB_HOST'          => '1.2.3.4',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => ''
            ]
        ];

        yield 'MySQL on IP Address w/ port' => [
            [
                'testSiteDbHost'     => '1.2.3.4:8989',
                'testSiteDbName'     => 'wp',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => 'password',
                'testDbHost'         => '1.2.3.4:8989',
                'testDbName'         => 'wpTests',
                'testDbUser'         => 'root',
                'testDbPassword'     => 'password',
            ],
            [
                'TEST_SITE_DSN'         => 'mysql:host=1.2.3.4;port=8989;dbname=wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => '1.2.3.4:8989',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];

        yield 'MySQL on unix socket' => [
            [
                'testSiteDbHost'     => '/var/mysql.sock',
                'testSiteDbName'     => 'tests',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => 'password',
                'testDbHost'         => '/var/mysql.sock',
                'testDbName'         => 'tests',
                'testDbUser'         => 'root',
                'testDbPassword'     => 'password',
            ],
            [
                'TEST_SITE_DSN'         => 'mysql:unix_socket=/var/mysql.sock;dbname=tests',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => 'localhost:/var/mysql.sock',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];

//      $dbInstallationData = [
//          'testSiteDbHost'     => 'localhost',
//          'testSiteDbName'     => 'wp',
//          'testSiteDbUser'     => 'root',
//          'testSiteDbPassword' => '',
//          'testDbHost'         => 'localhost',
//          'testDbName'         => 'wpTests',
//          'testDbUser'         => 'root',
//          'testDbPassword'     => '',
//      ];
    }

    /**
     * It should correctly scaffold db vars
     *
     * @test
     * @dataProvider dbInstallationDataProvider
     */
    public function should_correctly_scaffold_db_vars(
        $installationDataOverrides,
        $expected
    ) {
        $template         = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = ( array_merge($template->getDefaultInstallationData(), $installationDataOverrides) );

        $envVars = $template->getEnvFileVars(new Map($installationData));

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $envVars[ $key ]);
        }
    }
}

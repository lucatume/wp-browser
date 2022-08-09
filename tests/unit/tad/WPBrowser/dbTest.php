<?php namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Utils\Map;

class dbTest extends \Codeception\Test\Unit
{
    public function testDbDsnMapTypeDataProvider()
    {
        return [
            'empty'                                      => [
                '',
                [
                    'type'        => 'mysql',
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null
                ]
            ],
            'localhost'                                  => [
                'localhost',
                [
                    'type'        => 'mysql',
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null
                ]
            ],
            'localhost:4010'                             => [
                'localhost:4010',
                [
                    'type'        => 'mysql',
                    'host'        => 'localhost',
                    'port'        => 4010,
                    'unix_socket' => null
                ]
            ],
            'localhost:4010;dbname=test'                             => [
                'localhost:4010;dbname=test',
                [
                    'type'        => 'mysql',
                    'host'        => 'localhost',
                    'port'        => 4010,
                    'unix_socket' => null,
                    'dbname' => 'test'
                ]
            ],
            '192.168.0.10:4010'                          => [
                '192.168.0.10:4010',
                [
                    'type'        => 'mysql',
                    'host'        => '192.168.0.10',
                    'port'        => 4010,
                    'unix_socket' => null
                ]
            ],
            '192.168.0.10:4010;dbname=test'                          => [
                '192.168.0.10:4010;dbname=test',
                [
                    'type'        => 'mysql',
                    'host'        => '192.168.0.10',
                    'port'        => 4010,
                    'unix_socket' => null,
                    'dbname' => 'test'
                ]
            ],
            'localhost:/Users/luca/var/socks/mysql.sock' => [
                'localhost:/Users/luca/var/socks/mysql.sock',
                [
                    'type'        => 'mysql',
                    'host'        => null,
                    'port'        => null,
                    'unix_socket' => '/Users/luca/var/socks/mysql.sock'
                ]
            ],
            '/Users/luca/var/socks/mysql.sock'           => [
                '/Users/luca/var/socks/mysql.sock',
                [
                    'type'        => 'mysql',
                    'host'        => null,
                    'port'        => null,
                    'unix_socket' => '/Users/luca/var/socks/mysql.sock'
                ]
            ],
            'unix_socket=/var/mysql.sock'                => [
                '/var/mysql.sock',
                [
                    'type'        => 'mysql',
                    'host'        => null,
                    'port'        => null,
                    'unix_socket' => '/var/mysql.sock'
                ]
            ],
            'unix_socket=/var/mysql.sock;dbname=test'                => [
                '/var/mysql.sock;dbname=test',
                [
                    'type'        => 'mysql',
                    'host'        => null,
                    'port'        => null,
                    'unix_socket' => '/var/mysql.sock',
                    'dbname' => 'test'
                ]
            ],
            'sqlite:/opt/databases/mydb.sq3'             => [
                'sqlite:/opt/databases/mydb.sq3',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite',
                    'file'        => '/opt/databases/mydb.sq3'
                ]
            ],
            'sqlite:/opt/databases/mydb.sqlite'          => [
                'sqlite:/opt/databases/mydb.sqlite',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite',
                    'file'        => '/opt/databases/mydb.sqlite'
                ]
            ],
            'sqlite::memory:'                            => [
                'sqlite::memory:',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite',
                    'file'        => null,
                    'memory'      => true,
                ]
            ],
            'sqlite2:/opt/databases/mydb.sq2'            => [
                'sqlite2:/opt/databases/mydb.sq2',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite2',
                    'file'        => '/opt/databases/mydb.sq2',
                    'memory'      => null,
                ]
            ],
            'sqlite2:/opt/databases/mydb.sqlite'         => [
                'sqlite2:/opt/databases/mydb.sqlite',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite2',
                    'file'        => '/opt/databases/mydb.sqlite',
                    'memory'      => null,
                ]
            ],
            'sqlite2::memory:'                           => [
                'sqlite2::memory:',
                [
                    'host'        => 'localhost',
                    'port'        => null,
                    'unix_socket' => null,
                    'type'        => 'sqlite',
                    'version'     => 'sqlite2',
                    'file'        => null,
                    'memory'      => true,
                ]
            ],
            'container_name' => [
                'mysql:host=db;dbname=test',
                [
                    'type'   => 'mysql',
                    'host'   => 'db',
                    'dbname' => 'test'
                ]
            ]
        ];
    }

    /**
     * Test dbDsnMap
     * @dataProvider testDbDsnMapTypeDataProvider
     */
    public function test_dbDsnMap($input, $expected)
    {
        $map = dbDsnMap($input);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $map($key));
        }
    }

    public function testDbCredentialsDataProvider()
    {
        return [
            'empty'              => [
                '',
                '',
                '',
                [ 'dsn' => 'mysql:host=localhost', 'user' => 'root', 'password' => 'password' ]
            ],
            'user_and_pass_only' => [
                '',
                'luca',
                'secret',
                [ 'dsn' => 'mysql:host=localhost', 'user' => 'luca', 'password' => 'secret' ]
            ],
            'unix_socket'        => [
                'unix_socket=/var/mysql.sock',
                'luca',
                'secret',
                [ 'dsn' => 'mysql:unix_socket=/var/mysql.sock', 'user' => 'luca', 'password' => 'secret' ]
            ]
        ];
    }

    /**
     * Test dbCredentials
     *
     * @dataProvider testDbCredentialsDataProvider
     */
    public function test_db_credentials($dsn, $user, $pass, array $expected)
    {
        $creds = dbCredentials(dbDsnMap($dsn), $user, $pass);

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $creds($key));
        }
    }

    public function testdbDsnStringDataSet()
    {
        yield 'empty' => [
            [],
            false,
            'mysql:host=localhost'
        ];

        yield 'empty, for DB_HOST' => [
            [],
            true,
            'localhost'
        ];

        yield 'mysql on localhost' => [
            ['type' => 'mysql', 'host'=> 'localhost'],
            false,
            'mysql:host=localhost'
        ];

        yield 'mysql on localhost, for DB_HOST' => [
            ['type' => 'mysql', 'host'=> 'localhost'],
            true,
            'localhost'
        ];

        yield 'mysql w/ dbname on localhost' => [
            ['type' => 'mysql', 'host'=> 'localhost','dbname' => 'test'],
            false,
            'mysql:host=localhost;dbname=test'
        ];

        yield 'mysql w/ dbname on localhost, for DB_HOST' => [
            ['type' => 'mysql', 'host'=> 'localhost','dbname' => 'test'],
            true,
            'localhost'
        ];

        yield 'mysql on IP address'=>[
            ['type' => 'mysql', 'host'=> '1.2.3.4'],
            false,
            'mysql:host=1.2.3.4'
        ];

        yield 'mysql on IP address w/ dbname'=>[
            ['type' => 'mysql', 'host'=> '1.2.3.4', 'dbname' => 'test'],
            false,
            'mysql:host=1.2.3.4;dbname=test'
        ];

        yield 'mysql on IP address for DB_HOST'=>[
            ['type' => 'mysql', 'host'=> '1.2.3.4'],
            true,
            '1.2.3.4'
        ];

        yield 'mysql on IP address w/ dbname for DB_HOST'=>[
            ['type' => 'mysql', 'host'=> '1.2.3.4', 'dbname' => 'test'],
            true,
            '1.2.3.4'
        ];

        yield 'mysql on IP address w/ port' => [
            [ 'type' => 'mysql', 'host' => '1.2.3.4', 'port' => 2389 ],
            false,
            'mysql:host=1.2.3.4;port=2389'
        ];

        yield 'mysql on IP address w/ dbname and port' => [
            [ 'type' => 'mysql', 'host' => '1.2.3.4', 'port' => 2389, 'dbname' => 'test' ],
            false,
            'mysql:host=1.2.3.4;port=2389;dbname=test'
        ];

        yield 'mysql on IP address w/ port for DB_HOST' => [
            [ 'type' => 'mysql', 'host' => '1.2.3.4', 'port' => 2389 ],
            true,
            '1.2.3.4:2389'
        ];

        yield 'mysql on IP address w/ dbname and port for DB_HOST' => [
            [ 'type' => 'mysql', 'host' => '1.2.3.4', 'port' => 2389, 'dbname' => 'test' ],
            true,
            '1.2.3.4:2389'
        ];

        yield 'mysql on unix socket' =>[
            [ 'type' => 'mysql', 'unix_socket' => '/var/mysql.sock', 'dbname' => 'test' ],
            false,
            'mysql:unix_socket=/var/mysql.sock;dbname=test'
        ];

        yield 'mysql on unix socket for DB_HOST' =>[
            [ 'type' => 'mysql', 'unix_socket' => '/var/mysql.sock', 'dbname' => 'test' ],
            true,
            'localhost:/var/mysql.sock'
        ];
    }

    /**
     * Test dbDsnString
     * @dataProvider testdbDsnStringDataSet
     */
    public function test_dbDsnString($inputMap, $forDbHost, $expected)
    {
        $this->assertEquals($expected, dbDsnString(new Map($inputMap), $forDbHost));
    }

    public function dbDsnToMapDataProvider()
    {
        return [
            'mysql on localhost' => ['mysql:host=localhost', ['type' => 'mysql', 'host' => 'localhost']],
            'mysql on ip address' => ['mysql:host=1.2.3.4', ['type' => 'mysql', 'host' => '1.2.3.4']],
            'mysql from container' => ['mysql:host=db', ['type' => 'mysql', 'host' => 'db']],
            'mysql from container 2' => ['mysql:host=mysql', ['type' => 'mysql', 'host' => 'mysql']],
            'mysql on unix socket' => ['mysql:unix_socket=/var/mysql.sock', ['type' => 'mysql', 'host' => 'localhost', 'unix_socket' => '/var/mysql.sock']],
            'sqlite file' => ['sqlite:/var/db.sqlite', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite', 'file' => '/var/db.sqlite']],
            'sqlite memory' => ['sqlite::memory:', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite', 'memory' => true]],
            'sqlite 2 file' => ['sqlite2:/var/db.sqlite', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite2', 'file' => '/var/db.sqlite']],
            'sqlite 2 memory' => ['sqlite2::memory:', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite2', 'memory' => true]],
            'sqlite 3 file' => ['sqlite3:/var/db.sqlite', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite3', 'file' => '/var/db.sqlite']],
            'sqlite 3 memory' => ['sqlite3::memory:', ['type' => 'sqlite', 'host' => 'localhost', 'version' => 'sqlite3', 'memory' => true]],
        ];
    }
    /**
     * Test dbDsnToMap
     * @dataProvider dbDsnToMapDataProvider
     */
    public function test_db_dsn_to_map($input, $expected)
    {
        $this->assertEquals($expected, dbDsnToMap($input)->toArray());
    }

    public function dbDsnToMapBadInputDataProvider()
    {
        return [
           'empty' => [''],
           'localhost' => ['localhost'],
           'localhost and port' => ['localhost:23'],
           'ip address' => ['1.2.3.4'],
           'ip address and port' => ['1.2.3.4:2389'],
           'mysql unix socket' => ['/var/mysql.sock'],
           'sqlite file' => ['/var/db.sqlite'],
        ] ;
    }
    /**
     * Test dbDsnToMap will throw if string is not DSN string
     * @dataProvider dbDsnToMapBadInputDataProvider
     */
    public function test_db_dsn_to_map_will_throw_if_string_is_not_dsn_string($input)
    {
        $this->expectException(\InvalidArgumentException::class);

        dbDsnToMap($input);
    }
}

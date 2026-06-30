<?php

namespace Unit\lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\ParallelRun\WorkerEnv;
use lucatume\WPBrowser\Utils\Filesystem as FS;

/**
 * @group fast
 */
class WorkerEnvTest extends Unit
{
    public function test_worker_0_keeps_base_ports_and_exposes_id(): void
    {
        $env = WorkerEnv::build(0, []);

        $this->assertSame('2389', $env['WORDPRESS_LOCALHOST_PORT']);
        $this->assertSame('2390', $env['CHROMEDRIVER_PORT']);
        $this->assertSame('var/tmp', $env['TEST_TMP_ROOT_DIR']);
        $this->assertSame('var/tmp/_cache', $env['TEST_CACHE_DIR']);
        $this->assertSame('0', $env['WPBROWSER_WORKER_ID']);
        $this->assertArrayNotHasKey('WORDPRESS_ROOT_DIR', $env);
    }

    public function test_worker_3_shifts_ports_by_stride_times_three(): void
    {
        $env = WorkerEnv::build(3, []);

        $this->assertSame('2419', $env['WORDPRESS_LOCALHOST_PORT']);
        $this->assertSame('2420', $env['CHROMEDRIVER_PORT']);
        $this->assertSame('var/tmp', $env['TEST_TMP_ROOT_DIR']);
        $this->assertSame('3', $env['WPBROWSER_WORKER_ID']);
    }

    public function test_port_bearing_urls_and_dsns_are_rewritten_from_dotenv(): void
    {
        $tmp = FS::tmpDir('worker-env-', ['.env' => implode("\n", [
            'WORDPRESS_URL=http://localhost:2389',
            'WORDPRESS_DOMAIN=localhost:2389',
            'WORDPRESS_SUBDIR_URL=http://localhost:2389/subdir',
            'WORDPRESS_SUBDOMAIN_URL=http://test1.localhost:2389',
            'WORDPRESS_DB_NAME=wordpress',
            'WORDPRESS_DB_DSN=mysql:host=127.0.0.1;port=2391;dbname=wordpress',
            'WORDPRESS_DB_HOST=127.0.0.1:2391',
            'WORDPRESS_DB_URL=mysql://root:password@127.0.0.1:2391/wordpress',
            'WORDPRESS_SUBDIR_DB_NAME=test_subdir',
            'WORDPRESS_SUBDIR_DB_DSN=mysql:host=127.0.0.1;port=2391;dbname=test_subdir',
            'WORDPRESS_SUBDOMAIN_DB_NAME=test_subdomain',
            'WORDPRESS_SUBDOMAIN_DB_DSN=mysql:host=127.0.0.1;port=2391;dbname=test_subdomain',
            'WORDPRESS_EMPTY_DB_NAME=empty',
            'WORDPRESS_EMPTY_DB_DSN=mysql:host=127.0.0.1;port=2391;dbname=empty',
            '',
        ])]);

        $env = WorkerEnv::build(2, [], $tmp . '/.env');

        // HTTP URLs are rewritten per-worker (port changed to per-worker PHP server port)
        $this->assertSame('http://localhost:2409', $env['WORDPRESS_URL']);
        $this->assertSame('localhost:2409', $env['WORDPRESS_DOMAIN']);
        $this->assertSame('http://localhost:2409/subdir', $env['WORDPRESS_SUBDIR_URL']);
        $this->assertSame('http://test1.localhost:2409', $env['WORDPRESS_SUBDOMAIN_URL']);

        // Database DSNs: host/port unchanged (shared MySQL), but dbname suffixed per-worker
        $this->assertSame('mysql:host=127.0.0.1;port=2391;dbname=wordpress_w2', $env['WORDPRESS_DB_DSN']);
        $this->assertSame('127.0.0.1:2391', $env['WORDPRESS_DB_HOST']);
        $this->assertSame('mysql://root:password@127.0.0.1:2391/wordpress_w2', $env['WORDPRESS_DB_URL']);
        $this->assertSame('mysql:host=127.0.0.1;port=2391;dbname=test_subdir_w2', $env['WORDPRESS_SUBDIR_DB_DSN']);
        $this->assertSame('mysql:host=127.0.0.1;port=2391;dbname=test_subdomain_w2', $env['WORDPRESS_SUBDOMAIN_DB_DSN']);
        $this->assertSame('mysql:host=127.0.0.1;port=2391;dbname=empty_w2', $env['WORDPRESS_EMPTY_DB_DSN']);

        // Database names are suffixed per-worker
        $this->assertSame('wordpress_w2', $env['WORDPRESS_DB_NAME']);
        $this->assertSame('test_subdir_w2', $env['WORDPRESS_SUBDIR_DB_NAME']);
        $this->assertSame('test_subdomain_w2', $env['WORDPRESS_SUBDOMAIN_DB_NAME']);
        $this->assertSame('empty_w2', $env['WORDPRESS_EMPTY_DB_NAME']);
    }

    public function test_dotenv_values_override_base_env(): void
    {
        $tmp = FS::tmpDir('worker-env-override-', ['.env' => "CUSTOM_KEY=from-dotenv\nWPBROWSER_VERSION=9.9.9\n"]);

        $env = WorkerEnv::build(0, ['CUSTOM_KEY' => 'from-base'], $tmp . '/.env');

        $this->assertSame('from-dotenv', $env['CUSTOM_KEY']);
        $this->assertSame('9.9.9', $env['WPBROWSER_VERSION']);
    }

    public function test_base_env_values_are_preserved_when_not_in_dotenv(): void
    {
        $env = WorkerEnv::build(0, ['PATH' => '/usr/bin', 'HOME' => '/home/x']);

        $this->assertSame('/usr/bin', $env['PATH']);
        $this->assertSame('/home/x', $env['HOME']);
    }

    public function test_missing_dotenv_file_is_tolerated(): void
    {
        $env = WorkerEnv::build(1, ['FOO' => 'bar'], '/does/not/exist/.env');

        $this->assertSame('bar', $env['FOO']);
        $this->assertSame('2399', $env['WORDPRESS_LOCALHOST_PORT']);
    }

    public function test_non_scalar_base_env_values_are_dropped(): void
    {
        $env = WorkerEnv::build(0, [
            'OK'     => 'value',
            'ARRAY'  => ['a', 'b'],
            'OBJECT' => new \stdClass(),
            'NULLY'  => null,
        ]);

        $this->assertSame('value', $env['OK']);
        $this->assertSame('', $env['NULLY']);
        $this->assertArrayNotHasKey('ARRAY', $env);
        $this->assertArrayNotHasKey('OBJECT', $env);
    }

    public function test_tmp_and_cache_dirs_keep_base_value_from_env(): void
    {
        $env = WorkerEnv::build(2, [
            'TEST_TMP_ROOT_DIR' => '/custom/tmp',
            'TEST_CACHE_DIR'    => '/custom/cache',
        ]);

        $this->assertSame('/custom/tmp', $env['TEST_TMP_ROOT_DIR']);
        $this->assertSame('/custom/cache', $env['TEST_CACHE_DIR']);
        $this->assertSame('2', $env['WPBROWSER_WORKER_ID']);
    }

    public function test_overrides_for_worker_0_uses_base_ports(): void
    {
        $tokens = WorkerEnv::overridesForWorker(0);
        $values = $this->overrideValues($tokens);

        $this->assertNotEmpty($values);
        $this->assertTrue($this->anyContains($values, 'ChromeDriverController') && $this->anyContains($values, 'port: 2390'));
        $this->assertTrue($this->anyContains($values, 'BuiltInServerController') && $this->anyContains($values, 'port: 2389'));
    }

    public function test_overrides_for_worker_shifts_ports_by_stride(): void
    {
        $tokens = WorkerEnv::overridesForWorker(3);
        $values = $this->overrideValues($tokens);

        $this->assertTrue($this->anyContains($values, 'ChromeDriverController') && $this->anyContains($values, 'port: 2420'));
        $this->assertTrue($this->anyContains($values, 'BuiltInServerController') && $this->anyContains($values, 'port: 2419'));
    }

    public function test_overrides_are_splattable_cli_tokens(): void
    {
        $tokens = WorkerEnv::overridesForWorker(2);

        $this->assertNotEmpty($tokens);
        $this->assertSame(0, count($tokens) % 2, 'tokens come in --override/value pairs');
        for ($i = 0, $n = count($tokens); $i < $n; $i += 2) {
            $this->assertSame('--override', $tokens[$i]);
            $this->assertIsString($tokens[$i + 1]);
            $this->assertNotSame('', $tokens[$i + 1]);
        }
    }

    public function test_override_values_contain_extension_fqcns_and_prefix(): void
    {
        $values = $this->overrideValues(WorkerEnv::overridesForWorker(0));

        $joined = implode("\n", $values);
        $this->assertStringContainsString('lucatume\\WPBrowser\\Extension\\ChromeDriverController', $joined);
        $this->assertStringContainsString('lucatume\\WPBrowser\\Extension\\BuiltInServerController', $joined);
    }

    public function test_allocated_ports_override_stride_defaults(): void
    {
        $ports = [
            'WORDPRESS_LOCALHOST_PORT' => 55001,
            'CHROMEDRIVER_PORT'        => 55002,
        ];
        $env = WorkerEnv::build(2, [], null, $ports);

        $this->assertSame('55001', $env['WORDPRESS_LOCALHOST_PORT']);
        $this->assertSame('55002', $env['CHROMEDRIVER_PORT']);

        $tokens = WorkerEnv::overridesForWorker(2, $ports);
        $joined = implode("\n", $tokens);
        $this->assertStringContainsString('port: 55001', $joined);
        $this->assertStringContainsString('port: 55002', $joined);
    }

    /**
     * @param string[] $tokens
     * @return string[]
     */
    private function overrideValues(array $tokens): array
    {
        $out = [];
        for ($i = 0, $n = count($tokens); $i < $n; $i += 2) {
            if (($tokens[$i] ?? null) === '--override' && isset($tokens[$i + 1])) {
                $out[] = $tokens[$i + 1];
            }
        }
        return $out;
    }

    /**
     * @param string[] $values
     */
    private function anyContains(array $values, string $needle): bool
    {
        foreach ($values as $v) {
            if (strpos($v, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\WordPress\Installation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;
use Throwable;

class DevRebuild extends Command implements CustomCommandInterface
{
    use ServiceExtensionsTrait;

    public static function getCommandName(): string
    {
        return 'dev:rebuild';
    }

    public function getDescription(): string
    {
        return 'Builds the WordPress installation used by the default configuration ' .
            '(tests/_wordpress), e.g. on a fresh clone or in CI.';
    }

    protected function configure(): void
    {
        $this->addOption(
            'wp-version',
            null,
            InputOption::VALUE_REQUIRED,
            'The WordPress version to install (e.g. "6.8.1"). Defaults to the WORDPRESS_VERSION ' .
            'environment value, or "latest".'
        );
    }

    /**
     * Resolves the WordPress version to install, in order of precedence: the --wp-version option,
     * the WORDPRESS_VERSION environment value recorded by `init`, then "latest".
     */
    public static function resolveVersion(?string $optionVersion, string|false|null $envVersion): string
    {
        if (is_string($optionVersion) && $optionVersion !== '') {
            return $optionVersion;
        }

        if (is_string($envVersion) && $envVersion !== '') {
            return $envVersion;
        }

        return 'latest';
    }

    /**
     * Resolves the WordPress root and SQLite database directory from the BuiltInServerController config.
     *
     * @param array<string,mixed> $serverConfig
     *
     * @return array{0: string, 1: string, 2: string} [wpRootDir, dataDir, url]
     */
    public static function resolveTarget(array $serverConfig, string $rootDir): array
    {
        $docRoot = $serverConfig['docroot'] ?? null;
        $env = $serverConfig['env'] ?? [];
        $isSqlite = is_array($env)
            && (($env['DATABASE_TYPE'] ?? null) === 'sqlite' || ($env['DB_ENGINE'] ?? null) === 'sqlite');

        if (!is_string($docRoot) || $docRoot === '' || !$isSqlite) {
            throw new RuntimeException(
                'The dev:rebuild command only supports the default SQLite configuration produced by ' .
                '`vendor/bin/codecept init wpbrowser`. Set up your WordPress installation manually.'
            );
        }

        $wpRootDir = rtrim(Path::isAbsolute($docRoot) ? $docRoot : $rootDir . '/' . $docRoot, '/');
        // The default configuration keeps the SQLite database next to the installation, in a data sub-directory.
        $dataDir = $wpRootDir . '/data';

        $port = $serverConfig['port'] ?? null;
        $url = is_numeric($port) ? "http://localhost:$port" : 'http://localhost';

        return [$wpRootDir, $dataDir, $url];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootDir = rtrim(codecept_root_dir(), '/');
        $serverConfig = $this->getServiceExtensionConfig(BuiltInServerController::class);

        try {
            [$wpRootDir, $dataDir, $url] = self::resolveTarget($serverConfig, $rootDir);
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        if (is_file($wpRootDir . '/wp-load.php')) {
            $output->writeln("WordPress is already installed in <info>$wpRootDir</info>.");
            $output->writeln('Delete that directory and run this command again to rebuild it.');
            return 0;
        }

        $optionVersion = $input->getOption('wp-version');
        $version = self::resolveVersion(
            is_string($optionVersion) ? $optionVersion : null,
            Env::get('WORDPRESS_VERSION')
        );

        try {
            $output->writeln("Installing WordPress <info>$version</info> in <info>$wpRootDir</info> ...");
            Installation::scaffoldWithSqlite($wpRootDir, $dataDir, $url, 'Test', $version);
        } catch (Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        $output->writeln('<info>WordPress installed.</info>');
        $output->writeln('The database state is restored from the WPDb dump when tests run.');

        return 0;
    }
}

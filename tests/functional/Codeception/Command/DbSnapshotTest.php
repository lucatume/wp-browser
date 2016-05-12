<?php
namespace Codeception\Command\Tests\Functional;


use Codeception\Command\DbSnapshot;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DbSnapshotTest extends \Codeception\TestCase\Test
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @test
     * it should allow specifying local and dist domain
     */
    public function it_should_allow_specifying_local_and_dist_domain()
    {
        $root = vfsStream::setup('dumps');
        $root->addChild(new vfsStreamFile('dump.sql'));
        $root->addChild(new vfsStreamFile('dump.dist.sql'));

        $dumpFile = $root->url() . '/dump.sql';
        $distDumpFile = $root->url() . '/dump.dist.sql';

        $application = new Application();
        $application->add(new DbSnapshot());

        $localDomain = 'http://codeception-acceptance.dev';
        $distDomain = 'http://dist.dev';

        $command = $application->find('db:snapshot');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'codeception-acceptance',
            'snapshot' => 'issue4455',
            '--dump-file' => $dumpFile,
            '--dist-dump-file' => $distDumpFile,
            '--local-url' => $localDomain,
            '--dist-url' => $distDomain
        ]);

        $distContents = file_get_contents($dumpFile);
        $distDumpContents = file_get_contents($distDumpFile);
        $localDomainCount = preg_match_all('~' . preg_quote($localDomain) . '~', $distContents);
        $distDomainCount = preg_match_all('~' . preg_quote($distDomain) . '~', $distDumpContents);
        $this->assertEquals($localDomainCount, $distDomainCount);
    }

    /**
     * @test
     * it should replace subdomain occurrences too
     */
    public function it_should_replace_subdomain_occurrences_too()
    {
        $root = vfsStream::setup('dumps');
        $root->addChild(new vfsStreamFile('dump.sql'));
        $root->addChild(new vfsStreamFile('dump.dist.sql'));

        $dumpFile = $root->url() . '/dump.sql';
        $distDumpFile = $root->url() . '/dump.dist.sql';

        $application = new Application();
        $application->add(new DbSnapshot());

        $localDomain = 'http://codeception-acceptance.dev';
        $distDomain = 'http://dist.dev';

        $command = $application->find('db:snapshot');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'codeception-acceptance',
            'snapshot' => 'issue4455',
            '--dump-file' => $dumpFile,
            '--dist-dump-file' => $distDumpFile,
            '--local-url' => $localDomain,
            '--dist-url' => $distDomain
        ]);

        $distContents = file_get_contents($dumpFile);
        $distDumpContents = file_get_contents($distDumpFile);
        $localDomainCount = preg_match_all('~' . preg_quote('codeception-acceptance.dev') . '~', $distContents);
        $distDomainCount = preg_match_all('~' . preg_quote('dist.dev') . '~', $distDumpContents);
        $this->assertEquals($localDomainCount, $distDomainCount);
    }
}

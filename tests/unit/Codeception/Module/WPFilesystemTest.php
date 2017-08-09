<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use PHPUnit_Framework_AssertionFailedError;
use tad\WPBrowser\Filesystem\Utils;

class WPFilesystemTest extends \Codeception\Test\Unit {

    /**
     * @var \Codeception\Lib\ModuleContainer
     */
    protected $moduleContainer;
    protected $config;
    protected $sandbox;
    protected $backupGlobals = false;
    protected $nowUploads;

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function _before() {
        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
    }

    /**
     * It should be instantiatable
     *
     * @test
     */
    public function be_instantiatable() {
        $this->assertInstanceOf(WPFilesystem::class, $this->make_instance());
    }

    /**
     * @return WPFilesystem
     */
    protected function make_instance() {
        $this->config = null !== $this->config
            ? $this->config
            : $this->getDefaultConfig();
        $instance = new WPFilesystem($this->moduleContainer->reveal(), $this->config);
        $instance->_initialize();

        return $instance;
    }

    protected function getDefaultConfig(array $overrides = []) {
        $wpFolder = isset($overrides['wp']) ? $overrides['wp'] : '';

        $this->sandbox = codecept_output_dir('sandbox' . $wpFolder);

        rrmdir($this->sandbox);

        mkdir($this->sandbox . $wpFolder, 0777, true);
        file_put_contents($this->sandbox . '/wp-load.php', '<?php //silence is golden;');

        $themes = isset($overrides['themes']) ? $overrides['themes'] : '/wp-content/themes';
        $plugins = isset($overrides['plugins']) ? $overrides['plugins'] : '/wp-content/plugins';
        $muPlugins = isset($overrides['mu-plugins']) ? $overrides['mu-plugins'] : '/wp-content/mu-plugins';
        $uploads = isset($overrides['uploads']) ? $overrides['uploads'] : "/wp-content/uploads";

        mkdir($this->sandbox . $themes, 0777, true);
        mkdir($this->sandbox . $plugins, 0777, true);
        mkdir($this->sandbox . $muPlugins, 0777, true);
        $Y = date('Y');
        $m = date('m');
        $nowUploads = $this->sandbox . "{$uploads}/{$Y}/{$m}";
        $this->nowUploads = $nowUploads;
        mkdir($nowUploads, 0777, true);

        return [
            'wpRootFolder' => $this->sandbox,
            'themes'       => $themes,
            'plugins'      => $plugins,
            'mu-plugins'   => $muPlugins,
            'uploads'      => $uploads,
        ];
    }

    /**
     * It should throw if wpRootFolder param is missing
     *
     * @test
     */
    public function it_should_throw_if_wp_root_folder_param_is_missing() {
        $this->config = [];

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    /**
     * It should only require the wpRootFolder path parameter and default the other parameters
     *
     * @test
     */
    public function it_should_only_require_the_wp_root_folder_path_parameter_and_default_the_other_parameters() {
        $config = $this->getDefaultConfig();
        $wpRoot = $config['wpRootFolder'];
        $this->config = ['wpRootFolder' => $wpRoot];

        $sut = $this->make_instance();
        $sut->_initialize();

        $moduleConfig = $sut->_getConfig();
        $this->assertEquals(Utils::untrailslashit($wpRoot) . '/', $moduleConfig['wpRootFolder']);
        $this->assertEquals($wpRoot . '/wp-content/themes/', $moduleConfig['themes']);
        $this->assertEquals($wpRoot . '/wp-content/plugins/', $moduleConfig['plugins']);
        $this->assertEquals($wpRoot . '/wp-content/mu-plugins/', $moduleConfig['mu-plugins']);
        $this->assertEquals($wpRoot . '/wp-content/uploads/', $moduleConfig['uploads']);
    }

    /**
     * It should allow passing optional parameters as relative paths
     *
     * @test
     */
    public function it_should_allow_passing_optional_parameters_as_relative_paths() {
    }

    public function optionalRequiredPathParameters() {
        return [
            ['themes'],
            ['plugins'],
            ['mu-plugins'],
            ['uploads'],
        ];
    }

    /**
     * It should allow specifying wpRootFolder as relative path to the project root
     *
     * @test
     */
    public function it_should_allow_specifying_wp_root_folder_as_relative_path_to_the_project_root() {
        $this->config = [
            'wpRootFolder' => '/tests/_output/sandbox',
        ];

        $this->getDefaultConfig();
        $sut = $this->make_instance();
        $sut->_initialize();
        $this->assertEquals(codecept_output_dir('sandbox/'), $sut->_getConfig('wpRootFolder'));
    }

    /**
     * It should allow specifying optional path parameters as relative paths
     *
     * @test
     * @dataProvider optionalRequiredPathParameters
     */
    public function it_should_allow_specifying_optional_path_parameters_as_relative_paths($parameter) {
        $config = $this->getDefaultConfig();
        $path = $config['wpRootFolder'] . '/foo/';
        mkdir($path, 0777, true);

        $this->config = [
            'wpRootFolder' => $config['wpRootFolder'],
            $parameter     => 'foo',
        ];

        $sut = $this->make_instance();
        $sut->_initialize();
        $this->assertEquals($path, $sut->_getConfig($parameter));
    }

    /**
     * It should allow being in the uploads path
     *
     * @test
     */
    public function it_should_allow_being_in_the_uploads_path() {
        $sut = $this->make_instance();

        $sut->amInUploadsPath();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        $this->assertEquals($uploadsPath, getcwd());
    }

    /**
     * It should allow being in an uploads subfolder
     *
     * @test
     */
    public function it_should_allow_being_in_an_uploads_subfolder() {
        $sut = $this->make_instance();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        mkdir($uploadsPath . '/foo', 0777, true);
        mkdir($uploadsPath . '/2017/04', 0777, true);

        $sut->amInUploadsPath('foo');

        $this->assertEquals($uploadsPath . '/foo', getcwd());

        $sut->amInUploadsPath('2017/04');

        $this->assertEquals($uploadsPath . '/2017/04', getcwd());
    }

    /**
     * It should allow being in an uploads path year/month subfolder from date
     *
     * @test
     */
    public function it_should_allow_being_in_an_uploads_path_year_month_subfolder_from_date() {
        $sut = $this->make_instance();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        $Y = date('Y');
        $m = date('m');
        $expected = $uploadsPath . "/{$Y}/{$m}";
        $nextMonthY = date('Y', strtotime('next month'));
        $nextMonthM = date('m', strtotime('next month'));
        $nextMonth = $uploadsPath . "/{$nextMonthY}/{$nextMonthM}";

        mkdir($nextMonth, 0777, true);

        $sut->amInUploadsPath('now');

        $this->assertEquals($expected, getcwd());

        $sut->amInUploadsPath('next month');

        $this->assertEquals($nextMonth, getcwd());
    }

    /**
     * It should being in time based uploads folder with Unix timestamp
     *
     * @test
     */
    public function it_should_being_in_time_based_uploads_folder_with_unix_timestamp() {
        $sut = $this->make_instance();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        $time = strtotime('next month');
        $Y = date('Y', $time);
        $m = date('m', $time);
        $expected = $uploadsPath . "/{$Y}/{$m}";

        mkdir($expected, 0777, true);

        $sut->amInUploadsPath($time);

        $this->assertEquals($expected, getcwd());
    }

    /**
     * It should allow seeing uploaded files
     *
     * @test
     */
    public function it_should_allow_seeing_uploaded_files() {
        $sut = $this->make_instance();

        file_put_contents($this->nowUploads . '/file.txt', 'foo bar');

        $sut->seeUploadedFileFound(str_replace($this->config['wpRootFolder'] . $this->config['uploads'], '', $this->nowUploads) . '/file.txt');
        $sut->dontSeeUploadedFileFound('file.txt');

        $this->expectException(PHPUnit_Framework_AssertionFailedError::class);

        $sut->seeUploadedFileFound('some-other-file.txt');
        $sut->dontSeeUploadedFileFound('some-other-file.txt');
    }

    /**
     * It should allow to see a file in the uploads folder based on the date
     *
     * @test
     */
    public function it_should_allow_to_see_a_file_in_the_uploads_folder_based_on_the_date() {
        $sut = $this->make_instance();

        file_put_contents($this->nowUploads . '/file.txt', 'foo bar');

        $sut->seeUploadedFileFound('file.txt', time());
        $sut->dontSeeUploadedFileFound('file.txt', 'somewhere/else');

        $this->expectException(PHPUnit_Framework_AssertionFailedError::class);

        $sut->seeUploadedFileFound('some-other-file.txt', 'now');
        $sut->dontSeeUploadedFileFound('some-other-file.txt', 'somewhere/else');
    }

    /**
     * It should allow to see in an uploaded file contents
     *
     * @test
     */
    public function it_should_allow_to_see_in_an_uploaded_file_contents() {
        $sut = $this->make_instance();

        file_put_contents($this->nowUploads . '/file.txt', 'foo bar');

        $dateFrag = str_replace($this->config['wpRootFolder'] . $this->config['uploads'], '', $this->nowUploads);
        $sut->seeInUploadedFile($dateFrag . '/file.txt', 'foo bar');
        $sut->dontSeeInUploadedFile($dateFrag . '/file.txt', 'nope');

        $this->expectException(PHPUnit_Framework_AssertionFailedError::class);

        $sut->seeInUploadedFile('some-other-file.txt', 'foo');
        $sut->dontSeeInUploadedFile('some-other-file.txt', 'foo');
    }

    /**
     * It should allow to see an uploaded file content based on the date
     *
     * @test
     */
    public function it_should_allow_to_see_an_uploaded_file_content_based_on_the_date() {
        $sut = $this->make_instance();

        file_put_contents($this->nowUploads . '/file.txt', 'foo bar');

        $sut->seeInUploadedFile('file.txt', 'foo bar', 'now');
        $sut->dontSeeInUploadedFile('file.txt', 'nope', 'now');

        $this->expectException(PHPUnit_Framework_AssertionFailedError::class);

        $sut->seeInUploadedFile('some-other-file.txt', 'foo', 'now');
        $sut->dontSeeInUploadedFile('some-other-file.txt', 'foo', 'now');
    }

    /**
     * It should allow to delete uploads dirs
     *
     * @test
     */
    public function it_should_allow_to_delete_uploads_dirs() {
        $sut = $this->make_instance();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        mkdir($uploadsPath . '/folder1', 0777, true);

        $sut->seeUploadedFileFound('folder1');

        $sut->deleteUploadedDir('folder1');

        $sut->dontSeeUploadedFileFound('folder1');
    }

    /**
     * It should allow to delete upload dir using date
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_dir_using_date() {
        $sut = $this->make_instance();

        mkdir($this->nowUploads . '/folder1', 0777, true);

        $sut->seeUploadedFileFound('folder1', 'now');

        $sut->deleteUploadedDir('folder1', 'now');

        $sut->dontSeeUploadedFileFound('folder1', 'now');
    }

    /**
     * It should allow to delete upload files
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_files() {
        $sut = $this->make_instance();

        $uploadsPath = $this->config['wpRootFolder'] . $this->config['uploads'];

        file_put_contents($uploadsPath . '/file.txt', 'foo');

        $sut->seeUploadedFileFound('file.txt');

        $sut->deleteUploadedFile('file.txt');

        $sut->dontSeeUploadedFileFound('file.txt');
        $this->assertFileNotExists($uploadsPath . '/file.txt');
    }

    /**
     * It should allow to delete upload file using date
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_file_using_date() {
        $sut = $this->make_instance();

        file_put_contents($this->nowUploads . '/file.txt', 'foo');

        $sut->seeUploadedFileFound('file.txt', 'now');

        $sut->deleteUploadedFile('file.txt', 'now');

        $sut->dontSeeUploadedFileFound('file.txt', 'now');
        $this->assertFileNotExists($this->nowUploads . '/file.txt');
    }

    /**
     * It should allow cleaning the uploads dir
     *
     * @test
     */
    public function it_should_allow_cleaning_the_uploads_dir() {
        $sut = $this->make_instance();

        $folder = $this->config['wpRootFolder'] . $this->config['uploads'] . '/folder1';
        $file = $folder . '/file.txt';
        mkdir($folder, 0777, true);
        file_put_contents($file, 'foo');

        $this->assertFileExists($folder);
        $this->assertFileExists($file);

        $sut->cleanUploadsDir('folder1');

        $this->assertFileExists($folder);
        $this->assertFileNotExists($file);

        $sut->cleanUploadsDir();

        $this->assertFileNotExists($folder);
    }

    /**
     * It should allow cleaning upload dirs by date
     *
     * @test
     */
    public function it_should_allow_cleaning_upload_dirs_by_date() {
        $sut = $this->make_instance();

        $folder = $this->nowUploads . '/folder1';
        $file = $folder . '/file.txt';
        mkdir($folder, 0777, true);
        file_put_contents($file, 'foo');

        $this->assertFileExists($folder);
        $this->assertFileExists($file);

        $sut->cleanUploadsDir('folder1', 'now');

        $this->assertFileExists($folder);
        $this->assertFileNotExists($file);

        $sut->cleanUploadsDir();

        $this->assertFileNotExists($folder);
    }

    /**
     * It should allow copying dirs to the uploads dir
     *
     * @test
     */
    public function it_should_allow_copying_dirs_to_the_uploads_dir() {
        $sut = $this->make_instance();

        $src = codecept_data_dir('folder-structures/folder1');
        $dest = $this->config['wpRootFolder'] . $this->config['uploads'] . '/folder2';

        $this->assertFileExists($src);
        $this->assertFileNotExists($dest);

        $sut->copyDirToUploads($src, 'folder2');

        $this->assertFileExists($src);
        $this->assertFileExists($dest);
    }

    /**
     * It should allow copying dirs to the uploads dir by date
     *
     * @test
     */
    public function it_should_allow_copying_dirs_to_the_uploads_dir_by_date() {
        $sut = $this->make_instance();

        $src = codecept_data_dir('folder-structures/folder1');
        $dest = $this->nowUploads . '/folder2';

        $this->assertFileExists($src);
        $this->assertFileNotExists($dest);

        $sut->copyDirToUploads($src, 'folder2', 'today');

        $this->assertFileExists($src);
        $this->assertFileExists($dest);
    }

    /**
     * It should allow writing to uploads file
     *
     * @test
     */
    public function it_should_allow_writing_to_uploads_file() {
        $sut = $this->make_instance();

        $dest = $this->config['wpRootFolder'] . $this->config['uploads'] . '/some-file.txt';

        $this->assertFileNotExists($dest);

        $sut->writeToUploadedFile('some-file.txt', 'foo');

        $this->assertFileExists($dest);
        $this->assertStringEqualsFile($dest, 'foo');
    }

    /**
     * It should allow writing to uploads file by date
     *
     * @test
     */
    public function it_should_allow_writing_to_uploads_file_by_date() {
        $sut = $this->make_instance();

        $dest = $this->nowUploads . '/some-file.txt';

        $this->assertFileNotExists($dest);

        $sut->writeToUploadedFile('some-file.txt', 'foo', 'today');

        $this->assertFileExists($dest);
        $this->assertStringEqualsFile($dest, 'foo');
    }

    /**
     * It should allow opening an uploaded file
     *
     * @test
     */
    public function it_should_allow_opening_an_uploaded_file() {
        $sut = $this->make_instance();

        $dest = $this->config['wpRootFolder'] . $this->config['uploads'] . '/some-file.txt';

        $this->assertFileNotExists($dest);

        $sut->writeToUploadedFile('some-file.txt', 'foo');
        $sut->openUploadedFile('some-file.txt');

        $this->assertFileExists($dest);
        $sut->seeInThisFile('foo');
    }

    /**
     * It should allow opening an uploaded file by date
     *
     * @test
     */
    public function it_should_allow_opening_an_uploaded_file_by_date() {
        $sut = $this->make_instance();

        $dest = $this->nowUploads . '/some-file.txt';

        $this->assertFileNotExists($dest);

        $sut->writeToUploadedFile('some-file.txt', 'foo', 'today');
        $sut->openUploadedFile('some-file.txt', 'today');

        $this->assertFileExists($dest);
        $sut->seeInThisFile('foo');
    }

    /**
     * It should allow being in a plugin path
     *
     * @test
     */
    public function it_should_allow_being_in_a_plugin_path() {
        $sut = $this->make_instance();

        $pluginFolder = $this->config['wpRootFolder'] . $this->config['plugins'] . '/plugin1';
        mkdir($pluginFolder, 0777, true);
        mkdir($pluginFolder . '/sub/folder', 0777, true);

        $sut->amInPluginPath('plugin1');

        $this->assertEquals($pluginFolder, getcwd());

        $sut->amInPluginPath('plugin1/sub');

        $this->assertEquals($pluginFolder . '/sub', getcwd());

        $sut->amInPluginPath('plugin1/sub/folder');

        $this->assertEquals($pluginFolder . '/sub/folder', getcwd());

        $sut->amInPluginPath('plugin1');
        $sut->writeToFile('some-file.txt', 'foo');

        $this->assertFileExists($pluginFolder . '/some-file.txt');

        $sut->deletePluginFile('plugin1/some-file.txt');

        $this->assertFileNotExists($pluginFolder . '/some-file.txt');
        $sut->dontSeePluginFileFound('plugin1/some-file.txt');

        $sut->copyDirToPlugin(codecept_data_dir('folder-structures/folder1'), 'plugin1/folder1');

        $this->assertFileExists($pluginFolder . '/folder1');

        $sut->writeToPluginFile('plugin1/some-file.txt', 'bar');

        $sut->seePluginFileFound('plugin1/some-file.txt');
        $sut->seeInPluginFile('plugin1/some-file.txt', 'bar');
        $sut->dontSeeInPluginFile('plugin1/some-file.txt', 'woo');

        $sut->cleanPluginDir('plugin1');

        $this->assertFileNotExists($pluginFolder . '/some-file.txt');
    }

    /**
     * It should allow being in a themes path
     *
     * @test
     */
    public function it_should_allow_being_in_a_themes_path() {
        $sut = $this->make_instance();

        $themeFolder = $this->config['wpRootFolder'] . $this->config['themes'] . '/theme1';
        mkdir($themeFolder, 0777, true);
        mkdir($themeFolder . '/sub/folder', 0777, true);

        $sut->amInThemePath('theme1');

        $this->assertEquals($themeFolder, getcwd());

        $sut->amInThemePath('theme1/sub');

        $this->assertEquals($themeFolder . '/sub', getcwd());

        $sut->amInThemePath('theme1/sub/folder');

        $this->assertEquals($themeFolder . '/sub/folder', getcwd());

        $sut->amInThemePath('theme1');
        $sut->writeToFile('some-file.txt', 'foo');

        $this->assertFileExists($themeFolder . '/some-file.txt');

        $sut->deleteThemeFile('theme1/some-file.txt');

        $this->assertFileNotExists($themeFolder . '/some-file.txt');
        $sut->dontSeeThemeFileFound('theme1/some-file.txt');

        $sut->copyDirToTheme(codecept_data_dir('folder-structures/folder1'), 'theme1/folder1');

        $this->assertFileExists($themeFolder . '/folder1');

        $sut->writeToThemeFile('theme1/some-file.txt', 'bar');

        $sut->seeThemeFileFound('theme1/some-file.txt');
        $sut->seeInThemeFile('theme1/some-file.txt', 'bar');
        $sut->dontSeeInThemeFile('theme1/some-file.txt', 'woo');

        $sut->cleanThemeDir('theme1');

        $this->assertFileNotExists($themeFolder . '/some-file.txt');
    }

    /**
     * It should allow being in a mu-plugin path
     *
     * @test
     */
    public function it_should_allow_being_in_a_mu_plugin_path() {
        $sut = $this->make_instance();

        $mupluginFolder = $this->config['wpRootFolder'] . $this->config['mu-plugins'] . '/muplugin1';
        mkdir($mupluginFolder, 0777, true);
        mkdir($mupluginFolder . '/sub/folder', 0777, true);

        $sut->amInMuPluginPath('muplugin1');

        $this->assertEquals($mupluginFolder, getcwd());

        $sut->amInMuPluginPath('muplugin1/sub');

        $this->assertEquals($mupluginFolder . '/sub', getcwd());

        $sut->amInMuPluginPath('muplugin1/sub/folder');

        $this->assertEquals($mupluginFolder . '/sub/folder', getcwd());

        $sut->amInMuPluginPath('muplugin1');
        $sut->writeToFile('some-file.txt', 'foo');

        $this->assertFileExists($mupluginFolder . '/some-file.txt');

        $sut->deleteMuPluginFile('muplugin1/some-file.txt');

        $this->assertFileNotExists($mupluginFolder . '/some-file.txt');
        $sut->dontSeeMuPluginFileFound('muplugin1/some-file.txt');

        $sut->copyDirToMuPlugin(codecept_data_dir('folder-structures/folder1'), 'muplugin1/folder1');

        $this->assertFileExists($mupluginFolder . '/folder1');

        $sut->writeToMuPluginFile('muplugin1/some-file.txt', 'bar');

        $sut->seeMuPluginFileFound('muplugin1/some-file.txt');
        $sut->seeInMuPluginFile('muplugin1/some-file.txt', 'bar');
        $sut->dontSeeInMuPluginFile('muplugin1/some-file.txt', 'woo');

        $sut->cleanMuPluginDir('muplugin1');

        $this->assertFileNotExists($mupluginFolder . '/some-file.txt');
    }

    protected function _after() {
        if ( ! empty($this->sandbox) && file_exists($this->sandbox)) {
            rrmdir($this->sandbox);
        }
    }

    /**
     * It should allow having a plugin with code
     * @test
     */
    public function it_should_allow_having_a_plugin_with_code() {
        $sut = $this->make_instance();

        $pluginFolder = $this->config['wpRootFolder'] . $this->config['plugins'] . '/foo';
        $pluginFile   = $pluginFolder . '/plugin.php';

        $code = "echo 'Hello world';";
        $sut->havePlugin('foo/plugin.php', $code);

        $this->assertFileExists($pluginFolder);
        $this->assertFileExists($pluginFile);

        $expected = <<<PHP
<?php
/*
Plugin Name: foo
Description: foo
*/

echo 'Hello world';
PHP;
        $this->assertStringEqualsFile($pluginFile, $expected);

        $sut->_after($this->prophesize(TestInterface::class)->reveal());

        $this->assertFileNotExists($pluginFile);
        $this->assertFileNotExists($pluginFolder);
    }


    /**
     * It should allow having a mu-plugin with code
     * @test
     */
    public function it_should_allow_having_a_mu_plugin_with_code() {
        $sut = $this->make_instance();

        $muPluginFolder = $this->config['wpRootFolder'] . $this->config['mu-plugins'];
        $muPluginFile   = $muPluginFolder . '/test-mu-plugin.php';

        $code = "echo 'Hello world';";
        $sut->haveMuPlugin('test-mu-plugin.php', $code);

        $this->assertFileExists($muPluginFolder);
        $this->assertFileExists($muPluginFile);

        $expected = <<<PHP
<?php
/*
Plugin Name: Test mu-plugin 1
Description: Test mu-plugin 1
*/

echo 'Hello world';
PHP;
        $this->assertStringEqualsFile($muPluginFile, $expected);

        $sut->_after($this->prophesize(TestInterface::class)->reveal());

        $this->assertFileNotExists($muPluginFile);
        $this->assertFileExists($muPluginFolder);
    }

    /**
     * It should allow having a theme with code
     * @test
     */
    public function it_should_allow_having_a_theme_with_code() {
        $sut = $this->make_instance();

        $themeFolder    = $this->config['wpRootFolder'] . $this->config['themes'];
        $themeIndexFile = $themeFolder . '/test/index.php';
        $themeStyleFile = $themeFolder . '/test/style.css';

        $code = "echo 'Hello world';";
        $sut->haveTheme('test', $code);

        $this->assertFileExists($themeFolder);
        $this->assertFileExists($themeIndexFile);
        $this->assertFileExists($themeStyleFile);

        $expectedCss = <<<CSS
/*
Theme Name: test
Author: wp-browser
Description: test 
Version: 1.0
*/
CSS;

        $expectedIndex = <<< PHP
<?php echo 'Hello world';
PHP;

        $this->assertStringEqualsFile($themeStyleFile, $expectedCss);
        $this->assertStringEqualsFile($themeIndexFile, $expectedIndex);

        $sut->_after($this->prophesize(TestInterface::class)->reveal());

        $this->assertFileNotExists($themeStyleFile);
        $this->assertFileNotExists($themeIndexFile);
    }

    /**
     * It should allow having a theme with code and functions file
     * @test
     */
    public function it_should_allow_having_a_theme_with_code_and_functions_file() {
        $sut = $this->make_instance();

        $themeFolder    = $this->config['wpRootFolder'] . $this->config['themes'];
        $themeIndexFile = $themeFolder . '/test/index.php';
        $themeStyleFile = $themeFolder . '/test/style.css';
        $themeFunctionsFile = $themeFolder . '/test/functions.php';

        $code = "echo 'Hello world';";
        $sut->haveTheme('test', $code, $code);

        $this->assertFileExists($themeFolder);
        $this->assertFileExists($themeIndexFile);
        $this->assertFileExists($themeStyleFile);

        $expectedCss = <<<CSS
/*
Theme Name: test
Author: wp-browser
Description: test 
Version: 1.0
*/
CSS;

        $expectedIndex = <<< PHP
<?php echo 'Hello world';
PHP;

        $this->assertStringEqualsFile($themeStyleFile, $expectedCss);
        $this->assertStringEqualsFile($themeIndexFile, $expectedIndex);
        $this->assertStringEqualsFile($themeFunctionsFile, $expectedIndex);

        $sut->_after($this->prophesize(TestInterface::class)->reveal());

        $this->assertFileNotExists($themeStyleFile);
        $this->assertFileNotExists($themeIndexFile);
        $this->assertFileNotExists($themeFunctionsFile);
    }
}

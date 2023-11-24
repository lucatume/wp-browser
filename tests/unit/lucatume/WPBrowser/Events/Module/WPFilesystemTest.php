<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use DateTime;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Strings;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

class WPFilesystemTest extends Unit
{
    use TmpFilesCleanup;

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;
    /**
     * @var mixed[]
     */
    protected $config = [];
    protected $backupGlobals = false;
    protected $nowUploads;

    protected function module(): WPFilesystem
    {
        $moduleContainer = new ModuleContainer(new Di(), []);
        $instance = new WPFilesystem($moduleContainer, $this->config);
        $instance->_initialize();

        return $instance;
    }

    /**
     * It should throw if wpRootFolder param is missing
     *
     * @test
     */
    public function it_should_throw_if_wp_root_folder_param_is_missing(): void
    {
        $this->config = [];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should only require the wpRootFolder path parameter and default the other parameters
     *
     * @test
     */
    public function it_should_only_require_the_wp_root_folder_path_parameter_and_default_the_other_parameters(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $this->config = ['wpRootFolder' => $wpRoot];

        $sut = $this->module();
        $sut->_initialize();

        $moduleConfig = $sut->_getConfig();
        $this->assertEquals($wpRoot . '/', $moduleConfig['wpRootFolder']);
        $this->assertEquals($wpRoot . '/wp-content/themes/', $moduleConfig['themes']);
        $this->assertEquals($wpRoot . '/wp-content/plugins/', $moduleConfig['plugins']);
        $this->assertEquals($wpRoot . '/wp-content/mu-plugins/', $moduleConfig['mu-plugins']);
        $this->assertEquals($wpRoot . '/wp-content/uploads/', $moduleConfig['uploads']);
    }

    /**
     * It should allow specifying wpRootFolder as relative path to the project root
     *
     * @test
     */
    public function it_should_allow_specifying_wp_root_folder_as_relative_path_to_the_project_root(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        $wpRootRelative = str_replace(codecept_root_dir(), '', $wpRoot);
        touch($wpRoot . '/wp-load.php');
        $this->config = [
            'wpRootFolder' => $wpRootRelative
        ];

        $sut = $this->module();
        $sut->_initialize();
        $this->assertEquals($wpRootRelative . '/', $sut->_getConfig('wpRootFolder'));
    }

    /**
     * It should allow specifying optional path parameters as relative paths
     *
     * @test
     */
    public function it_should_allow_specifying_optional_path_parameters_as_relative_paths(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        mkdir($wpRoot . '/' . 'test-themes');
        mkdir($wpRoot . '/' . 'test-plugins');
        mkdir($wpRoot . '/' . 'test-mu-plugins');
        mkdir($wpRoot . '/' . 'test-uploads');

        $this->config = [
            'wpRootFolder' => $wpRoot,
            'themes' => 'test-themes',
            'plugins' => 'test-plugins',
            'mu-plugins' => 'test-mu-plugins',
            'uploads' => 'test-uploads',
        ];

        $sut = $this->module();
        $sut->_initialize();

        $this->assertEquals($wpRoot . '/test-themes/', $sut->_getConfig('themes'));
        $this->assertEquals($wpRoot . '/test-plugins/', $sut->_getConfig('plugins'));
        $this->assertEquals($wpRoot . '/test-mu-plugins/', $sut->_getConfig('mu-plugins'));
        $this->assertEquals($wpRoot . '/test-uploads/', $sut->_getConfig('uploads'));
    }

    /**
     * It should allow being in the uploads path
     *
     * @test
     */
    public function it_should_allow_being_in_the_uploads_path(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, ['wp-content' => ['uploads' => []]]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();
        $sut->amInUploadsPath();

        $this->assertEquals($wpRoot . '/wp-content/uploads', getcwd());
    }

    /**
     * It should allow being in an uploads subfolder
     *
     * @test
     */
    public function it_should_allow_being_in_an_uploads_subfolder(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [],
                    '2017' => [
                        '04' => []
                    ],
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();
        $sut->amInUploadsPath('test_1');

        $this->assertEquals($wpRoot . '/wp-content/uploads/test_1', getcwd());

        $sut->amInUploadsPath('2017/04');

        $this->assertEquals($wpRoot . '/wp-content/uploads/2017/04', getcwd());
    }

    /**
     * It should allow being in an uploads path year/month subfolder from date
     *
     * @test
     */
    public function it_should_allow_being_in_an_uploads_path_year_month_subfolder_from_date(): void
    {

        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [],
                    '2019' => [
                        '03' => []
                    ],
                    $Y => [
                        $m => []
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->amInUploadsPath('2019/03');

        $this->assertEquals($wpRoot . '/wp-content/uploads/2019/03', getcwd());

        $sut->amInUploadsPath('now');

        $this->assertEquals($wpRoot . "/wp-content/uploads/$Y/$m", getcwd());
    }

    /**
     * It should being in time based uploads folder with Unix timestamp
     *
     * @test
     */
    public function it_should_being_in_time_based_uploads_folder_with_unix_timestamp(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $lastMonthTimestamp = strtotime('last month');
        $lastMonth = new DateTime('@' . $lastMonthTimestamp);
        $lastMonthYear = $lastMonth->format('Y');
        $lastMonthMonth = $lastMonth->format('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [],
                    '2019' => [
                        '03' => []
                    ],
                    $lastMonthYear => [
                        $lastMonthMonth => []
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();
        $sut->amInUploadsPath($lastMonthTimestamp);

        $this->assertEquals($wpRoot . "/wp-content/uploads/$lastMonthYear/$lastMonthMonth", getcwd());
    }

    /**
     * It should allow seeing uploaded files
     *
     * @test
     */
    public function it_should_allow_seeing_uploaded_files(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => ['file.txt' => 'test test test']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound("/$Y/$m/file.txt");
        $sut->dontSeeUploadedFileFound('file.txt');
        $this->expectException(AssertionFailedError::class);
        $sut->seeUploadedFileFound('some-other-file.txt');
        $sut->dontSeeUploadedFileFound('some-other-file.txt');
    }

    /**
     * It should allow to see a file in the uploads folder based on the date
     *
     * @test
     */
    public function it_should_allow_to_see_a_file_in_the_uploads_folder_based_on_the_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => ['file.txt' => 'test test test']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound('file.txt', time());
        $sut->dontSeeUploadedFileFound('file.txt', 'last month');
        $this->expectException(AssertionFailedError::class);
        $sut->seeUploadedFileFound('some-other-file.txt', 'now');
        $sut->dontSeeUploadedFileFound('some-other-file.txt', 'last month');
    }

    /**
     * It should allow to see in an uploaded file contents
     *
     * @test
     */
    public function it_should_allow_to_see_in_an_uploaded_file_contents(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => ['file.txt' => 'lorem dolor']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeInUploadedFile("/$Y/$m/file.txt", 'lorem dolor');
        $sut->dontSeeInUploadedFile("/$Y/$m/file.txt", 'nunquam');
    }

    /**
     * It should allow to see an uploaded file content based on the date
     *
     * @test
     */
    public function it_should_allow_to_see_an_uploaded_file_content_based_on_the_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => ['file.txt' => 'lorem dolor']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeInUploadedFile("/file.txt", 'lorem dolor', 'now');
        $sut->dontSeeInUploadedFile("/file.txt", 'nunquam', 'now');
    }

    /**
     * It should allow to delete uploads dirs
     *
     * @test
     */
    public function it_should_allow_to_delete_uploads_dirs(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [
                        'test_2' => ['file.txt' => 'lorem dolor']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound('test_1');
        $sut->seeUploadedFileFound('test_1/test_2');

        $sut->deleteUploadedDir('test_1');

        $sut->dontSeeUploadedFileFound('test_1');
        $sut->dontSeeUploadedFileFound('test_1/test_2');
    }

    /**
     * It should allow to delete upload dir using date
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_dir_using_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => [
                            'test_1' => [
                                'test_2' => ['file.txt' => 'lorem dolor']
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound('test_1', time());
        $sut->seeUploadedFileFound('test_1/test_2', time());

        $sut->deleteUploadedDir('test_1', time());

        $sut->dontSeeUploadedFileFound('test_1', time());
        $sut->dontSeeUploadedFileFound('test_1/test_2', time());
    }

    /**
     * It should allow to delete upload files
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_files(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [
                        'test_2' => ['file.txt' => 'lorem dolor']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound('test_1/test_2/file.txt');
        $sut->deleteUploadedFile('test_1/test_2/file.txt');

        $sut->dontSeeUploadedFileFound('test_1/test_2/file.txt');
        $this->assertFileNotExists($wpRoot . '/wp-content/uploads/test_1/test_2/file.txt');
    }

    /**
     * It should allow to delete upload file using date
     *
     * @test
     */
    public function it_should_allow_to_delete_upload_file_using_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => ['file.txt' => 'lorem dolor']
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->seeUploadedFileFound("$Y/$m/file.txt");
        $sut->deleteUploadedFile("$Y/$m/file.txt");

        $sut->dontSeeUploadedFileFound("$Y/$m/file.txt");
        $this->assertFileNotExists($wpRoot . "/wp-content/uploads/$Y/$m/file.txt");
    }

    /**
     * It should allow cleaning the uploads dir
     *
     * @test
     */
    public function it_should_allow_cleaning_the_uploads_dir(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => ['file.txt' => 'lorem dolor']
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->cleanUploadsDir('test_1');

        $this->assertFileExists($wpRoot . '/wp-content/uploads/test_1');
        $this->assertFileNotExists($wpRoot . '/wp-content/uploads/test_1/file.txt');

        $sut->cleanUploadsDir();

        $this->assertFileNotExists($wpRoot . '/wp-content/uploads/test_1');
    }

    /**
     * It should allow cleaning upload dirs by date
     *
     * @test
     */
    public function it_should_allow_cleaning_upload_dirs_by_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => [
                            'test_1' => [
                                'test_2' => [
                                    'file.txt' => 'lorem dolor'
                                ]
                            ],
                            'file.txt' => 'lorem dolor'
                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $sut->cleanUploadsDir('test_1', time());

        $this->assertFileExists($wpRoot . "/wp-content/uploads/$Y/$m");
        $this->assertFileExists($wpRoot . "/wp-content/uploads/$Y/$m/test_1");
        $this->assertFileExists($wpRoot . "/wp-content/uploads/$Y/$m/file.txt");
        $this->assertFileNotExists($wpRoot . "/wp-content/uploads/$Y/$m/test_1/test_2");

        $sut->cleanUploadsDir('/', time());

        $this->assertFileExists($wpRoot . "/wp-content/uploads/$Y/$m");
        $this->assertFileNotExists($wpRoot . "/wp-content/uploads/$Y/$m/test_1");
    }

    /**
     * It should allow copying dirs to the uploads dir
     *
     * @test
     */
    public function it_should_allow_copying_dirs_to_the_uploads_dir(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    'test_1' => [
                        'test_2' => [
                            'file.txt' => 'lorem dolor'
                        ]
                    ],
                    'file.txt' => 'lorem dolor'
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $src = codecept_data_dir('folder-structures/folder1');
        $dest = $wpRoot . '/wp-content/uploads/folder2';

        $this->assertFileExists($src);
        $this->assertFileNotExists($dest);

        $sut->copyDirToUploads($src, 'folder2');

        $this->assertFileExists($src);
        $this->assertFileExists($dest);
        $this->assertFileExists($dest . '/index.html');
    }

    /**
     * It should allow copying dirs to the uploads dir by date
     *
     * @test
     */
    public function it_should_allow_copying_dirs_to_the_uploads_dir_by_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => [

                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $src = codecept_data_dir('folder-structures/folder1');
        $dest = $wpRoot . "/wp-content/uploads/$Y/$m/folder2";

        $this->assertFileExists($src);
        $this->assertFileNotExists($dest);

        $sut->copyDirToUploads($src, 'folder2', time());

        $this->assertFileExists($src);
        $this->assertFileExists($dest);
        $this->assertFileExists($dest . '/index.html');
    }

    /**
     * It should allow writing to uploads file
     *
     * @test
     */
    public function it_should_allow_writing_to_uploads_file(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $dest = $wpRoot . '/wp-content/uploads/some-file.txt';

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
    public function it_should_allow_writing_to_uploads_file_by_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => [

                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $dest = $wpRoot . "/wp-content/uploads/$Y/$m/some-file.txt";

        $this->assertFileNotExists($dest);

        $sut->writeToUploadedFile('some-file.txt', 'foo', time());

        $this->assertFileExists($dest);
        $this->assertStringEqualsFile($dest, 'foo');
    }

    /**
     * It should allow opening an uploaded file
     *
     * @test
     */
    public function it_should_allow_opening_an_uploaded_file(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $dest = $wpRoot . '/wp-content/uploads/some-file.txt';

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
    public function it_should_allow_opening_an_uploaded_file_by_date(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        $Y = date('Y');
        $m = date('m');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'uploads' => [
                    $Y => [
                        $m => [

                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $dest = $wpRoot . "/wp-content/uploads/$Y/$m/some-file.txt";

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
    public function it_should_allow_being_in_a_plugin_path(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => [
                    'plugin1' => [
                        'sub' => [
                            'folder' => []
                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $pluginFolder = $wpRoot . '/wp-content/plugins/plugin1';

        $sut = $this->module();

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
    public function it_should_allow_being_in_a_themes_path(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'themes' => [
                    'theme1' => [
                        'sub' => [
                            'folder' => []
                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $themeFolder = $wpRoot . '/wp-content/themes/theme1';

        $sut = $this->module();

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
    public function it_should_allow_being_in_a_mu_plugin_path(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'mu-plugins' => [
                    'muplugin1' => [
                        'sub' => [
                            'folder' => []
                        ]
                    ]
                ]
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $mupluginFolder = $wpRoot . '/wp-content/mu-plugins/muplugin1';

        $sut = $this->module();

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

    /**
     * It should allow having a plugin with code
     * @test
     */
    public function it_should_allow_having_a_plugin_with_code(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $pluginFolder = $wpRoot . '/wp-content/plugins/foo';
        $pluginFile = $pluginFolder . '/plugin.php';

        $sut = $this->module();

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
        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($pluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($pluginFile);
        $this->assertFileNotExists($pluginFolder);
    }

    /**
     * It should allow having a single file plugin with code
     * @test
     */
    public function it_should_allow_having_a_single_file_plugin_with_code(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $pluginsFolder = $wpRoot . '/wp-content/plugins';
        $pluginFile = $pluginsFolder . '/plugin.php';

        $sut = $this->module();

        $code = "echo 'Hello world';";
        $sut->havePlugin('plugin.php', $code);

        $this->assertFileExists($pluginFile);

        $expected = <<<PHP
<?php
/*
Plugin Name: plugin
Description: plugin
*/

echo 'Hello world';
PHP;
        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($pluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($pluginFile);
        $this->assertFileExists($pluginsFolder);
    }

    /**
     * It should allow having a mu-plugin with code
     * @test
     */
    public function it_should_allow_having_a_mu_plugin_with_code(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'mu-plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $muPluginFolder = $wpRoot . '/wp-content/mu-plugins';
        $muPluginFile = $muPluginFolder . '/test-mu-plugin.php';

        $sut = $this->module();

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

        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($muPluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($muPluginFile);
        $this->assertFileExists($muPluginFolder);
    }

    /**
     * It should allow having a theme with code
     * @test
     */
    public function it_should_allow_having_a_theme_with_code(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'themes' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $themesFolder = $wpRoot . '/wp-content/themes';
        $themeIndexFile = $themesFolder . '/test/index.php';
        $themeStyleFile = $themesFolder . '/test/style.css';

        $sut = $this->module();

        $code = "echo 'Hello world';";
        $sut->haveTheme('test', $code);

        $this->assertFileExists($themesFolder);
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

        $this->assertEquals(Strings::normalizeNewLine($expectedCss),
            Strings::normalizeNewLine(file_get_contents($themeStyleFile)));
        $this->assertEquals(Strings::normalizeNewLine($expectedIndex),
            Strings::normalizeNewLine(file_get_contents($themeIndexFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($themeStyleFile);
        $this->assertFileNotExists($themeIndexFile);
    }

    /**
     * It should allow having a theme with code and functions file
     * @test
     */
    public function it_should_allow_having_a_theme_with_code_and_functions_file(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'themes' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;
        $themesFolder = $wpRoot . '/wp-content/themes';
        $themeIndexFile = $themesFolder . '/test/index.php';
        $themeStyleFile = $themesFolder . '/test/style.css';
        $themeFunctionsFile = $themesFolder . '/test/functions.php';

        $sut = $this->module();

        $code = "echo 'Hello world';";
        $sut->haveTheme('test', $code, $code);

        $this->assertFileExists($themesFolder);
        $this->assertFileExists($themeIndexFile);
        $this->assertFileExists($themeStyleFile);
        $this->assertFileExists($themeFunctionsFile);

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

        $this->assertEquals(Strings::normalizeNewLine($expectedCss),
            Strings::normalizeNewLine(file_get_contents($themeStyleFile)));
        $this->assertEquals(Strings::normalizeNewLine($expectedIndex),
            Strings::normalizeNewLine(file_get_contents($themeIndexFile)));
        $this->assertEquals(Strings::normalizeNewLine($expectedIndex),
            Strings::normalizeNewLine(file_get_contents($themeFunctionsFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($themeStyleFile);
        $this->assertFileNotExists($themeIndexFile);
        $this->assertFileNotExists($themeFunctionsFile);
    }

    /**
     * It should allow opening PHP tag when having plugin
     *
     * @test
     */
    public function should_allow_opening_php_tag_when_having_plugin(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $pluginFolder =$wpRoot  . '/wp-content/plugins/foo';
        $pluginFile = $pluginFolder . '/plugin.php';

        $code = "<?php echo 'Hello world';";
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
        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($pluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($pluginFile);
        $this->assertFileNotExists($pluginFolder);
    }

    /**
     * It should allow the opening PHP tag when having a mu plugin
     *
     * @test
     */
    public function should_allow_the_opening_php_tag_when_having_a_mu_plugin(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'mu-plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $muPluginFolder = $wpRoot . '/wp-content/mu-plugins';
        $muPluginFile = $muPluginFolder . '/test-mu-plugin.php';

        $code = "<?php\necho 'Hello world';";
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

        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($muPluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($muPluginFile);
        $this->assertFileExists($muPluginFolder);
    }

    /**
     * It should allow the opening PHP tag when having a theme
     *
     * @test
     */
    public function should_allow_the_opening_php_tag_when_having_a_theme(): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'themes' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $themesFolder = $wpRoot . '/wp-content/themes';
        $themeIndexFile = $themesFolder . '/test/index.php';
        $themeStyleFile = $themesFolder . '/test/style.css';
        $themeFunctionsFile = $themesFolder . '/test/functions.php';

        $code = "<?php\necho 'Hello world';";
        $sut->haveTheme('test', $code, $code);

        $this->assertFileExists($themesFolder);
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

        $this->assertEquals(Strings::normalizeNewLine($expectedCss),
            Strings::normalizeNewLine(file_get_contents($themeStyleFile)));
        $this->assertEquals(Strings::normalizeNewLine($expectedIndex),
            Strings::normalizeNewLine(file_get_contents($themeIndexFile)));
        $this->assertEquals(Strings::normalizeNewLine($expectedIndex),
            Strings::normalizeNewLine(file_get_contents($themeFunctionsFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($themeStyleFile);
        $this->assertFileNotExists($themeIndexFile);
        $this->assertFileNotExists($themeFunctionsFile);
    }

    public function plugin_path_with_diff_path_separators_data_provider(): array
    {
        return [
            '/' => ['foo/plugin.php'],
            '\\' => ['foo\\plugin.php']
        ];
    }

    /**
     * It should allow using different directory separators to havePlugin
     *
     * @test
     * @dataProvider plugin_path_with_diff_path_separators_data_provider
     */
    public function should_allow_using_different_directory_separators_to_have_plugin($pluginPath): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $pluginsFolder = $wpRoot . '/wp-content/plugins';
        $pluginFile = $pluginsFolder . '/foo/plugin.php';

        $code = "echo 'Hello world';";
        $sut->havePlugin($pluginPath, $code);

        $this->assertFileExists($pluginFile);

        $expected = <<<PHP
<?php
/*
Plugin Name: foo
Description: foo
*/

echo 'Hello world';
PHP;
        $this->assertEquals(Strings::normalizeNewLine($expected),
            Strings::normalizeNewLine(file_get_contents($pluginFile)));

        $sut->_after(new class extends Unit {
        });

        $this->assertFileNotExists($pluginFile);
        $this->assertFileExists($pluginsFolder);
    }

    public function plugin_path_without_extension(): array
    {
        return [
            ['input' => 'foo', 'expected' => 'foo.php'],
            ['input' => 'foo/bar', 'expected' => 'foo/bar.php'],
            ['input' => 'foo.html', 'expected' => 'foo.html'],
            ['input' => 'foo/bar.html', 'expected' => 'foo/bar.html'],
        ];
    }

    /**
     * @test
     * @dataProvider plugin_path_without_extension
     */
    public function should_allow_passing_plugin_filename_without_extension($input, $expected): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $pluginsFolder = $wpRoot . '/wp-content/plugins';
        $pluginFile = "$pluginsFolder/$expected";

        $sut->havePlugin($input, '');

        $this->assertFileExists($pluginFile);
    }

    /**
     * @test
     * @dataProvider plugin_path_without_extension
     */
    public function should_allow_passing_muplugin_filename_without_extension($input, $expected): void
    {
        $wpRoot = FS::tmpDir('wpfilesystem_' . md5(microtime(true)));
        touch($wpRoot . '/wp-load.php');
        FS::mkdirp($wpRoot, [
            'wp-content' => [
                'mu-plugins' => []
            ]
        ]);
        $this->config['wpRootFolder'] = $wpRoot;

        $sut = $this->module();

        $muPluginsFolder = $wpRoot . '/wp-content/mu-plugins';
        $muPluginFile = "$muPluginsFolder/$expected";

        $sut->haveMuPlugin($input, '');

        $this->assertFileExists($muPluginFile);
    }
}

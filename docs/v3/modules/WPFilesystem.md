> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

# WPFilesystem module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Filesystem module](https://codeception.com/docs/modules/Filesystem) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress filesystem **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  
This module also provides methods to scaffold plugins and themes on the fly in the context of tests and auto-remove them after each test.

## Module requirements for Codeception 4.0+

This module requires the `codeception/module-filesystem` Composer package to work when wp-browser is used with Codeception 4.0.  

To install the package run: 

```bash
composer require --dev codeception/module-filesystem:^1.0
```

## Configuration

* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `themes` - defaults to `/wp-content/themes`; the path, relative to the the WordPress installation root folder, to the themes folder.
* `plugins` - defaults to `/wp-content/plugins`; the path, relative to the WordPress installation root folder, to the plugins folder.
* `mu-plugins` - defaults to `wp-content/mu-plugins`; the path, relative to the WordPress installation root folder, to the must-use plugins folder.
* `uploads` - defaults to `/wp-content/uploads`; the path, relative to the WordPress installation root folder, to the uploads folder.

### Example configuration
```yaml
modules:
    enabled:
        - WPFilesystem
    config:
        WPFilesystem:
            wpRootFolder: "/var/www/wordpress"
```
<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#aminmupluginpath">amInMuPluginPath</a>
		</li>
		<li>
			<a href="#aminpluginpath">amInPluginPath</a>
		</li>
		<li>
			<a href="#aminthemepath">amInThemePath</a>
		</li>
		<li>
			<a href="#aminuploadspath">amInUploadsPath</a>
		</li>
		<li>
			<a href="#cleanmuplugindir">cleanMuPluginDir</a>
		</li>
		<li>
			<a href="#cleanplugindir">cleanPluginDir</a>
		</li>
		<li>
			<a href="#cleanthemedir">cleanThemeDir</a>
		</li>
		<li>
			<a href="#cleanuploadsdir">cleanUploadsDir</a>
		</li>
		<li>
			<a href="#copydirtomuplugin">copyDirToMuPlugin</a>
		</li>
		<li>
			<a href="#copydirtoplugin">copyDirToPlugin</a>
		</li>
		<li>
			<a href="#copydirtotheme">copyDirToTheme</a>
		</li>
		<li>
			<a href="#copydirtouploads">copyDirToUploads</a>
		</li>
		<li>
			<a href="#deletemupluginfile">deleteMuPluginFile</a>
		</li>
		<li>
			<a href="#deletepluginfile">deletePluginFile</a>
		</li>
		<li>
			<a href="#deletethemefile">deleteThemeFile</a>
		</li>
		<li>
			<a href="#deleteuploadeddir">deleteUploadedDir</a>
		</li>
		<li>
			<a href="#deleteuploadedfile">deleteUploadedFile</a>
		</li>
		<li>
			<a href="#dontseeinmupluginfile">dontSeeInMuPluginFile</a>
		</li>
		<li>
			<a href="#dontseeinpluginfile">dontSeeInPluginFile</a>
		</li>
		<li>
			<a href="#dontseeinthemefile">dontSeeInThemeFile</a>
		</li>
		<li>
			<a href="#dontseeinuploadedfile">dontSeeInUploadedFile</a>
		</li>
		<li>
			<a href="#dontseemupluginfilefound">dontSeeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#dontseepluginfilefound">dontSeePluginFileFound</a>
		</li>
		<li>
			<a href="#dontseethemefilefound">dontSeeThemeFileFound</a>
		</li>
		<li>
			<a href="#dontseeuploadedfilefound">dontSeeUploadedFileFound</a>
		</li>
		<li>
			<a href="#getbloguploadspath">getBlogUploadsPath</a>
		</li>
		<li>
			<a href="#getuploadspath">getUploadsPath</a>
		</li>
		<li>
			<a href="#getwprootfolder">getWpRootFolder</a>
		</li>
		<li>
			<a href="#havemuplugin">haveMuPlugin</a>
		</li>
		<li>
			<a href="#haveplugin">havePlugin</a>
		</li>
		<li>
			<a href="#havetheme">haveTheme</a>
		</li>
		<li>
			<a href="#makeuploadsdir">makeUploadsDir</a>
		</li>
		<li>
			<a href="#openuploadedfile">openUploadedFile</a>
		</li>
		<li>
			<a href="#seeinmupluginfile">seeInMuPluginFile</a>
		</li>
		<li>
			<a href="#seeinpluginfile">seeInPluginFile</a>
		</li>
		<li>
			<a href="#seeinthemefile">seeInThemeFile</a>
		</li>
		<li>
			<a href="#seeinuploadedfile">seeInUploadedFile</a>
		</li>
		<li>
			<a href="#seemupluginfilefound">seeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#seepluginfilefound">seePluginFileFound</a>
		</li>
		<li>
			<a href="#seethemefilefound">seeThemeFileFound</a>
		</li>
		<li>
			<a href="#seeuploadedfilefound">seeUploadedFileFound</a>
		</li>
		<li>
			<a href="#writetomupluginfile">writeToMuPluginFile</a>
		</li>
		<li>
			<a href="#writetopluginfile">writeToPluginFile</a>
		</li>
		<li>
			<a href="#writetothemefile">writeToThemeFile</a>
		</li>
		<li>
			<a href="#writetouploadedfile">writeToUploadedFile</a>
		</li>
	</ul>
</nav>

<h3>amInMuPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a mu-plugin.</p>
```php
$I->amInMuPluginPath('mu-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>amInPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a plugin.</p>
```php
$I->amInPluginPath('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The folder path, relative to the root uploads folder, to change to.</li></ul>
  

<h3>amInThemePath</h3>

<hr>

<p>Sets the current working folder to a folder in a theme.</p>
```php
$I->amInThemePath('my-theme');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the theme folder, relative to themes root folder.</li></ul>
  

<h3>amInUploadsPath</h3>

<hr>

<p>Enters, changing directory, to the uploads folder in the local filesystem.</p>
```php
$I->amInUploadsPath('/logs');
  $I->seeFileFound('shop.log');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the site uploads folder.</li></ul>
  

<h3>cleanMuPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a mu-plugin folder.</p>
```php
$I->cleanMuPluginDir('mu-plugin1/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory, relative to the mu-plugins root folder.</li></ul>
  

<h3>cleanPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a plugin folder.</p>
```php
$I->cleanPluginDir('my-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the plugins root folder.</li></ul>
  

<h3>cleanThemeDir</h3>

<hr>

<p>Clears, emptying it, a folder in a theme folder.</p>
```php
$I->cleanThemeDir('my-theme/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the themese root folder.</li></ul>
  

<h3>cleanUploadsDir</h3>

<hr>

<p>Clears a folder in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->cleanUploadsDir('some/folder');
  $I->cleanUploadsDir('some/folder', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>copyDirToMuPlugin</h3>

<hr>

<p>Copies a folder to a folder in a mu-plugin.</p>
```php
$I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The path to the destination folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>copyDirToPlugin</h3>

<hr>

<p>Copies a folder to a folder in a plugin.</p>
```php
// Copy the 'foo' folder to the 'foo' folder in the plugin.
  $I->copyDirToPlugin(codecept_data_dir('foo'), 'my-plugin/foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source directory to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The destination path, relative to the plugins root folder.</li></ul>
  

<h3>copyDirToTheme</h3>

<hr>

<p>Copies a folder in a theme folder.</p>
```php
$I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file.</li>
<li><code>string</code> <strong>$themeDst</strong> - The path to the destination folder, relative to the themes root folder.</li></ul>
  

<h3>copyDirToUploads</h3>

<hr>

<p>Copies a folder to the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo');
  $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$dst</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteMuPluginFile</h3>

<hr>

<p>Deletes a file in a mu-plugin folder.</p>
```php
$I->deleteMuPluginFile('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li></ul>
  

<h3>deletePluginFile</h3>

<hr>

<p>Deletes a file in a plugin folder.</p>
```php
$I->deletePluginFile('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The folder path, relative to the plugins root folder.</li></ul>
  

<h3>deleteThemeFile</h3>

<hr>

<p>Deletes a file in a theme folder.</p>
```php
$I->deleteThemeFile('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file to delete, relative to the themes root folder.</li></ul>
  

<h3>deleteUploadedDir</h3>

<hr>

<p>Deletes a dir in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->deleteUploadedDir('folder');
  $I->deleteUploadedDir('folder', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteUploadedFile</h3>

<hr>

<p>Deletes a file in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->deleteUploadedFile('some-file.txt');
  $I->deleteUploadedFile('some-file.txt', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder does not contain a string.</p>
```php
$I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder does not contain a string.</p>
```php
$I->dontSeeInPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder does not contain a string.</p>
```php
$I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->dontSeeInUploadedFile('some-file.txt', 'foo');
  $I->dontSeeInUploadedFile('some-file.txt','foo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The not expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a mu-plugin folder.</p>
```php
$I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>dontSeePluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a plugin folder.</p>
```php
$I->dontSeePluginFileFound('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li></ul>
  

<h3>dontSeeThemeFileFound</h3>

<hr>

<p>Checks that a file is not found in a theme folder.</p>
```php
$I->dontSeeThemeFileFound('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>dontSeeUploadedFileFound</h3>

<hr>

<p>Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->dontSeeUploadedFileFound('some-file.txt');
  $I->dontSeeUploadedFileFound('some-file.txt','today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>getBlogUploadsPath</h3>

<hr>

<p>Returns the absolute path to a blog uploads folder or file.</p>
```php
$blogId = $I->haveBlogInDatabase('test');
  $testTodayUploads = $I->getBlogUploadsPath($blogId);
  $testLastMonthLogs = $I->getBlogUploadsPath($blogId, '/logs', '-1 month');
  file or folder.
  sub-folders in the year/month format; a UNIX timestamp or
  a string supported by the `strtotime` function; defaults
  to `now`.
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID to get the path for.</li>
<li><code>string</code> <strong>$file</strong> - The path, relatitve to the blog uploads folder, to the</li>
<li><code>null/string/[\DateTime](http://php.net/manual/en/class.datetime.php)/[\DateTime](http://php.net/manual/en/class.datetime.php)Immutable</code> <strong>$date</strong> - The date that should be used to build the uploads</li></ul>
  

<h3>getUploadsPath</h3>

<hr>

<p>Returns the path to the specified uploads file of folder. Not providing a value for <code>$file</code> and <code>$date</code> will return the uploads folder path.</p>
```php
$todaysPath = $I->getUploadsPath();
  $lastWeek = $I->getUploadsPath('', '-1 week');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder.</li>
<li><code>mixed</code> <strong>$date</strong> - A string compatible with <code>strtotime</code>, a Unix timestamp or a Date object.</li></ul>
  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to WordPress root folder without trailing slash.</p>
```php
$rootFolder = $I->getWpRootFolder();
  $I->assertFileExists($rootFolder . 'wp-load.php');
```

  

<h3>haveMuPlugin</h3>

<hr>

<p>Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'echo "Hello world!"';
  $I->haveMuPlugin('foo-mu-plugin.php', $code);
  // Load the code from a file.
  $code = file_get_contents(codecept_data_dir('code/mu-plugin.php'));
  $I->haveMuPlugin('foo-mu-plugin.php', $code);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file to create, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file with or without the opening PHP tag.</li></ul>
  

<h3>havePlugin</h3>

<hr>

<p>Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'echo "Hello world!"';
  $I->havePlugin('foo/plugin.php', $code);
  // Load the code from a file.
  $code = file_get_contents(codecept_data_dir('code/plugin.php'));
  $I->havePlugin('foo/plugin.php', $code);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the file to create, relative to the plugins folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file with or without the opening PHP tag.</li></ul>
  

<h3>haveTheme</h3>

<hr>

<p>Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code can not contain the opening '&lt;?php' tag.</p>
```php
$code = 'sayHi();';
  $functionsCode  = 'function sayHi(){echo "Hello world";};';
  $I->haveTheme('foo', $indexCode, $functionsCode);
  // Load the code from a file.
  $indexCode = file_get_contents(codecept_data_dir('code/index.php'));
  $functionsCode = file_get_contents(codecept_data_dir('code/functions.php'));
  $I->haveTheme('foo', $indexCode, $functionsCode);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$folder</strong> - The path to the theme to create, relative to the themes root folder.</li>
<li><code>string</code> <strong>$indexFileCode</strong> - The content of the theme index.php file with or without the opening PHP tag.</li>
<li><code>string</code> <strong>$functionsFileCode</strong> - The content of the theme functions.php file with or without the opening PHP tag.</li></ul>
  

<h3>makeUploadsDir</h3>

<hr>

<p>Creates an empty folder in the WordPress installation uploads folder.</p>
```php
$logsDir = $I->makeUploadsDir('logs/acme');
  to create.
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the WordPress installation uploads folder, of the folder</li></ul>
  

<h3>openUploadedFile</h3>

<hr>

<p>Opens a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->openUploadedFile('some-file.txt');
  $I->openUploadedFile('some-file.txt', 'time');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>seeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder contains a string.</p>
```php
$I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder contains a string.</p>
```php
$I->seeInPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder contains a string.</p>
```php
<?php
  $I->seeInThemeFile('my-theme/some-file.txt', 'foo');
  ?>
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->seeInUploadedFile('some-file.txt', 'foo');
  $I->seeInUploadedFile('some-file.txt','foo', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>seeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is found in a mu-plugin folder.</p>
```php
$I->seeMuPluginFileFound('mu-plugin1/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>seePluginFileFound</h3>

<hr>

<p>Checks that a file is found in a plugin folder.</p>
```php
$I->seePluginFileFound('my-plugin/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to thep plugins root folder.</li></ul>
  

<h3>seeThemeFileFound</h3>

<hr>

<p>Checks that a file is found in a theme folder.</p>
```php
$I->seeThemeFileFound('my-theme/some-file.txt');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>seeUploadedFileFound</h3>

<hr>

<p>Checks if file exists in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->seeUploadedFileFound('some-file.txt');
  $I->seeUploadedFileFound('some-file.txt','today');
  ?>
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>writeToMuPluginFile</h3>

<hr>

<p>Writes a file in a mu-plugin folder.</p>
```php
$I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the destination file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToPluginFile</h3>

<hr>

<p>Writes a file in a plugin folder.</p>
```php
$I->writeToPluginFile('my-plugin/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write in the file.</li></ul>
  

<h3>writeToThemeFile</h3>

<hr>

<p>Writes a string to a file in a theme folder.</p>
```php
$I->writeToThemeFile('my-theme/some-file.txt', 'foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themese root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToUploadedFile</h3>

<hr>

<p>Writes a string to a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
```php
$I->writeToUploadedFile('some-file.txt', 'foo bar');
  $I->writeToUploadedFile('some-file.txt', 'foo bar', 'today');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>


*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

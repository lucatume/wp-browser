## WPFilesystem module

Interact and make assertions on the WordPress file structure.

This module is used together with [the WPDb module](WPDb.md) to manage the state of the WordPress installation in the
context of end-to-end tests.

This module extends [the Filesystem module from Codeception][1], you can reference to the Codeception documentation to
find out more about the module configuration and usage.

This module should be with [Cest][2] and [Cept][3] test cases.

## Configuration

`wpRootFolder` - **required**; the path to the WordPress installation root folder. This can be a relative path to the
codeception root directory, or an absolute path to the WordPress installation directory. The WordPress installation
directory is the directory that contains the `wp-load.php` file.
`themes` - the path, relative to the path specified in the `wpRootFolder` parameter, to the themes directory. By
default,
it's `/wp-content/themes`.
`plugins` - the path, relative to the path specified in the `wpRootFolder` parameter, to the plugins directory. By
default, it's `/wp-content/plugins`.
`mu-plugins` - the path, relative to the path specified in the `wpRootFolder` parameter, to the must-use plugins. By
default, it's `/wp-content/mu-plugins`. directory.
`uploads` - the path, relative to the path specified in the `wpRootFolder` parameter, to the uploads directory. By
default, it's `/wp-content/uploads`.

The following is an example of the module configuration to run tests on the `/var/wordpress` site:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPFilesystem:
      wpRootFolder: /var/wordpress
      themes: wp-content/themes
      plugins: wp-content/plugins
      mu-plugins: wp-content/mu-plugins
      uploads: wp-content/uploads
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPFilesystem:
      wpRootFolder: '%WP_ROOT_FOLDER%'
```

## Methods

The module provides the following methods:

<!-- methods -->

#### amInMuPluginPath
Signature: `amInMuPluginPath(string $path)` : `void`  

Sets the current working folder to a folder in a mu-plugin.

``` php
$I->amInMuPluginPath('mu-plugin');
```

#### amInPath
Signature: `amInPath(string $path)` : `void`  

Enters a directory In local filesystem.
Project root directory is used by default
#### amInPluginPath
Signature: `amInPluginPath(string $path)` : `void`  

Sets the current working folder to a folder in a plugin.

``` php
$I->amInPluginPath('my-plugin');
```

#### amInThemePath
Signature: `amInThemePath(string $path)` : `void`  

Sets the current working folder to a folder in a theme.

``` php
$I->amInThemePath('my-theme');
```

#### amInUploadsPath
Signature: `amInUploadsPath([?string $path])` : `void`  

Enters, changing directory, to the uploads folder in the local filesystem.

```php
<?php
$I->amInUploadsPath('/logs');
$I->seeFileFound('shop.log');
```

#### assertDirectoryExists
Signature: `assertDirectoryExists(string $directory, [string $message])` : `void`
#### cleanDir
Signature: `cleanDir(string $dirname)` : `void`  

Erases directory contents

``` php
<?php
$I->cleanDir('logs');
```
#### cleanMuPluginDir
Signature: `cleanMuPluginDir(string $dir)` : `void`  

Cleans, emptying it, a folder in a mu-plugin folder.

``` php
$I->cleanMuPluginDir('mu-plugin1/foo');
```

#### cleanPluginDir
Signature: `cleanPluginDir(string $dir)` : `void`  

Cleans, emptying it, a folder in a plugin folder.

``` php
$I->cleanPluginDir('my-plugin/foo');
```

#### cleanThemeDir
Signature: `cleanThemeDir(string $dir)` : `void`  

Clears, emptying it, a folder in a theme folder.

``` php
$I->cleanThemeDir('my-theme/foo');
```

#### cleanUploadsDir
Signature: `cleanUploadsDir([?string $dir], [DateTime|string|int|null $date])` : `void`  

Clears a folder in the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->cleanUploadsDir('some/folder');
$I->cleanUploadsDir('some/folder', 'today');
```

#### copyDir
Signature: `copyDir(string $src, string $dst)` : `void`  

Copies directory with all contents

``` php
<?php
$I->copyDir('vendor','old_vendor');
```
#### copyDirToMuPlugin
Signature: `copyDirToMuPlugin(string $src, string $pluginDst)` : `void`  

Copies a folder to a folder in a mu-plugin.

``` php
$I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo');
```

#### copyDirToPlugin
Signature: `copyDirToPlugin(string $src, string $pluginDst)` : `void`  

Copies a folder to a folder in a plugin.

``` php
// Copy the 'foo' folder to the 'foo' folder in the plugin.
$I->copyDirToPlugin(codecept_data_dir('foo'), 'my-plugin/foo');
```

#### copyDirToTheme
Signature: `copyDirToTheme(string $src, string $themeDst)` : `void`  

Copies a folder in a theme folder.

``` php
$I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme');
```

#### copyDirToUploads
Signature: `copyDirToUploads(string $src, string $dst, [DateTime|string|int|null $date])` : `void`  

Copies a folder to the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo');
$I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today');
```

#### deleteDir
Signature: `deleteDir(string $dirname)` : `void`  

Deletes directory with all subdirectories

``` php
<?php
$I->deleteDir('vendor');
```
#### deleteFile
Signature: `deleteFile(string $filename)` : `void`  

Deletes a file

``` php
<?php
$I->deleteFile('composer.lock');
```
#### deleteMuPluginFile
Signature: `deleteMuPluginFile(string $file)` : `void`  

Deletes a file in a mu-plugin folder.

``` php
$I->deleteMuPluginFile('mu-plugin1/some-file.txt');
```

#### deletePluginFile
Signature: `deletePluginFile(string $file)` : `void`  

Deletes a file in a plugin folder.

``` php
$I->deletePluginFile('my-plugin/some-file.txt');
```

#### deleteThemeFile
Signature: `deleteThemeFile(string $file)` : `void`  

Deletes a file in a theme folder.

``` php
$I->deleteThemeFile('my-theme/some-file.txt');
```

#### deleteThisFile
Signature: `deleteThisFile()` : `void`  

Deletes a file
#### deleteUploadedDir
Signature: `deleteUploadedDir(string $dir, [DateTime|string|int|null $date])` : `void`  

Deletes a dir in the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->deleteUploadedDir('folder');
$I->deleteUploadedDir('folder', 'today');
```

#### deleteUploadedFile
Signature: `deleteUploadedFile(string $file, [string|int|null $date])` : `void`  

Deletes a file in the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->deleteUploadedFile('some-file.txt');
$I->deleteUploadedFile('some-file.txt', 'today');
```

#### dontSeeFileFound
Signature: `dontSeeFileFound(string $filename, [string $path])` : `void`  

Checks if file does not exist in path
#### dontSeeInMuPluginFile
Signature: `dontSeeInMuPluginFile(string $file, string $contents)` : `void`  

Checks that a file in a mu-plugin folder does not contain a string.

``` php
$I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

#### dontSeeInPluginFile
Signature: `dontSeeInPluginFile(string $file, string $contents)` : `void`  

Checks that a file in a plugin folder does not contain a string.

``` php
$I->dontSeeInPluginFile('my-plugin/some-file.txt', 'foo');
```

#### dontSeeInThemeFile
Signature: `dontSeeInThemeFile(string $file, string $contents)` : `void`  

Checks that a file in a theme folder does not contain a string.

``` php
$I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo');
```

#### dontSeeInThisFile
Signature: `dontSeeInThisFile(string $text)` : `void`  

Checks If opened file doesn't contain `text` in it

``` php
<?php
$I->openFile('composer.json');
$I->dontSeeInThisFile('codeception/codeception');
```
#### dontSeeInUploadedFile
Signature: `dontSeeInUploadedFile(string $file, string $contents, [string|int|null $date])` : `void`  

Checks that a file in the uploads folder does contain a string.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

```php
<?php
$I->dontSeeInUploadedFile('some-file.txt', 'foo');
$I->dontSeeInUploadedFile('some-file.txt','foo', 'today');
```

#### dontSeeMuPluginFileFound
Signature: `dontSeeMuPluginFileFound(string $file)` : `void`  

Checks that a file is not found in a mu-plugin folder.

``` php
$I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt');
```

#### dontSeePluginFileFound
Signature: `dontSeePluginFileFound(string $file)` : `void`  

Checks that a file is not found in a plugin folder.

``` php
$I->dontSeePluginFileFound('my-plugin/some-file.txt');
```

#### dontSeeThemeFileFound
Signature: `dontSeeThemeFileFound(string $file)` : `void`  

Checks that a file is not found in a theme folder.

``` php
$I->dontSeeThemeFileFound('my-theme/some-file.txt');
```

#### dontSeeUploadedFileFound
Signature: `dontSeeUploadedFileFound(string $file, [string|int|null $date])` : `void`  

Checks thata a file does not exist in the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->dontSeeUploadedFileFound('some-file.txt');
$I->dontSeeUploadedFileFound('some-file.txt','today');
```

#### getBlogUploadsPath
Signature: `getBlogUploadsPath(int $blogId, [string $file], [DateTimeImmutable|DateTime|string|null $date])` : `string`  

Returns the absolute path to a blog uploads folder or file.

```php
<?php
$blogId = $I->haveBlogInDatabase('test');
$testTodayUploads = $I->getBlogUploadsPath($blogId);
$testLastMonthLogs = $I->getBlogUploadsPath($blogId, '/logs', '-1 month');
```

#### getUploadsPath
Signature: `getUploadsPath([string $file], [mixed $date])` : `string`  

Returns the path to the specified uploads file of folder.

Not providing a value for `$file` and `$date` will return the uploads folder path.

```php
<?php
$todaysPath = $I->getUploadsPath();
$lastWeek = $I->getUploadsPath('', '-1 week');
```

#### getWpRootFolder
Signature: `getWpRootFolder()` : `string`  

Returns the absolute path to WordPress root folder without trailing slash.

```php
<?php
$rootFolder = $I->getWpRootFolder();
$I->assertFileExists($rootFolder . 'wp-load.php');
```

#### haveMuPlugin
Signature: `haveMuPlugin(string $filename, string $code)` : `void`  

Creates a mu-plugin file, including plugin header, in the mu-plugins folder.

The code can not contain the opening '<?php' tag.

``` php
$code = 'echo "Hello world!"';
$I->haveMuPlugin('foo-mu-plugin.php', $code);
// Load the code from a file.
$code = file_get_contents(codecept_data_dir('code/mu-plugin.php'));
$I->haveMuPlugin('foo-mu-plugin.php', $code);
```

#### havePlugin
Signature: `havePlugin(string $path, string $code)` : `void`  

Creates a plugin file, including plugin header, in the plugins folder.

The plugin is just created and not activated; the code can not contain the opening '<?php' tag.

``` php
$code = 'echo "Hello world!"';
$I->havePlugin('foo/plugin.php', $code);
// Load the code from a file.
$code = file_get_contents(codecept_data_dir('code/plugin.php'));
$I->havePlugin('foo/plugin.php', $code);
```

#### haveTheme
Signature: `haveTheme(string $folder, string $indexFileCode, [string $functionsFileCode])` : `void`  

Creates a theme file structure, including theme style file and index, in the themes folder.

The theme is just created and not activated; the code can not contain the opening '<?php' tag.

``` php
$code = 'sayHi();';
$functionsCode  = 'function sayHi(){echo "Hello world";};';
$I->haveTheme('foo', $indexCode, $functionsCode);
// Load the code from a file.
$indexCode = file_get_contents(codecept_data_dir('code/index.php'));
$functionsCode = file_get_contents(codecept_data_dir('code/functions.php'));
$I->haveTheme('foo', $indexCode, $functionsCode);
```

#### makeUploadsDir
Signature: `makeUploadsDir(string $path)` : `string`  

Creates an empty folder in the WordPress installation uploads folder.

```php
<?php
$logsDir = $I->makeUploadsDir('logs/acme');
```

#### openFile
Signature: `openFile(string $filename)` : `void`  

Opens a file and stores it's content.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
```
#### openUploadedFile
Signature: `openUploadedFile(string $filename, [DateTime|string|int|null $date])` : `void`  

Opens a file in the the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->openUploadedFile('some-file.txt');
$I->openUploadedFile('some-file.txt', 'time');
```

#### seeFileContentsEqual
Signature: `seeFileContentsEqual(string $text)` : `void`  

Checks the strict matching of file contents.
Unlike `seeInThisFile` will fail if file has something more than expected lines.
Better to use with HEREDOC strings.
Matching is done after removing "\r" chars from file content.

``` php
<?php
$I->openFile('process.pid');
$I->seeFileContentsEqual('3192');
```
#### seeFileFound
Signature: `seeFileFound(string $filename, [string $path])` : `void`  

Checks if file exists in path.
Opens a file when it's exists

``` php
<?php
$I->seeFileFound('UserModel.php','app/models');
```
#### seeInMuPluginFile
Signature: `seeInMuPluginFile(string $file, string $contents)` : `void`  

Checks that a file in a mu-plugin folder contains a string.

``` php
$I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

#### seeInPluginFile
Signature: `seeInPluginFile(string $file, string $contents)` : `void`  

Checks that a file in a plugin folder contains a string.

``` php
$I->seeInPluginFile('my-plugin/some-file.txt', 'foo');
```

#### seeInThemeFile
Signature: `seeInThemeFile(string $file, string $contents)` : `void`  

Checks that a file in a theme folder contains a string.

``` php
<?php
$I->seeInThemeFile('my-theme/some-file.txt', 'foo');
?>
```

#### seeInThisFile
Signature: `seeInThisFile(string $text)` : `void`  

Checks If opened file has `text` in it.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
```
#### seeInUploadedFile
Signature: `seeInUploadedFile(string $file, string $contents, [string|int|null $date])` : `void`  

Checks that a file in the uploads folder contains a string.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

```php
<?php
$I->seeInUploadedFile('some-file.txt', 'foo');
$I->seeInUploadedFile('some-file.txt','foo', 'today');
```

#### seeMuPluginFileFound
Signature: `seeMuPluginFileFound(string $file)` : `void`  

Checks that a file is found in a mu-plugin folder.

``` php
$I->seeMuPluginFileFound('mu-plugin1/some-file.txt');
```

#### seeNumberNewLines
Signature: `seeNumberNewLines(int $number)` : `void`  

Checks If opened file has the `number` of new lines.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeNumberNewLines(5);
```

#### seePluginFileFound
Signature: `seePluginFileFound(string $file)` : `void`  

Checks that a file is found in a plugin folder.

``` php
$I->seePluginFileFound('my-plugin/some-file.txt');
```

#### seeThemeFileFound
Signature: `seeThemeFileFound(string $file)` : `void`  

Checks that a file is found in a theme folder.

``` php
$I->seeThemeFileFound('my-theme/some-file.txt');
```

#### seeThisFileMatches
Signature: `seeThisFileMatches(string $regex)` : `void`  

Checks that contents of currently opened file matches $regex
#### seeUploadedFileFound
Signature: `seeUploadedFileFound(string $filename, [string|int|null $date])` : `void`  

Checks if file exists in the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

```php
<?php
$I->seeUploadedFileFound('some-file.txt');
$I->seeUploadedFileFound('some-file.txt','today');
?>
```

#### writeToFile
Signature: `writeToFile(string $filename, string $contents)` : `void`  

Saves contents to file
#### writeToMuPluginFile
Signature: `writeToMuPluginFile(string $file, string $data)` : `void`  

Writes a file in a mu-plugin folder.

``` php
$I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo');
```

#### writeToPluginFile
Signature: `writeToPluginFile(string $file, string $data)` : `void`  

Writes a file in a plugin folder.

``` php
$I->writeToPluginFile('my-plugin/some-file.txt', 'foo');
```

#### writeToThemeFile
Signature: `writeToThemeFile(string $file, string $data)` : `void`  

Writes a string to a file in a theme folder.

``` php
$I->writeToThemeFile('my-theme/some-file.txt', 'foo');
```

#### writeToUploadedFile
Signature: `writeToUploadedFile(string $filename, string $data, [DateTime|string|int|null $date])` : `string`  

Writes a string to a file in the the uploads folder.

The date argument can be a string compatible with `strtotime` or a Unix
timestamp that will be used to build the `Y/m` uploads subfolder path.

``` php
$I->writeToUploadedFile('some-file.txt', 'foo bar');
$I->writeToUploadedFile('some-file.txt', 'foo bar', 'today');
```
<!-- /methods -->

Read more [in Codeception documentation.][1]

[1]: https://codeception.com/docs/modules/Filesystem

[2]: https://codeception.com/docs/AcceptanceTests

[3]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

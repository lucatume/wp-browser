# WPFilesystem module

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

* `amInMuPluginPath(string $path)` : `void`
* `amInPath(string $path)` : `void`
* `amInPluginPath(string $path)` : `void`
* `amInThemePath(string $path)` : `void`
* `amInUploadsPath(?string [$path])` : `void`
* `assertDirectoryExists(string $directory, string [$message])` : `void`
* `cleanDir(string $dirname)` : `void`
* `cleanMuPluginDir(string $dir)` : `void`
* `cleanPluginDir(string $dir)` : `void`
* `cleanThemeDir(string $dir)` : `void`
* `cleanUploadsDir(?string [$dir], DateTime|string|int|null [$date])` : `void`
* `copyDir(string $src, string $dst)` : `void`
* `copyDirToMuPlugin(string $src, string $pluginDst)` : `void`
* `copyDirToPlugin(string $src, string $pluginDst)` : `void`
* `copyDirToTheme(string $src, string $themeDst)` : `void`
* `copyDirToUploads(string $src, string $dst, DateTime|string|int|null [$date])` : `void`
* `deleteDir(string $dirname)` : `void`
* `deleteFile(string $filename)` : `void`
* `deleteMuPluginFile(string $file)` : `void`
* `deletePluginFile(string $file)` : `void`
* `deleteThemeFile(string $file)` : `void`
* `deleteThisFile()` : `void`
* `deleteUploadedDir(string $dir, DateTime|string|int|null [$date])` : `void`
* `deleteUploadedFile(string $file, string|int|null [$date])` : `void`
* `dontSeeFileFound(string $filename, string [$path])` : `void`
* `dontSeeInMuPluginFile(string $file, string $contents)` : `void`
* `dontSeeInPluginFile(string $file, string $contents)` : `void`
* `dontSeeInThemeFile(string $file, string $contents)` : `void`
* `dontSeeInThisFile(string $text)` : `void`
* `dontSeeInUploadedFile(string $file, string $contents, string|int|null [$date])` : `void`
* `dontSeeMuPluginFileFound(string $file)` : `void`
* `dontSeePluginFileFound(string $file)` : `void`
* `dontSeeThemeFileFound(string $file)` : `void`
* `dontSeeUploadedFileFound(string $file, string|int|null [$date])` : `void`
* `getBlogUploadsPath(int $blogId, string [$file], DateTimeImmutable|DateTime|string|null [$date])` : `string`
* `getUploadsPath(string [$file], mixed [$date])` : `string`
* `getWpRootFolder()` : `string`
* `haveMuPlugin(string $filename, string $code)` : `void`
* `havePlugin(string $path, string $code)` : `void`
* `haveTheme(string $folder, string $indexFileCode, string [$functionsFileCode])` : `void`
* `makeUploadsDir(string $path)` : `string`
* `openFile(string $filename)` : `void`
* `openUploadedFile(string $filename, DateTime|string|int|null [$date])` : `void`
* `seeFileContentsEqual(string $text)` : `void`
* `seeFileFound(string $filename, string [$path])` : `void`
* `seeInMuPluginFile(string $file, string $contents)` : `void`
* `seeInPluginFile(string $file, string $contents)` : `void`
* `seeInThemeFile(string $file, string $contents)` : `void`
* `seeInThisFile(string $text)` : `void`
* `seeInUploadedFile(string $file, string $contents, string|int|null [$date])` : `void`
* `seeMuPluginFileFound(string $file)` : `void`
* `seeNumberNewLines(int $number)` : `void`
* `seePluginFileFound(string $file)` : `void`
* `seeThemeFileFound(string $file)` : `void`
* `seeThisFileMatches(string $regex)` : `void`
* `seeUploadedFileFound(string $filename, string|int|null [$date])` : `void`
* `writeToFile(string $filename, string $contents)` : `void`
* `writeToMuPluginFile(string $file, string $data)` : `void`
* `writeToPluginFile(string $file, string $data)` : `void`
* `writeToThemeFile(string $file, string $data)` : `void`
* `writeToUploadedFile(string $filename, string $data, DateTime|string|int|null [$date])` : `string`

Read more [in Codeception documentation.][1]

[1]: https://codeception.com/docs/modules/Filesystem

[2]: https://codeception.com/docs/AcceptanceTests

[3]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 

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
***
Sets the current working folder to a folder in a mu-plugin. ``` php <?php $I->amInMuPluginPath('mu-plugin'); ?> ```
#### Parameters

* `string` **$path**
  

<h3>amInPluginPath</h3>
***
Sets the current working folder to a folder in a plugin. ``` php <?php $I->amInPluginPath('my-plugin'); ?> ```
#### Parameters

* `string` **$path**
  

<h3>amInThemePath</h3>
***
Sets the current working folder to a folder in a theme. ``` php <?php $I->amInThemePath('my-theme'); ?> ```
#### Parameters

* `string` **$path**
  

<h3>amInUploadsPath</h3>
***
Enters the uploads folder in the local filesystem.
#### Parameters

* `string` **$path**
  

<h3>cleanMuPluginDir</h3>
***
Cleans a folder in a mu-plugin folder. ``` php <?php $I->cleanMuPluginDir('mu-plugin1/foo'); ?> ```
#### Parameters

* `string` **$dir**
  

<h3>cleanPluginDir</h3>
***
Cleans a folder in a plugin folder. ``` php <?php $I->cleanPluginDir('plugin1/foo'); ?> ```
#### Parameters

* `string` **$dir**
  

<h3>cleanThemeDir</h3>
***
Clears a folder in a theme folder. ``` php <?php $I->cleanThemeDir('my-theme/foo'); ?> ```
#### Parameters

* `string` **$dir**
  

<h3>cleanUploadsDir</h3>
***
Clears a folder in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->cleanUploadsDir('some/folder'); $I->cleanUploadsDir('some/folder', 'today'); ?> ```
#### Parameters

* `string` **$dir**
* `string` **$date**
  

<h3>copyDirToMuPlugin</h3>
***
Copies a folder to a folder in a mu-plugin. ``` php <?php $I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo'); ?> ```
#### Parameters

* `string` **$src**
* `string` **$pluginDst**
  

<h3>copyDirToPlugin</h3>
***
Copies a folder to a folder in a plugin. ``` php <?php $I->copyDirToPlugin(codecept_data_dir('foo'), 'plugin/foo'); ?> ```
#### Parameters

* `string` **$src**
* `string` **$pluginDst**
  

<h3>copyDirToTheme</h3>
***
Copies a folder in a theme folder. ``` php <?php $I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme'); ?> ```
#### Parameters

* `string` **$src**
* `string` **$themeDst**
  

<h3>copyDirToUploads</h3>
***
Copies a folder to the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo'); $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today'); ?> ```
#### Parameters

* `string` **$src**
* `string` **$dst**
* `string` **$date**
  

<h3>deleteMuPluginFile</h3>
***
Deletes a file in a mu-plugin folder. ``` php <?php $I->deleteMuPluginFile('mu-plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>deletePluginFile</h3>
***
Deletes a file in a plugin folder. ``` php <?php $I->deletePluginFile('plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>deleteThemeFile</h3>
***
Deletes a file in a theme folder. ``` php <?php $I->deleteThemeFile('my-theme/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>deleteUploadedDir</h3>
***
Deletes a dir in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path.
<pre><code class="language-php">    &lt;?php
    $I-&gt;deleteUploadedDir('folder');
    $I-&gt;deleteUploadedDir('folder', 'today');
    ?&gt;</code></pre>
<pre><code>                                   if not passed.</code></pre>
#### Parameters

* `string` **$dir** - The path to the directory to delete, relative to the uploads folder.
* `string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)` **$date** - The date of the uploads to delete, will default to <code>now</code>
  

<h3>deleteUploadedFile</h3>
***
Deletes a file in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->deleteUploadedFile('some-file.txt'); $I->deleteUploadedFile('some-file.txt', 'today'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$date**
  

<h3>dontSeeInMuPluginFile</h3>
***
Checks that a file in a mu-plugin folder does not contain a string. ``` php <?php $I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>dontSeeInPluginFile</h3>
***
Checks that a file in a plugin folder does not contain a string. ``` php <?php $I->dontSeeInPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>dontSeeInThemeFile</h3>
***
Checks that a file in a theme folder does not contain a string. ``` php <?php $I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>dontSeeInUploadedFile</h3>
***
Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->dontSeeInUploadedFile('some-file.txt', 'foo'); $I->dontSeeInUploadedFile('some-file.txt','foo', 'today'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
* `string` **$date**
  

<h3>dontSeeMuPluginFileFound</h3>
***
Checks that a file is not found in a mu-plugin folder. ``` php <?php $I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>dontSeePluginFileFound</h3>
***
Checks that a file is not found in a plugin folder. ``` php <?php $I->dontSeePluginFileFound('plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>dontSeeThemeFileFound</h3>
***
Checks that a file is not found in a theme folder. ``` php <?php $I->dontSeeThemeFileFound('my-theme/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>dontSeeUploadedFileFound</h3>
***
Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->dontSeeUploadedFileFound('some-file.txt'); $I->dontSeeUploadedFileFound('some-file.txt','today'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$date**
  

<h3>getBlogUploadsPath</h3>
***
Returns the absolute path to a blog uploads folder or file.
#### Parameters

* `int` **$blogId** - The blog ID to get the path for.
* `string` **$file** - The path, relatitve to the blog uploads folder, to the file or folder.
* `null` **$date** - The date that should be used to build the uploads sub-folders in the year/month format;
  

<h3>getUploadsPath</h3>
***
Returns the path to the specified uploads file of folder. Not providing a value for `$file` and `$date` will return the uploads folder path. a UNIX timestamp or a string supported by the `strtotime` function; defaults to `now`.
#### Parameters

* `string` **$file** - The file path, relative to the uploads folder.
* `null` **$date** - The date that should be used to build the uploads sub-folders in the year/month format;
  

<h3>getWpRootFolder</h3>
***
Returns the absolute path to WordPress root folder without trailing slash.
  

<h3>haveMuPlugin</h3>
***
Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code should not contain the opening '<?php' tag. ``` php <?php $code = 'echo "Hello world!"'; $I->haveMuPlugin('foo-mu-plugin.php', $code); ?> ``` plugin file to create. php tag.
#### Parameters

* `string` **$filename** - The path, relative to the plugins folder, of the
* `string` **$code** - The content of the plugin file without the opening
  

<h3>havePlugin</h3>
***
Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code should not contain the opening '<?php' tag. ``` php <?php $code = 'echo "Hello world!"'; $I->havePlugin('foo/plugin.php', $code); ?> ``` plugin file to create. php tag.
#### Parameters

* `string` **$path** - The path, relative to the plugins folder, of the
* `string` **$code** - The content of the plugin file without the opening
  

<h3>haveTheme</h3>
***
Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code should not contain the opening '<?php' tag. ``` php <?php $code = 'sayHi();'; $functionsCode  = 'function sayHi(){echo "Hello world";};'; $I->haveTheme('foo', $indexCode, $functionsCode); ?> ``` folder, of the plugin folder to create. without the opening php tag. file without the opening php tag.
#### Parameters

* `string` **$folder** - The path, relative to the themes
* `string` **$indexFileCode** - The content of the theme index.php file
* `string` **$functionsFileCode** - The content of the theme functions.php
  

<h3>makeUploadsDir</h3>
***
Creates an empty folder in the WordPress installation uploads folder.
<pre><code class="language-php">    $logsDir = $I-&gt;makeUploadsDir('logs/acme');</code></pre>
<pre><code>                    to create.</code></pre>
#### Parameters

* `string` **$path** - The path, relative to the WordPress installation uploads folder, of the folder
  

<h3>openUploadedFile</h3>
***
Opens a file in the the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->openUploadedFile('some-file.txt'); $I->openUploadedFile('some-file.txt', 'time'); ?> ```
#### Parameters

* `string` **$filename**
* `string` **$date**
  

<h3>seeInMuPluginFile</h3>
***
Checks that a file in a mu-plugin folder contains a string. ``` php <?php $I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>seeInPluginFile</h3>
***
Checks that a file in a plugin folder contains a string. ``` php <?php $I->seeInPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>seeInThemeFile</h3>
***
Checks that a file in a theme folder contains a string. ``` php <?php $I->seeInThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
  

<h3>seeInUploadedFile</h3>
***
Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->seeInUploadedFile('some-file.txt', 'foo'); $I->seeInUploadedFile('some-file.txt','foo', 'today'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$contents**
* `string` **$date**
  

<h3>seeMuPluginFileFound</h3>
***
Checks that a file is found in a mu-plugin folder. ``` php <?php $I->seeMuPluginFileFound('mu-plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>seePluginFileFound</h3>
***
Checks that a file is found in a plugin folder. ``` php <?php $I->seePluginFileFound('plugin1/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>seeThemeFileFound</h3>
***
Checks that a file is found in a theme folder. ``` php <?php $I->seeThemeFileFound('my-theme/some-file.txt'); ?> ```
#### Parameters

* `string` **$file**
  

<h3>seeUploadedFileFound</h3>
***
Checks if file exists in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. Opens a file when it's exists ``` php <?php $I->seeUploadedFileFound('some-file.txt'); $I->seeUploadedFileFound('some-file.txt','today'); ?> ```
#### Parameters

* `string` **$filename**
* `string` **$date**
  

<h3>writeToMuPluginFile</h3>
***
Writes a file in a mu-plugin folder. ``` php <?php $I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$data**
  

<h3>writeToPluginFile</h3>
***
Writes a file in a plugin folder. ``` php <?php $I->writeToPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$data**
  

<h3>writeToThemeFile</h3>
***
Writes a string to a file in a theme folder. ``` php <?php $I->writeToThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
#### Parameters

* `string` **$file**
* `string` **$data**
  

<h3>writeToUploadedFile</h3>
***
Writes a string to a file in the the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->writeToUploadedFile('some-file.txt', 'foo bar'); $I->writeToUploadedFile('some-file.txt', 'foo bar', 'today'); ?> ```
#### Parameters

* `string` **$filename**
* `string` **$data**
* `string` **$date**
</br>

*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

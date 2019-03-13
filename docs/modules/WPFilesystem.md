<!--doc-->


<h2>Public API</h2>
<nav>
	<ul>
		<li>
			<a href="#amInMuPluginPath">amInMuPluginPath</a>
		</li>
		<li>
			<a href="#amInPluginPath">amInPluginPath</a>
		</li>
		<li>
			<a href="#amInThemePath">amInThemePath</a>
		</li>
		<li>
			<a href="#amInUploadsPath">amInUploadsPath</a>
		</li>
		<li>
			<a href="#cleanMuPluginDir">cleanMuPluginDir</a>
		</li>
		<li>
			<a href="#cleanPluginDir">cleanPluginDir</a>
		</li>
		<li>
			<a href="#cleanThemeDir">cleanThemeDir</a>
		</li>
		<li>
			<a href="#cleanUploadsDir">cleanUploadsDir</a>
		</li>
		<li>
			<a href="#copyDirToMuPlugin">copyDirToMuPlugin</a>
		</li>
		<li>
			<a href="#copyDirToPlugin">copyDirToPlugin</a>
		</li>
		<li>
			<a href="#copyDirToTheme">copyDirToTheme</a>
		</li>
		<li>
			<a href="#copyDirToUploads">copyDirToUploads</a>
		</li>
		<li>
			<a href="#deleteMuPluginFile">deleteMuPluginFile</a>
		</li>
		<li>
			<a href="#deletePluginFile">deletePluginFile</a>
		</li>
		<li>
			<a href="#deleteThemeFile">deleteThemeFile</a>
		</li>
		<li>
			<a href="#deleteUploadedDir">deleteUploadedDir</a>
		</li>
		<li>
			<a href="#deleteUploadedFile">deleteUploadedFile</a>
		</li>
		<li>
			<a href="#dontSeeInMuPluginFile">dontSeeInMuPluginFile</a>
		</li>
		<li>
			<a href="#dontSeeInPluginFile">dontSeeInPluginFile</a>
		</li>
		<li>
			<a href="#dontSeeInThemeFile">dontSeeInThemeFile</a>
		</li>
		<li>
			<a href="#dontSeeInUploadedFile">dontSeeInUploadedFile</a>
		</li>
		<li>
			<a href="#dontSeeMuPluginFileFound">dontSeeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#dontSeePluginFileFound">dontSeePluginFileFound</a>
		</li>
		<li>
			<a href="#dontSeeThemeFileFound">dontSeeThemeFileFound</a>
		</li>
		<li>
			<a href="#dontSeeUploadedFileFound">dontSeeUploadedFileFound</a>
		</li>
		<li>
			<a href="#getBlogUploadsPath">getBlogUploadsPath</a>
		</li>
		<li>
			<a href="#getUploadsPath">getUploadsPath</a>
		</li>
		<li>
			<a href="#getWpRootFolder">getWpRootFolder</a>
		</li>
		<li>
			<a href="#haveMuPlugin">haveMuPlugin</a>
		</li>
		<li>
			<a href="#havePlugin">havePlugin</a>
		</li>
		<li>
			<a href="#haveTheme">haveTheme</a>
		</li>
		<li>
			<a href="#makeUploadsDir">makeUploadsDir</a>
		</li>
		<li>
			<a href="#openUploadedFile">openUploadedFile</a>
		</li>
		<li>
			<a href="#seeInMuPluginFile">seeInMuPluginFile</a>
		</li>
		<li>
			<a href="#seeInPluginFile">seeInPluginFile</a>
		</li>
		<li>
			<a href="#seeInThemeFile">seeInThemeFile</a>
		</li>
		<li>
			<a href="#seeInUploadedFile">seeInUploadedFile</a>
		</li>
		<li>
			<a href="#seeMuPluginFileFound">seeMuPluginFileFound</a>
		</li>
		<li>
			<a href="#seePluginFileFound">seePluginFileFound</a>
		</li>
		<li>
			<a href="#seeThemeFileFound">seeThemeFileFound</a>
		</li>
		<li>
			<a href="#seeUploadedFileFound">seeUploadedFileFound</a>
		</li>
		<li>
			<a href="#writeToMuPluginFile">writeToMuPluginFile</a>
		</li>
		<li>
			<a href="#writeToPluginFile">writeToPluginFile</a>
		</li>
		<li>
			<a href="#writeToThemeFile">writeToThemeFile</a>
		</li>
		<li>
			<a href="#writeToUploadedFile">writeToUploadedFile</a>
		</li>
	</ul>
</nav>

<h4 id="amInMuPluginPath">amInMuPluginPath</h4>

***

Sets the current working folder to a folder in a mu-plugin. ``` php <?php $I->amInMuPluginPath('mu-plugin'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong></li></ul>
<h4 id="amInPluginPath">amInPluginPath</h4>

***

Sets the current working folder to a folder in a plugin. ``` php <?php $I->amInPluginPath('my-plugin'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong></li></ul>
<h4 id="amInThemePath">amInThemePath</h4>

***

Sets the current working folder to a folder in a theme. ``` php <?php $I->amInThemePath('my-theme'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong></li></ul>
<h4 id="amInUploadsPath">amInUploadsPath</h4>

***

Enters the uploads folder in the local filesystem.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong> = <em>null</em></li></ul>
<h4 id="cleanMuPluginDir">cleanMuPluginDir</h4>

***

Cleans a folder in a mu-plugin folder. ``` php <?php $I->cleanMuPluginDir('mu-plugin1/foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$dir</strong></li></ul>
<h4 id="cleanPluginDir">cleanPluginDir</h4>

***

Cleans a folder in a plugin folder. ``` php <?php $I->cleanPluginDir('plugin1/foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$dir</strong></li></ul>
<h4 id="cleanThemeDir">cleanThemeDir</h4>

***

Clears a folder in a theme folder. ``` php <?php $I->cleanThemeDir('my-theme/foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$dir</strong></li></ul>
<h4 id="cleanUploadsDir">cleanUploadsDir</h4>

***

Clears a folder in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->cleanUploadsDir('some/folder'); $I->cleanUploadsDir('some/folder', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$dir</strong> = <em>null</em></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="copyDirToMuPlugin">copyDirToMuPlugin</h4>

***

Copies a folder to a folder in a mu-plugin. ``` php <?php $I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$src</strong></li>
<li><em>string</em> <strong>$pluginDst</strong></li></ul>
<h4 id="copyDirToPlugin">copyDirToPlugin</h4>

***

Copies a folder to a folder in a plugin. ``` php <?php $I->copyDirToPlugin(codecept_data_dir('foo'), 'plugin/foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$src</strong></li>
<li><em>string</em> <strong>$pluginDst</strong></li></ul>
<h4 id="copyDirToTheme">copyDirToTheme</h4>

***

Copies a folder in a theme folder. ``` php <?php $I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$src</strong></li>
<li><em>string</em> <strong>$themeDst</strong></li></ul>
<h4 id="copyDirToUploads">copyDirToUploads</h4>

***

Copies a folder to the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo'); $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$src</strong></li>
<li><em>string</em> <strong>$dst</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="deleteMuPluginFile">deleteMuPluginFile</h4>

***

Deletes a file in a mu-plugin folder. ``` php <?php $I->deleteMuPluginFile('mu-plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="deletePluginFile">deletePluginFile</h4>

***

Deletes a file in a plugin folder. ``` php <?php $I->deletePluginFile('plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="deleteThemeFile">deleteThemeFile</h4>

***

Deletes a file in a theme folder. ``` php <?php $I->deleteThemeFile('my-theme/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="deleteUploadedDir">deleteUploadedDir</h4>

***

Deletes a dir in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path.
<pre><code class="language-php">    &lt;?php
    $I-&gt;deleteUploadedDir('folder');
    $I-&gt;deleteUploadedDir('folder', 'today');
    ?&gt;</code></pre>
<pre><code>                                   if not passed.</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><em>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</em> <strong>$date</strong> = <em>null</em> - The date of the uploads to delete, will default to <code>now</code></li></ul>
<h4 id="deleteUploadedFile">deleteUploadedFile</h4>

***

Deletes a file in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->deleteUploadedFile('some-file.txt'); $I->deleteUploadedFile('some-file.txt', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="dontSeeInMuPluginFile">dontSeeInMuPluginFile</h4>

***

Checks that a file in a mu-plugin folder does not contain a string. ``` php <?php $I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="dontSeeInPluginFile">dontSeeInPluginFile</h4>

***

Checks that a file in a plugin folder does not contain a string. ``` php <?php $I->dontSeeInPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="dontSeeInThemeFile">dontSeeInThemeFile</h4>

***

Checks that a file in a theme folder does not contain a string. ``` php <?php $I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="dontSeeInUploadedFile">dontSeeInUploadedFile</h4>

***

Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->dontSeeInUploadedFile('some-file.txt', 'foo'); $I->dontSeeInUploadedFile('some-file.txt','foo', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="dontSeeMuPluginFileFound">dontSeeMuPluginFileFound</h4>

***

Checks that a file is not found in a mu-plugin folder. ``` php <?php $I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="dontSeePluginFileFound">dontSeePluginFileFound</h4>

***

Checks that a file is not found in a plugin folder. ``` php <?php $I->dontSeePluginFileFound('plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="dontSeeThemeFileFound">dontSeeThemeFileFound</h4>

***

Checks that a file is not found in a theme folder. ``` php <?php $I->dontSeeThemeFileFound('my-theme/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="dontSeeUploadedFileFound">dontSeeUploadedFileFound</h4>

***

Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->dontSeeUploadedFileFound('some-file.txt'); $I->dontSeeUploadedFileFound('some-file.txt','today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="getBlogUploadsPath">getBlogUploadsPath</h4>

***

Returns the absolute path to a blog uploads folder or file.
<h5>Parameters</h5><ul>
<li><em>int</em> <strong>$blogId</strong> - The blog ID to get the path for.</li>
<li><em>string</em> <strong>$file</strong> = <em>`''`</em> - The path, relatitve to the blog uploads folder, to the file or folder.</li>
<li><em>null</em> <strong>$date</strong> = <em>null</em> - The date that should be used to build the uploads sub-folders in the year/month format;</li></ul>
<h4 id="getUploadsPath">getUploadsPath</h4>

***

Returns the path to the specified uploads file of folder. Not providing a value for `$file` and `$date` will return the uploads folder path. a UNIX timestamp or a string supported by the `strtotime` function; defaults to `now`.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong> = <em>`''`</em> - The file path, relative to the uploads folder.</li>
<li><em>null</em> <strong>$date</strong> = <em>null</em> - The date that should be used to build the uploads sub-folders in the year/month format;</li></ul>
<h4 id="getWpRootFolder">getWpRootFolder</h4>

***

Returns the absolute path to WordPress root folder without trailing slash.
<h4 id="haveMuPlugin">haveMuPlugin</h4>

***

Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code should not contain the opening '<?php' tag. ``` php <?php $code = 'echo "Hello world!"'; $I->haveMuPlugin('foo-mu-plugin.php', $code); ?> ``` plugin file to create. php tag.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$filename</strong> - The path, relative to the plugins folder, of the</li>
<li><em>string</em> <strong>$code</strong> - The content of the plugin file without the opening</li></ul>
<h4 id="havePlugin">havePlugin</h4>

***

Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code should not contain the opening '<?php' tag. ``` php <?php $code = 'echo "Hello world!"'; $I->havePlugin('foo/plugin.php', $code); ?> ``` plugin file to create. php tag.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong> - The path, relative to the plugins folder, of the</li>
<li><em>string</em> <strong>$code</strong> - The content of the plugin file without the opening</li></ul>
<h4 id="haveTheme">haveTheme</h4>

***

Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code should not contain the opening '<?php' tag. ``` php <?php $code = 'sayHi();'; $functionsCode  = 'function sayHi(){echo "Hello world";};'; $I->haveTheme('foo', $indexCode, $functionsCode); ?> ``` folder, of the plugin folder to create. without the opening php tag. file without the opening php tag.
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$folder</strong> - The path, relative to the themes</li>
<li><em>string</em> <strong>$indexFileCode</strong> - The content of the theme index.php file</li>
<li><em>string</em> <strong>$functionsFileCode</strong> = <em>null</em> - The content of the theme functions.php</li></ul>
<h4 id="makeUploadsDir">makeUploadsDir</h4>

***

Creates an empty folder in the WordPress installation uploads folder.
<pre><code class="language-php">    $logsDir = $I-&gt;makeUploadsDir('logs/acme');</code></pre>
<pre><code>                    to create.</code></pre>
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$path</strong> - The path, relative to the WordPress installation uploads folder, of the folder</li></ul>
<h4 id="openUploadedFile">openUploadedFile</h4>

***

Opens a file in the the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->openUploadedFile('some-file.txt'); $I->openUploadedFile('some-file.txt', 'time'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$filename</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="seeInMuPluginFile">seeInMuPluginFile</h4>

***

Checks that a file in a mu-plugin folder contains a string. ``` php <?php $I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="seeInPluginFile">seeInPluginFile</h4>

***

Checks that a file in a plugin folder contains a string. ``` php <?php $I->seeInPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="seeInThemeFile">seeInThemeFile</h4>

***

Checks that a file in a theme folder contains a string. ``` php <?php $I->seeInThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li></ul>
<h4 id="seeInUploadedFile">seeInUploadedFile</h4>

***

Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->seeInUploadedFile('some-file.txt', 'foo'); $I->seeInUploadedFile('some-file.txt','foo', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$contents</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="seeMuPluginFileFound">seeMuPluginFileFound</h4>

***

Checks that a file is found in a mu-plugin folder. ``` php <?php $I->seeMuPluginFileFound('mu-plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="seePluginFileFound">seePluginFileFound</h4>

***

Checks that a file is found in a plugin folder. ``` php <?php $I->seePluginFileFound('plugin1/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="seeThemeFileFound">seeThemeFileFound</h4>

***

Checks that a file is found in a theme folder. ``` php <?php $I->seeThemeFileFound('my-theme/some-file.txt'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li></ul>
<h4 id="seeUploadedFileFound">seeUploadedFileFound</h4>

***

Checks if file exists in the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. Opens a file when it's exists ``` php <?php $I->seeUploadedFileFound('some-file.txt'); $I->seeUploadedFileFound('some-file.txt','today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$filename</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
<h4 id="writeToMuPluginFile">writeToMuPluginFile</h4>

***

Writes a file in a mu-plugin folder. ``` php <?php $I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$data</strong></li></ul>
<h4 id="writeToPluginFile">writeToPluginFile</h4>

***

Writes a file in a plugin folder. ``` php <?php $I->writeToPluginFile('plugin1/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$data</strong></li></ul>
<h4 id="writeToThemeFile">writeToThemeFile</h4>

***

Writes a string to a file in a theme folder. ``` php <?php $I->writeToThemeFile('my-theme/some-file.txt', 'foo'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$file</strong></li>
<li><em>string</em> <strong>$data</strong></li></ul>
<h4 id="writeToUploadedFile">writeToUploadedFile</h4>

***

Writes a string to a file in the the uploads folder. The date argument can be a string compatible with `strtotime` or a Unix timestamp that will be used to build the `Y/m` uploads subfolder path. ``` php <?php $I->writeToUploadedFile('some-file.txt', 'foo bar'); $I->writeToUploadedFile('some-file.txt', 'foo bar', 'today'); ?> ```
<h5>Parameters</h5><ul>
<li><em>string</em> <strong>$filename</strong></li>
<li><em>string</em> <strong>$data</strong></li>
<li><em>string</em> <strong>$date</strong> = <em>null</em></li></ul>
</br>

*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

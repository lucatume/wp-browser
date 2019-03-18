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

<p>Sets the current working folder to a folder in a mu-plugin. <code>php &lt;?php $I-&gt;amInMuPluginPath('mu-plugin'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong></li></ul>
  

<h3>amInPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a plugin. <code>php &lt;?php $I-&gt;amInPluginPath('my-plugin'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong></li></ul>
  

<h3>amInThemePath</h3>

<hr>

<p>Sets the current working folder to a folder in a theme. <code>php &lt;?php $I-&gt;amInThemePath('my-theme'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong></li></ul>
  

<h3>amInUploadsPath</h3>

<hr>

<p>Enters the uploads folder in the local filesystem.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong></li></ul>
  

<h3>cleanMuPluginDir</h3>

<hr>

<p>Cleans a folder in a mu-plugin folder. <code>php &lt;?php $I-&gt;cleanMuPluginDir('mu-plugin1/foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong></li></ul>
  

<h3>cleanPluginDir</h3>

<hr>

<p>Cleans a folder in a plugin folder. <code>php &lt;?php $I-&gt;cleanPluginDir('plugin1/foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong></li></ul>
  

<h3>cleanThemeDir</h3>

<hr>

<p>Clears a folder in a theme folder. <code>php &lt;?php $I-&gt;cleanThemeDir('my-theme/foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong></li></ul>
  

<h3>cleanUploadsDir</h3>

<hr>

<p>Clears a folder in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;cleanUploadsDir('some/folder'); $I-&gt;cleanUploadsDir('some/folder', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>copyDirToMuPlugin</h3>

<hr>

<p>Copies a folder to a folder in a mu-plugin. <code>php &lt;?php $I-&gt;copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong></li>
<li><code>string</code> <strong>$pluginDst</strong></li></ul>
  

<h3>copyDirToPlugin</h3>

<hr>

<p>Copies a folder to a folder in a plugin. <code>php &lt;?php $I-&gt;copyDirToPlugin(codecept_data_dir('foo'), 'plugin/foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong></li>
<li><code>string</code> <strong>$pluginDst</strong></li></ul>
  

<h3>copyDirToTheme</h3>

<hr>

<p>Copies a folder in a theme folder. <code>php &lt;?php $I-&gt;copyDirToTheme(codecept_data_dir('foo'), 'my-theme'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong></li>
<li><code>string</code> <strong>$themeDst</strong></li></ul>
  

<h3>copyDirToUploads</h3>

<hr>

<p>Copies a folder to the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo'); $I-&gt;copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong></li>
<li><code>string</code> <strong>$dst</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>deleteMuPluginFile</h3>

<hr>

<p>Deletes a file in a mu-plugin folder. <code>php &lt;?php $I-&gt;deleteMuPluginFile('mu-plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>deletePluginFile</h3>

<hr>

<p>Deletes a file in a plugin folder. <code>php &lt;?php $I-&gt;deletePluginFile('plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>deleteThemeFile</h3>

<hr>

<p>Deletes a file in a theme folder. <code>php &lt;?php $I-&gt;deleteThemeFile('my-theme/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>deleteUploadedDir</h3>

<hr>

<p>Deletes a dir in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    &lt;?php
    $I-&gt;deleteUploadedDir('folder');
    $I-&gt;deleteUploadedDir('folder', 'today');
    ?&gt;</code></pre>
<pre><code>                                   if not passed.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code></li></ul>
  

<h3>deleteUploadedFile</h3>

<hr>

<p>Deletes a file in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;deleteUploadedFile('some-file.txt'); $I-&gt;deleteUploadedFile('some-file.txt', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>dontSeeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder does not contain a string. <code>php &lt;?php $I-&gt;dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>dontSeeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder does not contain a string. <code>php &lt;?php $I-&gt;dontSeeInPluginFile('plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>dontSeeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder does not contain a string. <code>php &lt;?php $I-&gt;dontSeeInThemeFile('my-theme/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>dontSeeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;dontSeeInUploadedFile('some-file.txt', 'foo'); $I-&gt;dontSeeInUploadedFile('some-file.txt','foo', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>dontSeeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a mu-plugin folder. <code>php &lt;?php $I-&gt;dontSeeMuPluginFileFound('mu-plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>dontSeePluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a plugin folder. <code>php &lt;?php $I-&gt;dontSeePluginFileFound('plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>dontSeeThemeFileFound</h3>

<hr>

<p>Checks that a file is not found in a theme folder. <code>php &lt;?php $I-&gt;dontSeeThemeFileFound('my-theme/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>dontSeeUploadedFileFound</h3>

<hr>

<p>Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;dontSeeUploadedFileFound('some-file.txt'); $I-&gt;dontSeeUploadedFileFound('some-file.txt','today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>getBlogUploadsPath</h3>

<hr>

<p>Returns the absolute path to a blog uploads folder or file.</p>
<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$blogId</strong> - The blog ID to get the path for.</li>
<li><code>string</code> <strong>$file</strong> - The path, relatitve to the blog uploads folder, to the file or folder.</li>
<li><code>null</code> <strong>$date</strong> - The date that should be used to build the uploads sub-folders in the year/month format;</li></ul>
  

<h3>getUploadsPath</h3>

<hr>

<p>Returns the path to the specified uploads file of folder. Not providing a value for <code>$file</code> and <code>$date</code> will return the uploads folder path. a UNIX timestamp or a string supported by the <code>strtotime</code> function; defaults to <code>now</code>.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder.</li>
<li><code>null</code> <strong>$date</strong> - The date that should be used to build the uploads sub-folders in the year/month format;</li></ul>
  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to WordPress root folder without trailing slash.</p></ul>
  

<h3>haveMuPlugin</h3>

<hr>

<p>Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code should not contain the opening '&lt;?php' tag. <code>php &lt;?php $code = 'echo "Hello world!"'; $I-&gt;haveMuPlugin('foo-mu-plugin.php', $code); ?&gt;</code> plugin file to create. php tag.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path, relative to the plugins folder, of the</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file without the opening</li></ul>
  

<h3>havePlugin</h3>

<hr>

<p>Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code should not contain the opening '&lt;?php' tag. <code>php &lt;?php $code = 'echo "Hello world!"'; $I-&gt;havePlugin('foo/plugin.php', $code); ?&gt;</code> plugin file to create. php tag.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the plugins folder, of the</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file without the opening</li></ul>
  

<h3>haveTheme</h3>

<hr>

<p>Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code should not contain the opening '&lt;?php' tag. <code>php &lt;?php $code = 'sayHi();'; $functionsCode  = 'function sayHi(){echo "Hello world";};'; $I-&gt;haveTheme('foo', $indexCode, $functionsCode); ?&gt;</code> folder, of the plugin folder to create. without the opening php tag. file without the opening php tag.</p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$folder</strong> - The path, relative to the themes</li>
<li><code>string</code> <strong>$indexFileCode</strong> - The content of the theme index.php file</li>
<li><code>string</code> <strong>$functionsFileCode</strong> - The content of the theme functions.php</li></ul>
  

<h3>makeUploadsDir</h3>

<hr>

<p>Creates an empty folder in the WordPress installation uploads folder.</p>
<pre><code class="language-php">    $logsDir = $I-&gt;makeUploadsDir('logs/acme');</code></pre>
<pre><code>                    to create.</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the WordPress installation uploads folder, of the folder</li></ul>
  

<h3>openUploadedFile</h3>

<hr>

<p>Opens a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;openUploadedFile('some-file.txt'); $I-&gt;openUploadedFile('some-file.txt', 'time'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>seeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder contains a string. <code>php &lt;?php $I-&gt;seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>seeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder contains a string. <code>php &lt;?php $I-&gt;seeInPluginFile('plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>seeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder contains a string. <code>php &lt;?php $I-&gt;seeInThemeFile('my-theme/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li></ul>
  

<h3>seeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;seeInUploadedFile('some-file.txt', 'foo'); $I-&gt;seeInUploadedFile('some-file.txt','foo', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$contents</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>seeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is found in a mu-plugin folder. <code>php &lt;?php $I-&gt;seeMuPluginFileFound('mu-plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>seePluginFileFound</h3>

<hr>

<p>Checks that a file is found in a plugin folder. <code>php &lt;?php $I-&gt;seePluginFileFound('plugin1/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>seeThemeFileFound</h3>

<hr>

<p>Checks that a file is found in a theme folder. <code>php &lt;?php $I-&gt;seeThemeFileFound('my-theme/some-file.txt'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li></ul>
  

<h3>seeUploadedFileFound</h3>

<hr>

<p>Checks if file exists in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. Opens a file when it's exists <code>php &lt;?php $I-&gt;seeUploadedFileFound('some-file.txt'); $I-&gt;seeUploadedFileFound('some-file.txt','today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
  

<h3>writeToMuPluginFile</h3>

<hr>

<p>Writes a file in a mu-plugin folder. <code>php &lt;?php $I-&gt;writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$data</strong></li></ul>
  

<h3>writeToPluginFile</h3>

<hr>

<p>Writes a file in a plugin folder. <code>php &lt;?php $I-&gt;writeToPluginFile('plugin1/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$data</strong></li></ul>
  

<h3>writeToThemeFile</h3>

<hr>

<p>Writes a string to a file in a theme folder. <code>php &lt;?php $I-&gt;writeToThemeFile('my-theme/some-file.txt', 'foo'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong></li>
<li><code>string</code> <strong>$data</strong></li></ul>
  

<h3>writeToUploadedFile</h3>

<hr>

<p>Writes a string to a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path. <code>php &lt;?php $I-&gt;writeToUploadedFile('some-file.txt', 'foo bar'); $I-&gt;writeToUploadedFile('some-file.txt', 'foo bar', 'today'); ?&gt;</code></p>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong></li>
<li><code>string</code> <strong>$data</strong></li>
<li><code>string</code> <strong>$date</strong></li></ul>
</br>

*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

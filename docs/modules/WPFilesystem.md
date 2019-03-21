# WPFilesystem module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Filesystem module](https://codeception.com/docs/modules/Filesystem) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress filesystem **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  
This module also provides methods to scaffold plugins and themes on the fly in the context of tests and auto-remove them after each test.

## Configuration

* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `themes` - defaults to `/wp-content/themes`; the path, relative to the the WordPress installaion root folder, to the themes folder.
* `plugins` - defaults to `/wp-content/plugins`; the path, relative to the WordPress installation root folder, to the plugins folder.
* `mu-plugins` - defaults to `wp-content/mu-plugins`; the path, relative to the WordPress installation root folder, to the must-use plugins folder.
* `uploads` - defaults to `/wp-content/uploads`; the path, relative to the WordPress installation root folder, to the uploads folder.
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
<pre><code class="language-php">    $I-&gt;amInMuPluginPath('mu-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>amInPluginPath</h3>

<hr>

<p>Sets the current working folder to a folder in a plugin.</p>
<pre><code class="language-php">    $I-&gt;amInPluginPath('my-plugin');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The folder path, relative to the root uploads folder, to change to.</li></ul>
  

<h3>amInThemePath</h3>

<hr>

<p>Sets the current working folder to a folder in a theme.</p>
<pre><code class="language-php">    $I-&gt;amInThemePath('my-theme');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the theme folder, relative to themes root folder.</li></ul>
  

<h3>amInUploadsPath</h3>

<hr>

<p>Enters, changing directory, to the uploads folder in the local filesystem.</p>
<pre><code class="language-php">    $I-&gt;amInUploadsPath('/logs');
    $I-&gt;seeFileFound('shop.log');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path, relative to the site uploads folder.</li></ul>
  

<h3>cleanMuPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a mu-plugin folder.</p>
<pre><code class="language-php">    $I-&gt;cleanMuPluginDir('mu-plugin1/foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory, relative to the mu-plugins root folder.</li></ul>
  

<h3>cleanPluginDir</h3>

<hr>

<p>Cleans, emptying it, a folder in a plugin folder.</p>
<pre><code class="language-php">    $I-&gt;cleanPluginDir('my-plugin/foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the plugins root folder.</li></ul>
  

<h3>cleanThemeDir</h3>

<hr>

<p>Clears, emptying it, a folder in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;cleanThemeDir('my-theme/foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the folder, relative to the themese root folder.</li></ul>
  

<h3>cleanUploadsDir</h3>

<hr>

<p>Clears a folder in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;cleanUploadsDir('some/folder');
    $I-&gt;cleanUploadsDir('some/folder', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>copyDirToMuPlugin</h3>

<hr>

<p>Copies a folder to a folder in a mu-plugin.</p>
<pre><code class="language-php">    $I-&gt;copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The path to the destination folder, relative to the mu-plugins root folder.</li></ul>
  

<h3>copyDirToPlugin</h3>

<hr>

<p>Copies a folder to a folder in a plugin.</p>
<pre><code class="language-php">    // Copy the 'foo' folder to the 'foo' folder in the plugin.
    $I-&gt;copyDirToPlugin(codecept_data_dir('foo'), 'my-plugin/foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source directory to copy.</li>
<li><code>string</code> <strong>$pluginDst</strong> - The destination path, relative to the plugins root folder.</li></ul>
  

<h3>copyDirToTheme</h3>

<hr>

<p>Copies a folder in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;copyDirToTheme(codecept_data_dir('foo'), 'my-theme');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file.</li>
<li><code>string</code> <strong>$themeDst</strong> - The path to the destination folder, relative to the themes root folder.</li></ul>
  

<h3>copyDirToUploads</h3>

<hr>

<p>Copies a folder to the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo');
    $I-&gt;copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$src</strong> - The path to the source file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$dst</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteMuPluginFile</h3>

<hr>

<p>Deletes a file in a mu-plugin folder.</p>
<pre><code class="language-php">    $I-&gt;deleteMuPluginFile('mu-plugin1/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li></ul>
  

<h3>deletePluginFile</h3>

<hr>

<p>Deletes a file in a plugin folder.</p>
<pre><code class="language-php">    $I-&gt;deletePluginFile('my-plugin/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The folder path, relative to the plugins root folder.</li></ul>
  

<h3>deleteThemeFile</h3>

<hr>

<p>Deletes a file in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;deleteThemeFile('my-theme/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file to delete, relative to the themes root folder.</li></ul>
  

<h3>deleteUploadedDir</h3>

<hr>

<p>Deletes a dir in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;deleteUploadedDir('folder');
    $I-&gt;deleteUploadedDir('folder', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$dir</strong> - The path to the directory to delete, relative to the uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>deleteUploadedFile</h3>

<hr>

<p>Deletes a file in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;deleteUploadedFile('some-file.txt');
    $I-&gt;deleteUploadedFile('some-file.txt', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder does not contain a string.</p>
<pre><code class="language-php">    $I-&gt;dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder does not contain a string.</p>
<pre><code class="language-php">    $I-&gt;dontSeeInPluginFile('my-plugin/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder does not contain a string.</p>
<pre><code class="language-php">    $I-&gt;dontSeeInThemeFile('my-theme/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>dontSeeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder does contain a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;dontSeeInUploadedFile('some-file.txt', 'foo');
    $I-&gt;dontSeeInUploadedFile('some-file.txt','foo', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The not expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>dontSeeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a mu-plugin folder.</p>
<pre><code class="language-php">    $I-&gt;dontSeeMuPluginFileFound('mu-plugin1/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>dontSeePluginFileFound</h3>

<hr>

<p>Checks that a file is not found in a plugin folder.</p>
<pre><code class="language-php">    $I-&gt;dontSeePluginFileFound('my-plugin/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li></ul>
  

<h3>dontSeeThemeFileFound</h3>

<hr>

<p>Checks that a file is not found in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;dontSeeThemeFileFound('my-theme/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>dontSeeUploadedFileFound</h3>

<hr>

<p>Checks thata a file does not exist in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;dontSeeUploadedFileFound('some-file.txt');
    $I-&gt;dontSeeUploadedFileFound('some-file.txt','today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

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

<p>Returns the path to the specified uploads file of folder. Not providing a value for <code>$file</code> and <code>$date</code> will return the uploads folder path.</p>
<pre><code class="language-php">    $todaysPath = $I-&gt;getUploadsPath();
    $lastWeek = $I-&gt;getUploadsPath('', '-1 week');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to WordPress root folder without trailing slash.</p>
  

<h3>haveMuPlugin</h3>

<hr>

<p>Creates a mu-plugin file, including plugin header, in the mu-plugins folder. The code should <strong>not</strong> contain the opening '&lt;?php' tag.</p>
<pre><code class="language-php">    $code = 'echo "Hello world!"';
    $I-&gt;haveMuPlugin('foo-mu-plugin.php', $code);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file to create, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file without the opening PHP tag.</li></ul>
  

<h3>havePlugin</h3>

<hr>

<p>Creates a plugin file, including plugin header, in the plugins folder. The plugin is just created and not activated; the code should <strong>not</strong> contain the opening '&lt;?php' tag.</p>
<pre><code class="language-php">    $code = 'echo "Hello world!"';
    $I-&gt;havePlugin('foo/plugin.php', $code);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$path</strong> - The path to the file to create, relative to the plugins folder.</li>
<li><code>string</code> <strong>$code</strong> - The content of the plugin file without the opening PHP tag.</li></ul>
  

<h3>haveTheme</h3>

<hr>

<p>Creates a theme file structure, including theme style file and index, in the themes folder. The theme is just created and not activated; the code should not contain the opening '&lt;?php' tag.</p>
<pre><code class="language-php">    $code = 'sayHi();';
    $functionsCode  = 'function sayHi(){echo "Hello world";};';
    $I-&gt;haveTheme('foo', $indexCode, $functionsCode);</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$folder</strong> - The path to the theme to create, relative to the themes root folder.</li>
<li><code>string</code> <strong>$indexFileCode</strong> - The content of the theme index.php file without the opening PHP tag.</li>
<li><code>string</code> <strong>$functionsFileCode</strong> - The content of the theme functions.php file without the opening PHP tag.</li></ul>
  

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

<p>Opens a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;openUploadedFile('some-file.txt');
    $I-&gt;openUploadedFile('some-file.txt', 'time');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the file, relative to the current uploads folder.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>
  

<h3>seeInMuPluginFile</h3>

<hr>

<p>Checks that a file in a mu-plugin folder contains a string.</p>
<pre><code class="language-php">    $I-&gt;seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path the file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInPluginFile</h3>

<hr>

<p>Checks that a file in a plugin folder contains a string.</p>
<pre><code class="language-php">    $I-&gt;seeInPluginFile('my-plugin/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInThemeFile</h3>

<hr>

<p>Checks that a file in a theme folder contains a string.</p>
<pre><code class="language-php">    &lt;?php
    $I-&gt;seeInThemeFile('my-theme/some-file.txt', 'foo');
    ?&gt;</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li>
<li><code>string</code> <strong>$contents</strong> - The contents to check the file for.</li></ul>
  

<h3>seeInUploadedFile</h3>

<hr>

<p>Checks that a file in the uploads folder contains a string. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;seeInUploadedFile('some-file.txt', 'foo');
    $I-&gt;seeInUploadedFile('some-file.txt','foo', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string</code> <strong>$contents</strong> - The expected file contents or part of them.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>seeMuPluginFileFound</h3>

<hr>

<p>Checks that a file is found in a mu-plugin folder.</p>
<pre><code class="language-php">    $I-&gt;seeMuPluginFileFound('mu-plugin1/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the mu-plugins folder.</li></ul>
  

<h3>seePluginFileFound</h3>

<hr>

<p>Checks that a file is found in a plugin folder.</p>
<pre><code class="language-php">    $I-&gt;seePluginFileFound('my-plugin/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to thep plugins root folder.</li></ul>
  

<h3>seeThemeFileFound</h3>

<hr>

<p>Checks that a file is found in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;seeThemeFileFound('my-theme/some-file.txt');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themes root folder.</li></ul>
  

<h3>seeUploadedFileFound</h3>

<hr>

<p>Checks if file exists in the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;seeUploadedFileFound('some-file.txt');
    $I-&gt;seeUploadedFileFound('some-file.txt','today');
    ?&gt;</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The file path, relative to the uploads folder or the current folder.</li>
<li><code>string/int</code> <strong>$date</strong> - A string compatible with <code>strtotime</code> or a Unix timestamp.</li></ul>
  

<h3>writeToMuPluginFile</h3>

<hr>

<p>Writes a file in a mu-plugin folder.</p>
<pre><code class="language-php">    $I-&gt;writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the destination file, relative to the mu-plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToPluginFile</h3>

<hr>

<p>Writes a file in a plugin folder.</p>
<pre><code class="language-php">    $I-&gt;writeToPluginFile('my-plugin/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the plugins root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write in the file.</li></ul>
  

<h3>writeToThemeFile</h3>

<hr>

<p>Writes a string to a file in a theme folder.</p>
<pre><code class="language-php">    $I-&gt;writeToThemeFile('my-theme/some-file.txt', 'foo');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$file</strong> - The path to the file, relative to the themese root folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li></ul>
  

<h3>writeToUploadedFile</h3>

<hr>

<p>Writes a string to a file in the the uploads folder. The date argument can be a string compatible with <code>strtotime</code> or a Unix timestamp that will be used to build the <code>Y/m</code> uploads subfolder path.</p>
<pre><code class="language-php">    $I-&gt;writeToUploadedFile('some-file.txt', 'foo bar');
    $I-&gt;writeToUploadedFile('some-file.txt', 'foo bar', 'today');</code></pre>
<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$filename</strong> - The path to the destination file, relative to the current uploads folder.</li>
<li><code>string</code> <strong>$data</strong> - The data to write to the file.</li>
<li><code>string/int/[\DateTime](http://php.net/manual/en/class.datetime.php)</code> <strong>$date</strong> - The date of the uploads to delete, will default to <code>now</code>.</li></ul>


*This class extends \Codeception\Module\Filesystem*

<!--/doc-->

## Setting up a minimum WordPress installation

As mentioned in [Installation](installation.md) section wp-browser will **not** download, configure and install WordPress for you.  

On a high level, once WordPress is installed and configured, whatever local development environment solution you've used, there are some information you'll need to gather before moving into wp-browser configuration.  

While there will be a section dedicated to different environments and setups I will outline below the example setup I will use, in the next section, to configure wp-browser:

* WordPress is installed, on my machine, at `/Users/luca/Sites/wordpress`.
* I'm running MySQL server locally; I can connect to the MySQL server with the command `mysql -u root -h 127.0.0.1 -P 3306`; there is no password.
* I've created two databases, `wordpress` and `tests`, with the command:
    ```bash
    mysql -u root -h 127.0.0.1 -P 3306 -e "create database if not exists wordpress; create database if not exists tests"
    ```
* I've configured the `/Users/luca/Sites/wordpress/wp-config.php` file like below (redacted for brevity):
    ```php
    <?php
    define( 'DB_NAME', 'wordpress' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', '' );
    define( 'DB_HOST', '127.0.0.1' );
    define( 'DB_CHARSET', 'utf8' );
    define( 'DB_COLLATE', '' );
    
    $table_prefix = 'wp_';
  
    if ( ! defined( 'ABSPATH' ) )
    	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    
    require_once ABSPATH . 'wp-settings.php';
    ```
* To serve the site I'm using PHP built-in server with the command:
    ```bash
    (cd /Users/luca/Sites/wordpress; php -S localhost:8080)
    ```
* I can access the WordPress homepage at `http://localhost:8080` and the administration area at `http://localhost:8080/wp-admin`.
* I've installed WordPress via its UI (`http://localhost:8080/wp-admin`), the administrator username is `admin`, the administrator password is `password`.
* I'm testing a plugin and that plugin is in the folder, relative to the WordPress root folder, `wp-content/plugins/acme-plugin`.
    
With all the steps above done I can now move into the [actual wp-browser configuration phase](configuration.md).

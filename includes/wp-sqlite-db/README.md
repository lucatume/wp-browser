# wp-sqlite-db

[![Test](https://github.com/aaemnnosttv/wp-sqlite-db/actions/workflows/test.yml/badge.svg)](https://github.com/aaemnnosttv/wp-sqlite-db/actions/workflows/test.yml)
[![Packagist](https://img.shields.io/packagist/v/aaemnnosttv/wp-sqlite-db.svg)](https://packagist.org/packages/aaemnnosttv/wp-sqlite-db)
[![Packagist](https://img.shields.io/packagist/l/aaemnnosttv/wp-sqlite-db.svg)](https://packagist.org/packages/aaemnnosttv/wp-sqlite-db)

A single file drop-in for using a SQLite database with WordPress. Based on the original SQLite Integration plugin.

## Installation

#### Quick Start
- Clone or download this repository
- Copy `src/db.php` into the root of your site's `wp-content` directory

#### Via Composer
- `composer require koodimonni/composer-dropin-installer`
- Add the configuration to your project's `composer.json` under the `extra` key  
```
"extra": {
    "dropin-paths": {
        "wp-content/": ["package:aaemnnosttv/wp-sqlite-db:src/db.php"]
    }
}
```
- `composer require aaemnnosttv/wp-sqlite-db`

## Overview

Once the drop-in is installed, no other configuration is necessary, but some things are configurable.

By default, the SQLite database is located in `wp-content/database/.ht.sqlite`, but you can change this using a few constants.

```php
define('DB_DIR', '/absolute/custom/path/to/directory/for/sqlite/database/file/');
define('DB_FILE', 'custom_filename_for_sqlite_database');
```

## Credit

This project is based on the [SQLite Integration](https://wordpress.org/plugins/sqlite-integration/) plugin by Kojima Toshiyasu.

<?php return 'The WPLoader module could not correctly load WordPress.
If you do not see any other output beside this, probably a call to `die` or `exit` might have been made while loading WordPress files.
There are a number of reasons why this might happen and the most common is an empty, incomplete or incoherent database status.

E.g. you are trying to bootstrap WordPress as multisite on a database that does not contain multisite tables.
Since the `WPLoader::loadOnly` parameter is set to `true` the WPLoader module will not try to populate the database.
The database should be populated from a dump using the WPDb/Db modules.
';

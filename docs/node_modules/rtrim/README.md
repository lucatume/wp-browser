rtrim
============

`rtrim` [Node.js module](https://www.npmjs.com/package/rtrim) returns a string with whitespace (or other characters) stripped from the end of a string. Without dependencies and library bloat.


[![Dependency Status](https://david-dm.org/sergejmueller/rtrim.svg)](https://david-dm.org/sergejmueller/rtrim)
[![Code Climate](https://codeclimate.com/github/sergejmueller/rtrim/badges/gpa.svg)](https://codeclimate.com/github/sergejmueller/rtrim)
[![Build Status](https://travis-ci.org/sergejmueller/rtrim.svg?branch=master)](https://travis-ci.org/sergejmueller/rtrim)


Install
-----

```
npm install rtrim
```


Usage
-----

```javascript
rtrim ( str [, chars ] )
```

`str` → The input string<br>
`chars` → Characters that you want to be stripped

Without the second parameter, `rtrim` will strip whitespaces (spaces, tabs and new lines).


Examples
-----

```javascript
var rtrim = require('rtrim');


/* Strip whitespace from the end of a string */
rtrim( '    Hello    ' ) + ' World' // →    Hello World

/* Strip multiple special chars from the end of a string */
rtrim( '... Hello World ...', ' .' ); // →... Hello World

/* Strip multiple chars from the end of a string */
rtrim( 'Hello World', 'Hdle' ); // →Hello Wor

/* Strip trailing slash from the end of a string */
rtrim( 'https://goo.gl/', '/' ); // →https://goo.gl
```

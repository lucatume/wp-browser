# range-utils

Javascript utilities to work with ranges. A range is an object with a least two property: `offset` and `length`.

[![Build Status](https://travis-ci.org/SamyPesse/range-utils.png?branch=master)](https://travis-ci.org/SamyPesse/range-utils)
[![NPM version](https://badge.fury.io/js/range-utils.svg)](http://badge.fury.io/js/range-utils)


### Installation

```
$ npm install range-utils
```

### Usage


```js
// Initialize a range

var from0To10 = Range(0, 10);
var from5To20 = Range(5, 15);
var withProperty = Range(0, 10, { hello: 'world' });

// Check if a range contains another range
Range.contains(
    Range(0, 10),
    Range(5, 2)
);

// Check that two ranges are collapsing
Range.areCollapsing(
    Range(0, 10),
    Range(9, 10)
);

// Translate a range
Range.moveBy(a, 10);

// Enlarge a range
Range.enlarge(a, 10);
```


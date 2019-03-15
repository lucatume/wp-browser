#! /usr/bin/env node
/* eslint-disable no-console */

var utils = require('./utils');

utils.command(function(content, markup) {
    console.log(markup.toText(content));
});

#! /usr/bin/env node
/* eslint-disable no-console */

var DraftMarkup = require('../');
var utils = require('./utils');

utils.command(function(content) {
    var markup = new DraftMarkup(utils.getSyntax('out.md'));
    var output = markup.toText(content);

    console.log(output);
});

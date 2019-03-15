#! /usr/bin/env node
/* eslint-disable no-console */

var DraftMarkup = require('../');
var textSyntax = require('../syntaxes/text');

var utils = require('./utils');

utils.command(function(content, markup) {
    var textMarkup = new DraftMarkup(textSyntax);
    var output = textMarkup.toText(content);

    console.log(output);
});

#! /usr/bin/env node
/* eslint-disable no-console */

var DraftMarkup = require('../');
var htmlSyntax = require('../syntaxes/html');

var utils = require('./utils');

utils.command(function(content, markup) {
    var htmlMarkup = new DraftMarkup(htmlSyntax);
    var output = htmlMarkup.toText(content);

    console.log(output);
});

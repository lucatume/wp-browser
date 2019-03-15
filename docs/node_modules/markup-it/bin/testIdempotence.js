#! /usr/bin/env node
/* eslint-disable no-console */
require('should');
require('should-html');

var MarkupIt = require('../');
var htmlSyntax = require('../syntaxes/html');

var utils = require('./utils');

utils.command(function(content1, markup) {
    var html = new MarkupIt(htmlSyntax);

    // Render json
    var backToMd = markup.toText(content1);

    // Parse it back
    var content2 = markup.toContent(backToMd);

    // Render the both to html
    var resultHtml1 = html.toText(content1);
    var resultHtml2 = html.toText(content2);

    // Compare the html
    try {
        (resultHtml1).should.be.html(resultHtml2);
        console.log('Test succeeded!');
    } catch(e) {
        console.log('Test failed with: ' + e);

        console.log('--------');
        console.log('Source -> HTML:');
        console.log(resultHtml1);
        console.log('--------');
        console.log('Source -> JSON -> Source -> HTML:');
        console.log(resultHtml2);
    }
});

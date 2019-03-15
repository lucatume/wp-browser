/* eslint-disable no-console */
var fs = require('fs');
var path = require('path');

var DraftMarkup = require('../');

var markdownSyntax = require('../syntaxes/markdown');
var htmlSyntax = require('../syntaxes/html');

var EXT_TO_SYNTAX = {
    '.md':   markdownSyntax,
    '.html': htmlSyntax
};

// Fail with an error message
function fail(msg) {
    console.log('error:', msg);
    process.exit(1);
}

// Get a syntax for a filename
function getSyntax(filename) {
    var ext = path.extname(filename).toLowerCase();

    var syntax = EXT_TO_SYNTAX[ext];
    if (!syntax) {
        fail('non parsable file');
    }

    return syntax;
}

// Execute a function on the provided file content
function command(fn) {
    if (process.argv.length < 3) {
        fail('no input file');
    }

    var filename = path.join(process.cwd(), process.argv[2]);
    var syntax = getSyntax(filename);

    var text = fs.readFileSync(filename, 'utf8');
    var markup = new DraftMarkup(syntax);

    var content = markup.toContent(text, {
        math:     true,
        template: true
    });

    fn(content, markup);
}

module.exports = {
    command: command,
    fail: fail,
    getSyntax: getSyntax
};



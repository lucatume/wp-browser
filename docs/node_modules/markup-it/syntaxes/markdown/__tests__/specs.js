var fs = require('fs');
var path = require('path');

var MarkupIt = require('../../..');
var markdownSyntax = require('../');
var htmlSyntax = require('../../html');

var FIXTURES = path.resolve(__dirname, 'specs');

var markdown = new MarkupIt(markdownSyntax);
var html = new MarkupIt(htmlSyntax);

describe('Markdown Specs', function() {
    var files = fs.readdirSync(FIXTURES);

    describe('MD -> HTML', function() {
        files.forEach(function(file) {
            if (path.extname(file) !== '.md') return;

            it(file, function () {
                var fixture = readFixture(file);
                testMdToHtml(fixture);
            });
        });
    });

    describe('MD -> MD', function() {
        files.forEach(function(file) {
            if (path.extname(file) !== '.md') return;

            it(file, function () {
                var fixture = readFixture(file);
                testMdIdempotence(fixture);
            });
        });
    });
});


function testMdToHtml(fixture) {
    var content = markdown.toContent(fixture.sourceMd);
    var resultHtml = html.toText(content);

    (resultHtml).should.be.html(fixture.sourceHtml);
}

function testMdIdempotence(fixture) {
    // Parse markdown to json
    var content1 = markdown.toContent(fixture.sourceMd);

    // Render json as markdown
    var backToMd = markdown.toText(content1);

    // Parse it back as markdown
    var content2 = markdown.toContent(backToMd);

    // Render the both to html
    var resultHtml1 = html.toText(content1);
    var resultHtml2 = html.toText(content2);

    // Compare the html
    (resultHtml1).should.be.html(resultHtml2);
}

function readFixture(filename) {
    var htmlFilePath = path.basename(filename, '.md') + '.html';

    var sourceMd = fs.readFileSync(path.resolve(FIXTURES, filename), 'utf8');
    var sourceHtml = fs.readFileSync(path.resolve(FIXTURES, htmlFilePath), 'utf8');

    return {
        sourceMd: sourceMd,
        sourceHtml: sourceHtml
    };
}

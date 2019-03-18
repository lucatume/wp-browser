var fs = require('fs');
var path = require('path');
var readMetadata = require('read-metadata');

var SlateUtils = require('../');
var JSONUtils = require('../../json');

describe('SlateUtils', function() {
    var tests = fs.readdirSync(__dirname);

    tests.forEach(function(test) {
        if (test[0] === '.' || path.extname(test).length > 0) return;

        var dir          = path.resolve(__dirname, test);
        var inputPath    = path.resolve(dir, 'input.yaml');
        var expectedPath = path.resolve(dir, 'expected.yaml');
        var backPath     = path.resolve(dir, 'back.yaml');

        it(test + ' (encode)', function() {
            var input    = readMetadata.sync(inputPath);
            var expected = readMetadata.sync(expectedPath);

            input = JSONUtils.decode(input);

            var result = SlateUtils.encode(input);
            result.should.deepEqual(expected);
        });

        if (!fs.existsSync(backPath)) {
            return;
        }

        it(test + ' (decode)', function() {
            var input    = readMetadata.sync(expectedPath);
            var expected = readMetadata.sync(backPath);

            var content = SlateUtils.decode(input);
            var result = JSONUtils.encode(content);

            result.should.deepEqual(expected);
        });
    });
});

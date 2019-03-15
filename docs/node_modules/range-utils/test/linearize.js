require('should');
var Range = require('../lib');

describe('Range.linearize', function() {
    it('should not modified linearized ranges', function() {
        var out = Range.linearize([
            {
                offset: 0,
                length: 10,
                type: 'BOLD'
            },
            {
                offset: 10,
                length: 10,
                type: 'ITALIC'
            }
        ]);

        out.should.have.lengthOf(2);
    });

    it('should linearize ranges', function() {
        Range.linearize([
            {
                offset: 0,
                length: 13,
                type: 'BOLD'
            },
            {
                offset: 10,
                length: 10,
                type: 'ITALIC'
            }
        ]).should.deepEqual([
            {
                offset: 0,
                length: 10,
                type: 'BOLD'
            },
            {
                offset: 10,
                length: 3,
                type: 'BOLD'
            },
            {
                offset: 10,
                length: 3,
                type: 'ITALIC'
            },
            {
                offset: 13,
                length: 7,
                type: 'ITALIC'
            }
        ]);
    });

    it('should not linearized // ranges', function() {
        var ranges = [
            {
                offset: 0,
                length: 10,
                type: 'BOLD'
            },
            {
                offset: 0,
                length: 10,
                type: 'ITALIC'
            }
        ];
        Range.linearize(ranges).should.deepEqual(ranges);
    });
});

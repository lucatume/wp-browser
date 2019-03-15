require('should');
var Range = require('../lib');

describe('Range.sort', function() {
    var ranges = [
        Range(0, 5, { type: 'bold' }),
        Range(5, 10, { type: 'italic' }),
        Range(0, 10, { type: 'link' }),
        Range(15, 5, { type: 'strike' })
    ];

    it('by offset', function() {
        var sorted = Range.sort(ranges);

        sorted.should.have.lengthOf(4);
        sorted[0].type.should.equal('bold');
        sorted[1].type.should.equal('link');
        sorted[2].type.should.equal('italic');
        sorted[3].type.should.equal('strike');
    });

    it('by length', function() {
        var sorted = Range.sortByLength(ranges);

        sorted.should.have.lengthOf(4);
        sorted[0].type.should.equal('italic');
        sorted[1].type.should.equal('link');
        sorted[2].type.should.equal('bold');
        sorted[3].type.should.equal('strike');
    });

});
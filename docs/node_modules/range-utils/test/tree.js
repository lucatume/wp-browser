require('should');
var Range = require('../lib');

describe('Range.toTree', function() {
    var ranges = [
        Range(0, 5, { type: 'bold' }),
        Range(5, 10, { type: 'italic' }),
        Range(0, 10, { type: 'link' }),
        Range(15, 5, { type: 'strike' })
    ];

    var tree;

    before(function() {
        tree = Range.toTree(ranges, function(range) {
            return range.type == 'link';
        });
    })

    it('should output a tree with 3 nodes', function() {
        tree.should.have.lengthOf(3);
    });

});
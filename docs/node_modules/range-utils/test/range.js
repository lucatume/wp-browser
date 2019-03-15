require('should');
var Range = require('../lib');

describe('Range', function() {
    describe('.contains', function() {
        it('should return false if does not contain offset', function() {
            Range.contains({
                offset: 0,
                length: 10
            }, 11).should.equal(false);

            Range.contains({
                offset: 0,
                length: 10
            }, 10).should.equal(false);

            Range.contains({
                offset: 0,
                length: 10
            }, 15).should.equal(false);

            Range.contains({
                offset: 10,
                length: 10
            }, 4).should.equal(false);

            Range.contains({
                offset: 10,
                length: 10
            }, 40).should.equal(false);
        });

        it('should return true if contain offset', function() {
            Range.contains({
                offset: 0,
                length: 10
            }, 1).should.equal(true);

            Range.contains({
                offset: 0,
                length: 10
            }, 0).should.equal(true);
        });
    });

    describe('.areCollapsing', function() {
        it('should return false if not collapsing', function() {
            Range.areCollapsing({
                offset: 0,
                length: 10
            }, {
                offset: 10,
                length: 10
            }).should.equal(false);
        });

        it('should return true if collapsing', function() {
            Range.areCollapsing({
                offset: 0,
                length: 13
            }, {
                offset: 10,
                length: 10
            }).should.equal(true);
        });
    });

    describe('.fill', function() {
        it('should fill empty spaces (mid)', function() {
            var ranges = [
                {
                    offset: 0,
                    length: 2,
                    type: 'BOLD'
                },
                {
                    offset: 3,
                    length: 2,
                    type: 'ITALIC'
                }
            ];

            Range.fill('ab cd', ranges, {
                type: 'unstyled'
            }).should.deepEqual([
                { offset: 0, length: 2, type: 'BOLD' },
                { type: 'unstyled', offset: 2, length: 1 },
                { offset: 3, length: 2, type: 'ITALIC' }
            ]);
        });

        it('should fill empty spaces (begining)', function() {
            var ranges = [
                {
                    offset: 2,
                    length: 2,
                    type: 'BOLD'
                },
                {
                    offset: 4,
                    length: 2,
                    type: 'ITALIC'
                }
            ];

            Range.fill('ab cd', ranges, {
                type: 'unstyled'
            }).should.deepEqual([
                { type: 'unstyled', offset: 0, length: 2 },
                { offset: 2, length: 2, type: 'BOLD' },
                { offset: 4, length: 2, type: 'ITALIC' }
            ]);
        });
    });

    describe('.reduceText', function() {
        function transform(text, range) {
            if (range.type == 'IMAGE') return '{' + text + '}';
            if (range.type == 'LINK') return '[' + text + ']';
            if (range.type == 'BOLD') return '*' + text + '*';
            if (range.type == 'ITALIC') return '_' + text + '_';
            return text;
        }


        it('should replace correctly', function() {
            var ranges = [
                {
                    offset: 0,
                    length: 2,
                    type: 'BOLD'
                },
                {
                    offset: 2,
                    length: 2,
                    type: 'ITALIC'
                }
            ];
            Range.reduceText('Hello', [ranges], transform).should.equal('*He*_ll_o');
        });

        it('should handle multiple styles', function() {
            var ranges = [
                {
                    offset: 0,
                    length: 2,
                    type: 'BOLD'
                },
                {
                    offset: 0,
                    length: 2,
                    type: 'ITALIC'
                }
            ];
            Range.reduceText('Hello', [ranges], transform).should.equal('_*He*_llo');
        });

        it('should handle overlapsing styles', function() {
            var ranges = [
                {
                    offset: 0,
                    length: 3,
                    type: 'BOLD'
                },
                {
                    offset: 0,
                    length: 4,
                    type: 'ITALIC'
                }
            ];
            Range.reduceText('Hello', [ranges], transform).should.equal('_*Hel*__l_o');
        });

        it('should handle styles + entities', function() {
            var styles = [
                {
                    offset: 0,
                    length: 3,
                    type: 'BOLD'
                },
                {
                    offset: 0,
                    length: 4,
                    type: 'ITALIC'
                }
            ];
            var entities = [
                {
                    offset: 0,
                    length: 5,
                    type: 'LINK'
                }
            ];
            Range.reduceText('Hello', [styles, entities], transform).should.equal('[_*Hel*__l_o]');
        });

        it('should handle overlapsing styles + entities', function() {
            var styles = [
                {
                    offset: 0,
                    length: 3,
                    type: 'BOLD'
                },
                {
                    offset: 0,
                    length: 4,
                    type: 'ITALIC'
                }
            ];
            var entities = [
                {
                    offset: 0,
                    length: 5,
                    type: 'LINK'
                },
                {
                    offset: 0,
                    length: 5,
                    type: 'IMAGE'
                }
            ];
            Range.reduceText('Hello', [styles, entities], transform).should.equal('{[_*Hel*__l_o]}');
        });
    });
});

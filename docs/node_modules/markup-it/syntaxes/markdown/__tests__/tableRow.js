var rowToCells = require('../tableRow').rowToCells;

describe('Markdown table rowToCells', function() {
    it('should parse common cells correctly', function() {
        // Test cases
        var TESTS = [
            // Simple column tests
            {
                text: '| A |',
                expected: [' A ']
            },
            {
                text: '| A | B |',
                expected: [' A ', ' B ']
            },
            // Cells with only whitespace (ensure whitespace is preserved)
            {
                text: '| | |',
                expected: [' ', ' ']
            },
            {
                text: '|  |  |',
                expected: ['  ', '  ']
            },

            {
                text: '| | |',
                expected: [' ', ' ']
            },
            // No whitespace around text
            {
                text: '|ABC|DEF|',
                expected: ['ABC', 'DEF']
            },
            {
                text: 'ABC|DEF',
                expected: ['ABC', 'DEF']
            },
            // Without trailing pipes |
            {
                text: 'A | B',
                expected: ['A ', ' B']
            },
            {
                text: 'A|B',
                expected: ['A', 'B']
            },
            // Empty and whitespace strings
            {
                text: '',
                expected: []
            },
            {
                text: ' ',
                expected: []
            },
            // No pipe but not just whitespace
            {
                text: 'abc',
                expected: ['abc']
            },
            // Rows with escaped pipes
            {
                text: '| P(a\\|b) | 0.5 |',
                expected: [' P(a\\|b) ', ' 0.5 ']
            },
            {
                text: '| \\|\\|\\| | abc |',
                expected: [' \\|\\|\\| ', ' abc ']
            }
        ];

        // Run the tests
        TESTS.forEach(function(test) {
            var input = test.text;
            var output = rowToCells(input);
            var expected = test.expected;
            (output).should.be.deepEqual(expected);
        });
    });
});

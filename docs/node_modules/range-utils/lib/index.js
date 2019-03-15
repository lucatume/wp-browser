var extend = require('extend');
var is = require('is');

function Range(offset, length, props) {
    var range = {};

    extend(range, props);
    range.offset = offset || 0;
    range.length = length || 0;

    return range;
}

/*
    Check if an object is a range

    @param {Object} range
    @return {Boolean}
*/
Range.is = function isRange(range) {
    return (range && is.number(range.offset) && is.number(range.length));
}

/*
    Return end offset of the range

    @param {Range} range
    @return {Number}
*/
Range.end = function end(range) {
    return (range.offset + range.length);
};

/*
    Extend property of a range

    @param {Range} range
    @param {Object} props
    @return {Range}
*/
Range.extend = function extendRange(range, props) {
    return Range(
        range.offset,
        range.length,
        extend(range, props)
    );
};

/*
    Return true if an offset/range is in the range

    @param {Range} base
    @paran {Range|Number} offset
*/
Range.contains = function contains(base, offset) {
    if (Range.is(offset)) {
        return (
            Range.contains(base, offset.offset) &&
            Range.contains(base, Range.end(offset))
        );
    }

    return (offset >= base.offset && offset < (base.offset + base.length));
};

// Return true if range starts in b
Range.startsIn = function startsIn(a, b) {
    return (a.offset >= b.offset && a.offset < (b.offset + b.length));
};

// Return true if range is before a
Range.isBefore = function isBefore(a, b) {
    return (a.offset < b.offset);
};

// Return true if range is after a
Range.isAfter = function isAfter(a, b) {
    return (a.offset >= Range.end(b));
};

// Return true if both ranges have the same position
Range.areEquals = function areEquals(a, b) {
    return (a.offset === b.offset && a.length === b.length);
};

// Return true if range is collapsing with another range
Range.areCollapsing = function areCollapsing(a, b) {
    return ((Range.startsIn(a, b) || Range.startsIn(b, a)) && !Range.areEquals(a, b));
};

// Move this range to a new position, returns a new range
Range.move = function move(range, offset, length) {
    return Range(offset, length, range);
};

// Move a range from a specified index
Range.moveBy = function moveBy(range, index) {
    return Range(range.offset + index, range.length, range);
};

// Enlarge a range
Range.enlarge = function enlarge(range, index) {
    return Range(range.offset, range.length + index, range);
};

// Considering a list of applied ranges with special prop "value" (text after application)
// (offset,length are still relative to the current string)
// It moves a range to match the resulting text
Range.relativeTo = function relativeTo(start, ranges) {
    return ranges.reduce(function(current, range, i) {
        var change = range.value.length - range.length;

        // Enlarge if the current range contains the other one
        if (Range.contains(current, range)) {
            return Range.enlarge(current, change);
        }

        // Change if before the other modification, range is not affected
        if (Range.isBefore(current, range)) {
            return current;
        }

        // Change is after the last modification, move it by the difference in length
        if (Range.isAfter(current, range)) {
            return Range.moveBy(current, change);
        }

        if (current.offset == range.offset) {
            return Range.enlarge(current, change);
        }

        return current;
    }, start);
};

/*
    Collapse two ranges and return a list of ranges

    @param {Range} a
    @param {Range} b
    @return {Array<Range>}
*/
Range.collapse = function collapse(a, b) {
    var intersectionOffset = a.offset + (b.offset - a.offset);
    var intersectionLength = (a.offset + a.length - b.offset);

    return [
        Range.move(a, a.offset, b.offset - a.offset),
        Range.move(a, intersectionOffset, intersectionLength),
        Range.move(b, intersectionOffset, intersectionLength),
        Range.move(b, intersectionOffset + intersectionLength, b.offset + b. length - (intersectionOffset + intersectionLength))
    ];
};

/*
    Linearize ranges to avoid collapsing ones

    @param {Array<Range>} ranges
    @return {Array<Range>}
*/
Range.linearize = function linearize(ranges) {
    var result = [], range, last, collapsed;

    // Sort according to offset
    ranges = Range.sort(ranges);

    for (var i = 0; i < ranges.length; i++) {
        range = ranges[i];
        last = result[result.length - 1];

        if (last && Range.areCollapsing(last, range)) {
            collapsed = Range.collapse(last, range);

            // Remove last one
            result.pop();

            // Push new ranges
            result = result.concat(collapsed);

        } else {
            result.push(range);
        }
    }

    return Range.compact(result);
};

/*
    Merge ranges collpasing using a transformation function

    @param {Array<Range>} ranges
    @param {Function} fn
    @return {Array<Range>}
*/
Range.merge = function merge(ranges, fn) {
    var result = [], range, last;

    // Linearize ranges
    ranges = Range.linearize(ranges);

    for (var i = 0; i < ranges.length; i++) {
        range = ranges[i];
        last = result[result.length - 1];

        if (last && Range.areEquals(range, last)) {
            // Remove last one
            result.pop();

            // Push new ranges
            result.push(fn(range, last));
        } else {
            result.push(range);
        }
    }

    return Range.compact(result);
};

/*
    Sort a list of ranges (using offset position)

    @param {Array<Range>} ranges
    @return {Array<Range>}
*/
Range.sort = function sort(ranges) {
    return [].concat(ranges)
    .sort(function(a, b) {
        if (a.offset < b.offset) {
            return -1;
        }
        if (a.offset > b.offset) {
            return 1;
        }
        return 0;
    });
};

/*
    Sort a list of ranges by size, return largest ranges first.

    @param {Array<Range>} ranges
    @return {Array<Range>}
*/
Range.sortByLength = function sortByLength(ranges) {
    return [].concat(ranges).sort(function(a, b) {
        if (a.length > b.length) {
            return -1;
        }
        if (a.length < b.length) {
            return 1;
        }
        return 0;
    });
};

/*
    Fill empty spaces in a text with new ranges
    Ranges should be linearized

    @param {String} text
    @param {Array<Range>} ranges
    @paran {Object} props
    @return {Array<Range>}
*/
Range.fill = function(text, ranges, props) {
    var rangeStart = 0;
    var rangeLength = 0;
    var result = [];
    var range;

    function pushFilledRange() {
        if (!rangeLength) return;

        result.push(Range(rangeStart, rangeLength, props));
    }

    for (var i = 0; i < text.length; i++) {
        range = Range.findByOffset(ranges, i);

        if (range) {
            pushFilledRange();

            rangeStart = i + 1;
            rangeLength = 0;
        } else {
            rangeLength++;
        }
    }

    pushFilledRange();

    return Range.sort(result.concat(ranges));
};

/*
    Find a range containing an offset

    @param {Array<Range>} ranges
    @paran {Number} offset
    @return {Range}
*/
Range.findByOffset = function findByOffset(ranges, offset) {
    var result;

    for (var i = 0;i < ranges.length; i++) {
        if (Range.contains(ranges[i], offset)) {
            result = ranges[i];
            break;
        }
    }

    return result;
};

/*
    Move an array of ranges

    @param {Array<Range>} ranges
    @paran {Number} index
    @return {Array<Range>}
*/
Range.moveRangesBy = function moveRangesBy(ranges, index) {
    return ranges.map(function(range) {
        return Range.moveBy(range, index);
    });
};

/*
    Remove empty ranges

    @param {Array<Range>} ranges
    @return {Array<Range>}
*/
Range.compact = function compact(ranges) {
    var result = [];

    ranges.map(function(range) {
        if (range.length > 0) result.push(range);
    });

    return result;
};

/*
    Apply a list of ranges/tranformations on the same text

    @param {String} originalText: text to apply ranges on
    @param {Array<Array<Range>>} groups
    @param {Function(text, range)}

    @return {String}
*/
Range.reduceText = function reduceText(originalText, groups, fn) {
    if (Range.is(groups[0])) groups = [groups];

    var appliedRanges = [];

    return groups.reduce(function(groupText, ranges) {
        // Linearize with entities
        ranges = Range.linearize(ranges);

        // Sort by size, we'll apply the shortest first
        ranges = Range.sortByLength(ranges);

        return ranges.reduce(function(text, currentRange) {
            var range = Range.relativeTo(currentRange, appliedRanges);

            // Extract text from range
            var originalText = text.slice(
                range.offset,
                range.offset + range.length
            );

            // Calcul new text
            var resultText = fn(originalText, range);

            // Push this range as being applied
            appliedRanges.push(
                Range(range.offset, range.length, {
                    value: resultText
                })
            );

            // Replace text
            return text.slice(0, range.offset) + resultText + text.slice(range.offset + range.length);
        }, groupText);
    }, originalText);
};

/*
    Filter ranges using an iter

    @param {Array<Range>} ranges
    @param {Function} iter
    @return {Array<Range>}
*/
Range.filter = function(ranges, iter) {
    var result = [];

    ranges.forEach(function(range) {
        if (!iter(range)) return;

        result.push(range);
    });

    return result;
};

/*
    Group ranges using an iter

    @param {Array<Range>} ranges
    @param {Function} iter
    @return {Map<String:Array<Range>>}
*/
Range.groupBy = function(ranges, iter) {
    var groups = {};

    ranges.forEach(function(range) {
        var group = iter(range);
        groups[group] = (groups[group] || []);

        groups[group].push(range);
    });

    return groups;
};

/*
    Linearize an array of ranges and keeps some fixed

    @param {Array<Range>} ranges
    @return {Array<Range>}
*/
Range.linearizeWithFixed = function(ranges, isFixedIter) {
    // Extract fixed ranges, that should not be linearized
    var fixedRanges = Range.filter(ranges, isFixedIter);

    ranges = Range.linearize(ranges);

    // Remove fixed ranges that were split
    ranges = Range.filter(ranges, function(range) {
        return !isFixedIter(range);
    });

    return ranges.concat(fixedRanges);
}


/*
    Transform an array of ranges into a tree.
    "isFixedIter" can be specify to determine which range should not be split.

    @param {Array<Range>} ranges
    @param {Function} isFixedIter
    @return {Tree<Range>}
*/
Range.toTree = function(ranges, isFixedIter) {
    if (ranges.length == 0) {
        return [];
    }

    // Linearize all ranges
    ranges = isFixedIter? Range.linearizeWithFixed(ranges, isFixedIter) : Range.linearize(ranges);
    ranges = Range.sortByLength(ranges);

    // Take the largest range
    var base = ranges[0];
    ranges = ranges.slice(1);

    // Extract ranges contained in this ranges and the ones not contained
    var groups = Range.groupBy(ranges, function(range) {
        if (Range.contains(base, range.offset)) {
            return 'contained';
        } else {
            return 'outside';
        }
    });

    var contained = groups.contained || [];
    var outside = groups.outside || [];

    // Move ranges contained to be relative to the base
    contained = Range.moveRangesBy(contained, -base.offset);

    // Build tree for contained ranges
    var innerTree = Range.toTree(contained, isFixedIter);

    return [
        Range.extend(base, {
            children: innerTree
        })
    ].concat(Range.toTree(outside, isFixedIter));
};

module.exports = Range;

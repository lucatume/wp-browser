var Immutable = require('immutable');
var MarkupIt = require('../../');

var reTable = require('./re/table');
var tableRow = require('./tableRow');

var ALIGN = {
    LEFT:   'left',
    RIGHT:  'right',
    CENTER: 'center'
};

/**
 * Create a table entity from parsed header/rows
 *
 * @param {ParsingState} state
 * @param {String} headerStr
 * @param {String} alignStr
 * @param {Array<String>} rowStrs
 * @rteturn {Object} tokenMatch
 */
function Table(state, headerStr, alignStr, rowStrs) {
    // Header
    var headerRow = tableRow.parse(state, headerStr);

    // Rows
    var rowTokens = rowStrs.map(function(rowStr) {
        return tableRow.parse(state, rowStr);
    });

    // Align for columns
    var alignCells = tableRow.rowToCells(alignStr);
    var align = mapAlign(alignCells);

    return {
        data: {
            align: align
        },
        tokens: Immutable.List([headerRow]).concat(rowTokens)
    };
}

/**
 * Detect alignement per column
 *
 * @param {Array<String>}
 * @return {Array<String|null>}
 */
function mapAlign(align) {
    return align.map(function(s) {
        if (reTable.alignRight.test(s)) {
            return ALIGN.RIGHT;
        } else if (reTable.alignCenter.test(s)) {
            return ALIGN.CENTER;
        } else if (reTable.alignLeft.test(s)) {
            return ALIGN.LEFT;
        } else {
            return null;
        }
    });
}

/**
 * Render align to text
 *
 * @param {Array<String>} row
 * @return {String}
 */
function alignToText(row) {
    return '|' + row.map(function(align) {
        if (align == 'right') {
            return ' ---: |';
        } else if (align == 'center') {
            return ' :---: |';
        } else if (align == 'left') {
            return ' :--- |';
        } else {
            return ' --- |';
        }
    }).join('');
}

var blockRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE)

    // Table no leading pipe (gfm)
    .regExp(reTable.nptable, function(state, match) {
        // Get all non empty lines
        var lines = match[0].split('\n').filter(Boolean);
        var header = lines[0];
        var align = lines[1];
        var rows = lines.slice(2);

        return Table(state, header, align, rows);
    })

    // Normal table
    .regExp(reTable.normal, function(state, match) {
        // Get all non empty lines
        var lines = match[0].split('\n').filter(Boolean);
        var header = lines[0];
        var align = lines[1];
        var rows = lines.slice(2);

        return Table(state, header, align, rows);
    })

    .toText(function(state, token) {
        var data = token.getData();
        var rows = token.getTokens();
        var align = data.get('align');

        var headerRows = rows.slice(0, 1);
        var bodyRows   = rows.slice(1);
        var headerRow  = headerRows.get(0);
        var countCells = headerRow.getTokens().size;

        align = align || [];
        align = Array
            .apply(null, Array(countCells))
            .map(function(v, i){
                return align[i];
            });

        var headerText = state.render(headerRows);
        var bodyText   = state.render(bodyRows);

        return (headerText
            + alignToText(align) + '\n'
            + bodyText + '\n');

    });

var rowRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE_ROW)
    .toText(function(state, token) {
        var innerContent = state.render(token);
        return '|' + innerContent + '\n';
    });

var cellRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE_CELL)
    .toText(function(state, token) {
        var innerContent = state.render(token);
        return ' ' + innerContent.trim() + ' |';
    });

module.exports = {
    block:  blockRule,
    cell:   cellRule,
    row:    rowRule
};

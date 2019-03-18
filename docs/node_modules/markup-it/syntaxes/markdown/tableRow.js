var MarkupIt = require('../../');

var reTable = require('./re/table');
var inlineRules = require('./inline');
var utils = require('./utils');

var CELL_SEPARATOR = 'cell';

/*
 * Custom inline syntax to parse each row with custom cell separator tokens
 */
var rowRules = inlineRules
    .unshift(
        MarkupIt.Rule(CELL_SEPARATOR)
            .regExp(reTable.cellSeparation, function(match) {
                return {
                    raw: match[0]
                };
            })
    )
    .replace(
        MarkupIt.Rule(MarkupIt.STYLES.TEXT)
            .regExp(reTable.cellInlineEscape, function(state, match) {
                var text = utils.unescape(match[0]);
                return { text: text };
            })
            .regExp(reTable.cellInlineText, function(state, match) {
                var text = utils.unescape(match[0]);
                return { text: text };
            })
            .toText(utils.escape)
    );

/**
 * Parse a row from a table
 *
 * @param {ParsingState} state
 * @param {String} text
 * @return {Token}
 */
function parseRow(state, text) {
    // Split into cells
    var cells = rowToCells(text);
    // Tokenize each cell
    var tokenizedCells = cells.map(function(cellStr) {
        var tokens = state.parse(rowRules, true, cellStr.trim());
        return MarkupIt.Token.create(MarkupIt.BLOCKS.TABLE_CELL, {
            tokens: tokens
        });
    });

    return MarkupIt.Token.create(MarkupIt.BLOCKS.TABLE_ROW, {
        tokens: tokenizedCells
    });
}

/**
 * Split a row up into its individual cells
 *
 * @param {String} rowStr
 * @return {Array<String>}
 */
function rowToCells(rowStr) {
    var cells = [];
    var trimmed = rowStr.trim();

    var lastSep = 0;
    for(var i = 0; i < trimmed.length; i++) {
        var prevIdx = i === 0 ? 0 : i-1;
        var isSep = trimmed[i] === '|';
        var isNotEscaped = (trimmed[prevIdx] !== '\\');

        if(isSep && isNotEscaped) {
            // New cell
            if(i > 0 && i < trimmed.length) {
                cells.push(trimmed.slice(lastSep, i));
            }
            lastSep = i+1;
        }
    }
    // Last cell
    if(lastSep < trimmed.length) {
        cells.push(trimmed.slice(lastSep));
    }

    return cells;
}


module.exports = {
    parse: parseRow,
    rowToCells: rowToCells
};

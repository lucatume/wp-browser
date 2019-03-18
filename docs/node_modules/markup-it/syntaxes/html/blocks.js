var MarkupIt = require('../../');
var HTMLRule = require('./rule');

var tableRules = require('./table');
var listRules = require('./list');

var utils = require('./utils');

/*
 * Generate an heading rule for a specific level
 */
function headingRule(n) {
    var type = MarkupIt.BLOCKS['HEADING_' + n];
    return HTMLRule(type, 'h' + n);
}

module.exports = [
    tableRules.block,
    tableRules.row,
    tableRules.cell,

    listRules.ul,
    listRules.ol,

    headingRule(1),
    headingRule(2),
    headingRule(3),
    headingRule(4),
    headingRule(5),
    headingRule(6),

    HTMLRule(MarkupIt.BLOCKS.HR, 'hr'),
    HTMLRule(MarkupIt.BLOCKS.PARAGRAPH, 'p'),
    HTMLRule(MarkupIt.BLOCKS.BLOCKQUOTE, 'blockquote'),

    MarkupIt.Rule(MarkupIt.BLOCKS.FOOTNOTE)
        .toText(function(state, token) {
            var data    = token.getData();
            var text    = state.renderAsInline(token);
            var refname = data.get('id');

            return '<blockquote id="fn_' + refname + '">\n'
                + '<sup>' + refname + '</sup>. '
                + text
                + '<a href="#reffn_' + refname + '" title="Jump back to footnote [' + refname + '] in the text."> &#8617;</a>\n'
                + '</blockquote>\n';
        }),

    MarkupIt.Rule(MarkupIt.BLOCKS.HTML)
        .toText('%s\n\n'),

    MarkupIt.Rule(MarkupIt.BLOCKS.CODE)
        .toText(function(state, token) {
            var attr   = '';
            var data   = token.getData();
            var text   = token.getAsPlainText();
            var syntax = data.get('syntax');

            if (syntax) {
                attr = ' class="lang-' + syntax +'"';
            }

            return '<pre><code' + attr + '>' + utils.escape(text) + '</code></pre>\n';
        })
];

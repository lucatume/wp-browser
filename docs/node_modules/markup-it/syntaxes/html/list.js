var MarkupIt = require('../../');

/**
 * Render a list item
 *
 * @param {String} text
 * @param {Token} token
 * @return {String}
 */
function renderListItem(state, token) {
    var isOrdered = (token.type == MarkupIt.BLOCKS.OL_LIST);
    var listTag   = isOrdered? 'ol' : 'ul';
    var items     = token.getTokens();

    return '<' + listTag + '>' +
        items.map(function(item) {
            return '<li>' + state.render(item) + '</li>';
        }).join('\n')
    + '</' + listTag + '>\n';
}

var ruleOL = MarkupIt.Rule(MarkupIt.BLOCKS.OL_LIST)
    .toText(renderListItem);

var ruleUL = MarkupIt.Rule(MarkupIt.BLOCKS.UL_LIST)
    .toText(renderListItem);

module.exports = {
    ol: ruleOL,
    ul: ruleUL
};

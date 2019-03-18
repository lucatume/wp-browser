var MarkupIt = require('../../');

var blockRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE)
    .toText(function(state, token) {
        state._rowIndex = 0;

        var data = token.getData();
        var rows = token.getTokens();
        var align = data.get('align');

        var headerRows = rows.slice(0, 1);
        var bodyRows   = rows.slice(1);

        state._tableAlign = align;

        var headerText = state.render(headerRows);
        var bodyText   = state.render(bodyRows);

        return '<table>\n'
            + '<thead>\n' + headerText + '</thead>'
            + '<tbody>\n' + bodyText + '</tbody>'
        + '</table>\n\n';
    });

var rowRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE_ROW)
    .toText(function(state, token) {
        var innerContent = state.render(token);
        state._rowIndex  = state._rowIndex + 1;
        state._cellIndex = 0;

        return '<tr>' + innerContent + '</tr>';
    });

var cellRule = MarkupIt.Rule(MarkupIt.BLOCKS.TABLE_CELL)
    .toText(function(state, token) {
        var align     = state._tableAlign[state._cellIndex];
        var isHeader  = (state._rowIndex || 0) === 0;
        var innerHTML = state.render(token);

        var type = isHeader ? 'th' : 'td';
        var tag = (align)
        ? '<' + type + ' style="text-align:' + align + '">'
        : '<' + type + '>';

        state._cellIndex = state._cellIndex + 1;

        return tag + innerHTML + '</' + type + '>\n';
    });

module.exports = {
    block:      blockRule,
    row:        rowRule,
    cell:       cellRule
};

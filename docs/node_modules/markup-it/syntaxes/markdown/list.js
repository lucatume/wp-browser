var reBlock = require('./re/block');
var MarkupIt = require('../../');
var utils = require('./utils');

var reList = reBlock.list;

// Rule for lists, rBlock.list match the whole (multilines) list, we stop at the first item
function listRule(type) {
    return MarkupIt.Rule(type)
        .regExp(reList.block, function(state, match) {
            var rawList = match[0];
            var bull = match[2];
            var ordered = bull.length > 1;

            if (ordered && type === MarkupIt.BLOCKS.UL_LIST) return;
            if (!ordered && type === MarkupIt.BLOCKS.OL_LIST) return;

            var item, loose, next = false;

            var lastIndex = 0;
            var result = [];
            var rawItem, textItem, space, items = [];

            // Extract all items
            reList.item.lastIndex = 0;
            while ((item = reList.item.exec(rawList)) !== null) {
                rawItem = rawList.slice(lastIndex, reList.item.lastIndex);
                lastIndex = reList.item.lastIndex;

                items.push([item, rawItem]);
            }

            for (var i = 0; i < items.length; i++) {
                item = items[i][0];
                rawItem = items[i][1];

                // Remove the list item's bullet
                // so it is seen as the next token.
                textItem = item[0];
                space = textItem.length;
                textItem = textItem.replace(/^ *([*+-]|\d+\.) +/, '');

                // Outdent whatever the
                // list item contains. Hacky.
                if (~textItem.indexOf('\n ')) {
                    space -= textItem.length;
                    textItem =  textItem.replace(new RegExp('^ {1,' + space + '}', 'gm'), '');
                }

                // Determine whether item is loose or not.
                // Use: /(^|\n)(?! )[^\n]+\n\n(?!\s*$)/
                // for discount behavior.
                loose = next || /\n\n(?!\s*$)/.test(textItem);
                if (i !== items.length - 1) {
                    next = textItem.charAt(textItem.length - 1) === '\n';
                    if (!loose) loose = next;
                }

                var parse = function() {
                    return MarkupIt.Token.create(MarkupIt.BLOCKS.LIST_ITEM, {
                        raw:  rawItem,
                        tokens: state.parseAsBlock(textItem),
                        data: {
                            loose: loose
                        }
                    });
                };


                result.push(
                    loose? state.toggle('looseList', parse) : parse()
                );
            }

            return {
                tokens: result
            };
        })
        .toText(function(state, token) {
            var listType = token.getType();
            var items    = token.getTokens();

            return items.reduce(function(text, item, i) {
                // Determine which bullet to use
                var bullet = '* ';
                if (listType == MarkupIt.BLOCKS.OL_LIST) {
                    bullet = (i + 1) + '. ';
                }

                // Prepend text with spacing
                var innerText = state.renderAsBlock(item);
                var rows = utils.splitLines(innerText);
                var head = rows[0];
                var indent = utils.spaces(bullet.length);
                var rest = utils.indent(rows.slice(1).join('\n'), indent);
                var eol = rest? '' : '\n';
                var isLoose = item.getTokens()
                    .find(function(p) {
                        return p.getType() === MarkupIt.BLOCKS.PARAGRAPH;
                    }) !== undefined;
                //if (isLoose) eol += '\n';

                var itemText = bullet + head + (rest ? '\n' + rest : '') + eol;
                return text + itemText;
            }, '') + '\n';
        });
}

module.exports = {
    ul: listRule(MarkupIt.BLOCKS.UL_LIST),
    ol: listRule(MarkupIt.BLOCKS.OL_LIST)
};

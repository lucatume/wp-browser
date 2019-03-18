var reBlock = require('./re/block');
var MarkupIt = require('../../');

var heading = require('./heading');
var list = require('./list');
var code = require('./code');
var table = require('./table');
var utils = require('./utils');

module.exports = MarkupIt.RulesSet([
    // ---- CODE BLOCKS ----
    code.block,

    // ---- FOOTNOTES ----
    MarkupIt.Rule(MarkupIt.BLOCKS.FOOTNOTE)
        .regExp(reBlock.footnote, function(state, match) {
            var text = match[2];

            return {
                tokens: state.parseAsInline(text),
                data: {
                    id: match[1]
                }
            };
        })
        .toText(function(state, token) {
            var data         = token.getData();
            var id           = data.get('id');
            var innerContent = state.renderAsInline(token);

            return '[^' + id + ']: ' + innerContent + '\n\n';
        }),

    // ---- HEADING ----
    heading(6),
    heading(5),
    heading(4),
    heading(3),
    heading(2),
    heading(1),

    // ---- TABLE ----
    table.block,
    table.row,
    table.cell,

    // ---- HR ----
    MarkupIt.Rule(MarkupIt.BLOCKS.HR)
        .regExp(reBlock.hr, function() {
            return {};
        })
        .toText('---\n\n'),

    // ---- BLOCKQUOTE ----
    MarkupIt.Rule(MarkupIt.BLOCKS.BLOCKQUOTE)
        .regExp(reBlock.blockquote, function(state, match) {
            var inner = match[0].replace(/^ *> ?/gm, '').trim();

            return state.toggle('blockquote', function() {
                return {
                    tokens: state.parseAsBlock(inner)
                };
            });
        })

        .toText(function(state, token) {
            var innerContent = state.renderAsBlock(token);
            var lines = utils.splitLines(innerContent.trim());

            return lines
                .map(function(line) {
                    return '> ' + line;
                })
                .join('\n') + '\n\n';
        }),

    // ---- LISTS ----
    list.ul,
    list.ol,

    // ---- HTML ----
    MarkupIt.Rule(MarkupIt.BLOCKS.HTML)
        .regExp(reBlock.html, function(state, match) {
            return {
                text: match[0]
            };
        })
        .toText('%s'),

    // ---- DEFINITION ----
    MarkupIt.Rule()
        .regExp(reBlock.def, function(state, match) {
            if (state.getDepth() > 1) {
                return;
            }

            var id    = match[1].toLowerCase();
            var href  = match[2];
            var title = match[3];

            state.refs     = state.refs || {};
            state.refs[id] = {
                href: href,
                title: title
            };

            return {
                type: 'definition'
            };
        }),

    // ---- PARAGRAPH ----
    MarkupIt.Rule(MarkupIt.BLOCKS.PARAGRAPH)
        .regExp(reBlock.math, function(state, match) {
            var text = match[2];

            if (state.getOption('math') !== true || text.trim().length === 0) {
                return;
            }

            return {
                tokens: [
                    MarkupIt.Token.create(MarkupIt.ENTITIES.MATH, {
                        data: {
                            tex: text
                        }
                    })
                ]
            };
        })
        .regExp(reBlock.paragraph, function(state, match) {
            var isInBlockquote = (state.get('blockquote') === state.getParentDepth());
            var isInLooseList = (state.get('looseList') === state.getParentDepth());
            var isTop = (state.getDepth() === 1);

            if (!isTop && !isInBlockquote && !isInLooseList) {
                return;
            }
            var text = match[1].trim();

            return {
                tokens: state.parseAsInline(text)
            };
        })
        .toText('%s\n\n'),

    // ---- TEXT ----
    // Top-level should never reach here.
    MarkupIt.Rule(MarkupIt.BLOCKS.TEXT)
        .regExp(reBlock.text, function(state, match) {
            var text = match[0];

            return {
                tokens: state.parseAsInline(text)
            };
        })
        .toText('%s\n')
]);

var Rule = require('../models/rule');

var BLOCKS = require('./blocks');
var STYLES = require('./styles');

var defaultDocumentRule = Rule(BLOCKS.DOCUMENT)
    .match(function(state, text) {
        return {
            tokens: state.parseAsBlock(text)
        };
    })
    .toText(function(state, token) {
        return state.renderAsBlock(token);
    });

var defaultBlockRule = Rule(BLOCKS.TEXT)
    .match(function(state, text) {
        return {
            tokens: state.parseAsInline(text)
        };
    })
    .toText('%s\n');

var defaultInlineRule = Rule(STYLES.TEXT)
    .match(function(state, text) {
        return {
            text: text
        };
    })
    .toText('%s');

module.exports = {
    documentRule: defaultDocumentRule,
    blockRule:    defaultBlockRule,
    inlineRule:   defaultInlineRule
};

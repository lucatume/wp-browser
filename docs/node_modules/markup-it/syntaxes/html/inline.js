var MarkupIt = require('../../');
var utils = require('./utils');

var HTMLRule = require('./rule');

module.exports = [
    // ---- HARD BREAKS
    MarkupIt.Rule(MarkupIt.ENTITIES.HARD_BREAK)
        .toText(function(state, token) {
            return '<br />';
        }),

    // ---- TEXT ----
    MarkupIt.Rule(MarkupIt.STYLES.TEXT)
        .toText(function(state, token) {
            return utils.escape(token.getAsPlainText());
        }),

    // ---- CODE ----
    MarkupIt.Rule(MarkupIt.STYLES.CODE)
        .toText(function(state, token) {
            return '<code>' + utils.escape(token.getAsPlainText()) + '</code>';
        }),

    // ---- BOLD ----
    HTMLRule(MarkupIt.STYLES.BOLD, 'strong'),

    // ---- ITALIC ----
    HTMLRule(MarkupIt.STYLES.ITALIC, 'em'),

    // ---- STRIKETHROUGH ----
    HTMLRule(MarkupIt.STYLES.STRIKETHROUGH, 'del'),

    // ---- IMAGES ----
    HTMLRule(MarkupIt.ENTITIES.IMAGE, 'img'),

    // ---- LINK ----
    HTMLRule(MarkupIt.ENTITIES.LINK, 'a', function(data) {
        return {
            title: data.title? utils.escape(data.title) : undefined,
            href:  utils.escape(data.href || '')
        };
    }),

    // ---- FOOTNOTE ----
    MarkupIt.Rule(MarkupIt.ENTITIES.FOOTNOTE_REF)
        .toText(function(state, token) {
            var refname = token.getAsPlainText();
            return '<sup><a href="#fn_' + refname + '" id="reffn_' + refname + '">' + refname + '</a></sup>';
        }),

    // ---- HTML ----
    MarkupIt.Rule(MarkupIt.STYLES.HTML)
        .toText(function(state, token) {
            return token.getAsPlainText();
        })
];

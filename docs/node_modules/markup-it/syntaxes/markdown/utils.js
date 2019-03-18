var entities = require('html-entities');

var escapeStringRegexp = require('escape-string-regexp');

var htmlEntities = new entities.AllHtmlEntities();
var xmlEntities = new entities.XmlEntities();

// Replacements for Markdown escaping
var replacements = [
    [ '*', '\\*' ],
    [ '#', '\\#' ],
    [ '/', '\\/' ],
    [ '(', '\\(' ],
    [ ')', '\\)' ],
    [ '[', '\\[' ],
    [ ']', '\\]' ],
    [ '`', '\\`' ],
    [ '<', '&lt;' ],
    [ '>', '&gt;' ],
    [ '_', '\\_' ],
    [ '|', '\\|' ]
];

// Split a text into lines
function splitLines(text) {
    return text.split(/\r?\n/);
}

// Build a regexp from a string
function re(str) {
    return new RegExp(escapeStringRegexp(str), 'g');
}

/**
 * Escape markdown syntax
 * We escape only basic XML entities
 *
 * @param {String} str
 * @param {Boolean} escapeXML
 * @return {String}
 */
function escapeMarkdown(str, escapeXML) {
    str = replacements.reduce(function(text, repl) {
        return text.replace(re(repl[0]), repl[1]);
    }, str);

    return escapeXML === false? str : xmlEntities.encode(str);
}

/**
 * Unescape markdown syntax
 * We unescape all entities (HTML + XML)
 *
 * @param {String} str
 * @return {String}
 */
function unescapeMarkdown(str) {
    str = replacements.reduce(function(text, repl) {
        return text.replace(re(repl[1]), repl[0]);
    }, str);

    return htmlEntities.decode(str);
}


/**
 * Create a function to replace content in a regex
 * @param  {RegEx} regex
 * @param  {String} opt
 * @return {Function(String, String)}
 */
function replace(regex, opt) {
    regex = regex.source;
    opt = opt || '';

    return function self(name, val) {
        if (!name) return new RegExp(regex, opt);
        val = val.source || val;
        val = val.replace(/(^|[^\[])\^/g, '$1');
        regex = regex.replace(name, val);
        return self;
    };
}

/**
 * Indent a text
 * @param  {String} text
 * @param  {String} prefix
 * @return {String}
 */
function indent(text, prefix) {
    prefix = prefix || '    ';

    return splitLines(text)
        .map(function(line) {
            if (!line) {
                return line;
            }
            return prefix + line;
        })
        .join('\n');
}

/**
 * @param {Number} n
 * @return {String} n spaces string
 */
function spaces(n) {
    return Array(n+1).join(' ');
}

module.exports = {
    splitLines: splitLines,
    escape: escapeMarkdown,
    unescape: unescapeMarkdown,
    replace: replace,
    spaces: spaces,
    indent: indent
};

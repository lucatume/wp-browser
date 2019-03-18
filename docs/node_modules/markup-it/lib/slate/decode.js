var Immutable = require('immutable');

var Content = require('../models/content');
var Token = require('../models/token');
var BLOCKS = require('../constants/blocks');

/**
 * Decode a text rnge into a token tree
 * @param {JSONNode} node
 * @return {Token} token
 */
function decodeTextRange(range) {
    var base = Token.createText(range.text);

    return (range.marks || [])
        .reduce(function(inner, mark) {
            return Token.create(mark.type, {
                tokens: [inner],
                data:   mark.data
            });
        }, base);
}

/**
 * Decode a JSON text node into a token.
 * @param {JSONNode} node
 * @return {Token} token
 */
function decodeTextTokens(node) {
    var ranges = node.ranges
            ? node.ranges
            : [ { text: node.text, marks: node.marks || [] } ];

    return Immutable.List(
        ranges.map(decodeTextRange)
    );
}

/**
 * Decode multiple nodes to a set of tokens
 * @param {Array<JSONNode>} nodes
 * @return {List<Token>} tokens
 */
function decodeNodesToTokens(nodes) {
    return nodes
        .reduce(function(accu, node) {
            return accu.concat(decodeTokens(node));
        }, Immutable.List());
}

/**
 * Decode a JSON node to a token.
 * @param {JSONNode} node
 * @return {List<Token>} tokens
 */
function decodeTokens(node) {
    if (node.kind == 'text') {
        return decodeTextTokens(node);
    }

    return Immutable.List([
        Token.create(node.type, {
            data:   node.data,
            tokens: node.nodes? decodeNodesToTokens(node.nodes) : []
        })
    ]);
}

/**
 * Decode a JSON into a Content
 *
 * @param {JSONDoc} json
 * @return {Content} content
 */
function decodeContentFromSlate(json) {
    return Content.createFromToken(
        'slate',
        Token.create(BLOCKS.DOCUMENT, {
            tokens: decodeNodesToTokens(json.nodes)
        })
    );
}

module.exports = decodeContentFromSlate;

var Immutable = require('immutable');

var Content = require('../models/content');
var Token = require('../models/token');

/**
 * Decode a token
 *
 * @paran {Object} json
 * @return {Token}
 */
function decodeTokenFromJSON(json) {
    return new Token({
        type: json.type,
        text: json.text,
        raw: json.raw,
        data: json.data,
        tokens: decodeTokensFromJSON(json.tokens || [])
    });
}

/**
 * Decode a list of tokens
 *
 * @paran {Object} json
 * @return {List<Token>}
 */
function decodeTokensFromJSON(json) {
    return new Immutable.List(json.map(decodeTokenFromJSON));
}

/**
 * Decode a JSON into a Content
 *
 * @paran {Object} json
 * @return {Content}
 */
function decodeContentFromJSON(json) {
    return Content.createFromToken(
        json.syntax,
        decodeTokenFromJSON(json.token)
    );
}

module.exports = decodeContentFromJSON;

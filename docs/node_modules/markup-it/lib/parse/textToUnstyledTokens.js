var Immutable = require('immutable');

var defaultRules = require('../constants/defaultRules');
var matchRule = require('./matchRule');

/**
 * Create a text token inline or block
 *
 * @param {ParsingState} state
 * @param {Boolean} isInline
 * @param {String} text
 * @return {Token}
 */
function createTextToken(state, isInline, text) {
    var rule = isInline? defaultRules.inlineRule : defaultRules.blockRule;
    return matchRule(state, rule, text).get(0);
}

/**
 * Convert a normal text into a list of unstyled tokens (block or inline)
 *
 * @param {ParsingState} state
 * @param {Boolean} isInline
 * @param {String} text
 * @return {List<Token>}
 */
function textToUnstyledTokens(state, isInline, text) {
    if (!text) {
        return Immutable.List();
    }

    var accu = '', c, wasNewLine = false;
    var result = [];

    function pushAccu() {
        var isEmpty = !(accu.trim())
        var token = createTextToken(state, isInline, accu);
        accu = '';

        if (!isEmpty) {
            result.push(token);
        }
    }

    for (var i = 0; i < text.length; i++) {
        c = text[i];

        if (c !== '\n' && wasNewLine) {
            pushAccu();
        }

        accu += c;
        wasNewLine = (c === '\n');
    }

    pushAccu();

    return new Immutable.List(result);
}

module.exports = textToUnstyledTokens;

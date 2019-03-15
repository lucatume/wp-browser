var is = require('is');
var Immutable = require('immutable');

var Token = require('../models/token');

/**
 * Match a text using a rule
 * @param {ParsingState} state
 * @param {Rule} rule
 * @param {String} text
 * @return {List<Token>|null}
 */
function matchRule(state, rule, text) {
    var matches  = rule.onText(state, text);
    var ruleType = rule.getType();

    if (!matches) {
        return;
    }
    if (!is.array(matches) && !Immutable.List.isList(matches)) {
        matches = [matches];
    }

    return Immutable.List(matches)
        .map(function(match) {
            return Token.create(match.type || ruleType, match);
        });
}

module.exports = matchRule;

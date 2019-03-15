var Token         = require('../models/token');
var RenderOptions = require('./options');

function RenderingState(syntax, options) {
    if (!(this instanceof RenderingState)) {
        return new RenderingState(syntax);
    }

    this.syntax  = syntax;
    this.options = RenderOptions(options || {});
}

/**
 * Render a token using a set of rules
 * @param {RulesSet} rules
 * @param {Boolean} isInline
 * @param {Token|List<Token>} tokens
 * @return {List<Token>}
 */
RenderingState.prototype.render = function(tokens) {
    var state    = this;
    var syntax   = this.syntax;
    var annotate = this.options.getAnnotateFn();

    if (tokens instanceof Token) {
        var token = tokens;
        tokens = token.getTokens();

        if (tokens.size === 0) {
            return annotate(state, token.getAsPlainText(), token);
        }
    }

    return tokens.reduce(function(text, token) {
        var tokenType = token.getType();
        var rule = (token.isInline()? syntax.getInlineRule(tokenType)
            : syntax.getBlockRule(tokenType));

        if (!rule) {
            throw new Error('Unexpected error: no rule to render "' + tokenType + '"');
        }

        var raw = rule.onToken(state, token);
        raw = annotate(state, raw, token);

        return text + raw;
    }, '');
};

RenderingState.prototype.renderAsInline = function(tokens) {
    return this.render(tokens);
};

RenderingState.prototype.renderAsBlock = function(tokens) {
    return this.render(tokens);
};

module.exports = RenderingState;

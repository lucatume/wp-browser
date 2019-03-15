var Immutable = require('immutable');

var reInline = require('./re/inline');
var MarkupIt = require('../../');

var utils = require('./utils');
var isHTMLBlock = require('./isHTMLBlock');
var math = require('./math');

/**
 * Test if a link input is an image
 * @param {String} raw
 * @return {Boolean}
 */
function isImage(raw) {
    return raw.charAt(0) === '!';
}


var inlineRules = MarkupIt.RulesSet([
    // ---- FOOTNOTE REFS ----
    MarkupIt.Rule(MarkupIt.ENTITIES.FOOTNOTE_REF)
        .regExp(reInline.reffn, function(state, match) {
            return {
                text: match[1]
            };
        })
        .toText(function(state, token) {
            return '[^' + token.getAsPlainText() + ']';
        }),

    // ---- IMAGES ----
    MarkupIt.Rule(MarkupIt.ENTITIES.IMAGE)
        .regExp(reInline.link, function(state, match) {
            if (!isImage(match[0])) {
                return;
            }

            var imgData = Immutable.Map({
                alt:   match[1],
                src:   match[2],
                title: match[3]
            }).filter(Boolean);

            return {
                data: imgData
            };
        })
        .regExp(reInline.reflink, function(state, match) {
            if (!isImage(match[0])) {
                return;
            }

            var refId = (match[2] || match[1]);
            return {
                data: { ref: refId }
            };
        })
        .regExp(reInline.nolink, function(state, match) {
            if (!isImage(match[0])) {
                return;
            }

            var refId = (match[2] || match[1]);
            return {
                data: { ref: refId }
            };
        })
        .regExp(reInline.reffn, function(state, match) {
            if (!isImage(match[0])) {
                return;
            }

            var refId = match[1];
            return {
                data: { ref: refId }
            };
        })
        .toText(function(state, token) {
            var data  = token.getData();
            var alt   = data.get('alt', '');
            var src   = data.get('src', '');
            var title = data.get('title', '');

            if (title) {
                return '![' + alt + '](' + src + ' "' + title + '")';
            } else {
                return '![' + alt + '](' + src + ')';
            }
        }),

    // ---- LINK ----
    MarkupIt.Rule(MarkupIt.ENTITIES.LINK)
        .regExp(reInline.link, function(state, match) {
            return state.toggle('link', function() {
                return {
                    tokens: state.parseAsInline(match[1]),
                    data: {
                        href:  match[2],
                        title: match[3]
                    }
                };
            });
        })
        .regExp(reInline.autolink, function(state, match) {
            return state.toggle('link', function() {
                return {
                    tokens: state.parseAsInline(match[1]),
                    data: {
                        href: match[1]
                    }
                };
            });
        })
        .regExp(reInline.url, function(state, match, parents) {
            if (state.get('link')) {
                return;
            }
            var uri = match[1];

            return {
                data: {
                    href: uri
                },
                tokens: [
                    MarkupIt.Token.createText(uri)
                ]
            };
        })
        .regExp(reInline.reflink, function(state, match) {
            var refId     = (match[2] || match[1]);
            var innerText = match[1];

            return state.toggle('link', function() {
                return {
                    type: MarkupIt.ENTITIES.LINK,
                    data: {
                        ref: refId
                    },
                    tokens: [
                        MarkupIt.Token.createText(innerText)
                    ]
                };
            });
        })
        .regExp(reInline.nolink, function(state, match) {
            var refId = (match[2] || match[1]);

            return state.toggle('link', function() {
                return {
                    type: MarkupIt.ENTITIES.LINK,
                    tokens: state.parseAsInline(match[1]),
                    data: {
                        ref: refId
                    }
                };
            });
        })
        .regExp(reInline.reffn, function(state, match) {
            var refId = match[1];

            return state.toggle('link', function() {
                return {
                    tokens: state.parseAsInline(match[1]),
                    data: {
                        ref: refId
                    }
                };
            });
        })
        .toText(function(state, token) {
            var data         = token.getData();
            var title        = data.get('title');
            var href         = data.get('href');
            var innerContent = state.renderAsInline(token);
            title            = title? ' "' + title + '"' : '';

            return '[' + innerContent + '](' + href + title + ')';
        }),

    // ---- CODE ----
    MarkupIt.Rule(MarkupIt.STYLES.CODE)
        .regExp(reInline.code, function(state, match) {
            return {
                tokens: [
                    MarkupIt.Token.createText(match[2])
                ]
            };
        })
        .toText(function(state, token) {
            var separator = '`';
            var text = token.getAsPlainText();

            // We need to find the right separator not present in the content
            while (text.indexOf(separator) >= 0) {
                separator += '`';
            }

            return (separator + text + separator);
        }),

    // ---- BOLD ----
    MarkupIt.Rule(MarkupIt.STYLES.BOLD)
        .regExp(reInline.strong, function(state, match) {
            return {
                tokens: state.parseAsInline(match[2] || match[1])
            };
        })
        .toText('**%s**'),

    // ---- ITALIC ----
    MarkupIt.Rule(MarkupIt.STYLES.ITALIC)
        .regExp(reInline.em, function(state, match) {
            return {
                tokens: state.parseAsInline(match[2] || match[1])
            };
        })
        .toText('_%s_'),

    // ---- STRIKETHROUGH ----
    MarkupIt.Rule(MarkupIt.STYLES.STRIKETHROUGH)
        .regExp(reInline.del, function(state, match) {
            return {
                tokens: state.parseAsInline(match[1])
            };
        })
        .toText('~~%s~~'),

    // ---- HARD BREAKS
    MarkupIt.Rule(MarkupIt.ENTITIES.HARD_BREAK)
        .regExp(reInline.br, function(state, match) {
            return {};
        })
        .toText(function(state, token) {
            return '\n\n';
        }),

    // ---- HTML ----
    MarkupIt.Rule(MarkupIt.STYLES.HTML)
        .regExp(reInline.html, function(state, match) {
            var tag       = match[0];
            var tagName   = match[1];
            var innerText = match[2] || '';
            var startTag, endTag;
            var innerTokens = [];

            if (innerText) {
                startTag = tag.substring(0, tag.indexOf(innerText));
                endTag   = tag.substring(tag.indexOf(innerText) + innerText.length);
            } else {
                startTag = match[0];
                endTag   = '';
            }

            if (tagName && !isHTMLBlock(tagName) && innerText) {
                var isLink = (tagName.toLowerCase() === 'a');

                innerTokens = state.toggle(isLink? 'link' : 'html', function() {
                    return state.parseAsInline(innerText);
                });
            } else {
                innerTokens = [
                    {
                        type: MarkupIt.STYLES.HTML,
                        text: innerText,
                        raw:  innerText
                    }
                ];
            }

            var result = Immutable.List()
                .push({
                    type: MarkupIt.STYLES.HTML,
                    text: startTag,
                    raw:  startTag
                });

            result = result.concat(innerTokens);

            if (endTag) {
                result = result.push({
                    type: MarkupIt.STYLES.HTML,
                    text: endTag,
                    raw:  endTag
                });
            }

            return result;
        })
        .toText(function(state, token) {
            return token.getAsPlainText();
        }),

    // ---- MATH ----
    math,

    MarkupIt.Rule(MarkupIt.ENTITIES.TEMPLATE)
        .regExp(reInline.template, function(state, match) {
            if (state.getOption('template') !== true) {
                return;
            }

            var type = match[1];
            var text = match[2];

            if (type == '%') type = 'expr';
            else if (type == '#') type = 'comment';
            else if (type == '{') type = 'var';

            return {
                text: '',
                data: {
                    type: type
                },
                tokens: [
                    MarkupIt.Token.createText(text)
                ]
            };
        })
        .toText(function(state, token) {
            var data = token.getData();
            var text = token.getAsPlainText();
            var type = data.get('type');

            if (type == 'expr') text = '{% ' + text + ' %}';
            else if (type == 'comment') text = '{# ' + text + ' #}';
            else if (type == 'var') text = '{{ ' + text + ' }}';

            return text;
        }),

    // ---- ESCAPED ----
    MarkupIt.Rule(MarkupIt.STYLES.TEXT)
        .regExp(reInline.escape, function(state, match) {
            return {
                text: match[1]
            };
        })
        .regExp(reInline.text, function(state, match) {
            return {
                text: utils.unescape(match[0])
            };
        })
        .toText(function(state, token) {
            var text = token.getAsPlainText();
            return utils.escape(text, false);
        })
]);

module.exports = inlineRules;

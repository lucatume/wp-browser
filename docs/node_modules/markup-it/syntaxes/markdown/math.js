var ltrim = require('ltrim');
var rtrim = require('rtrim');

var reInline = require('./re/inline');
var MarkupIt = require('../../');

/**
 * Return true if a tex content is inline
 */
function isInlineTex(content) {
    return content[0] !== '\n';
}

/**
 * Normalize some TeX content
 * @param {String} content
 * @param {Boolean} isInline
 * @return {String}
 */
function normalizeTeX(content, isInline) {
    content = ltrim(content, '\n');
    content = rtrim(content, '\n');

    if (!isInline) {
        content = '\n' + content + '\n';
    }

    return content;
}

module.exports = MarkupIt.Rule(MarkupIt.ENTITIES.MATH)
    .regExp(reInline.math, function(state, match) {
        var text = match[1];

        if (state.getOption('math') !== true || text.trim().length === 0) {
            return;
        }

        return {
            data: {
                tex: text
            }
        };
    })
    .toText(function(state, token) {
        var data     = token.getData();
        var tex      = data.get('tex');
        var isInline = isInlineTex(tex);

        tex = normalizeTeX(tex, isInline);

        var output = '$$' + tex + '$$';

        if (!isInline) {
            output = '\n' + output + '\n';
        }

        return output;
    });

var objectValues = require('object-values');
var STYLES = require('../constants/styles');

var STYLE_TYPES = objectValues(STYLES);

/**
 * Return true if a token is a style
 *
 * @param {Token}
 * @return {Boolean}
 */
function isStyle(token) {
    return (STYLE_TYPES.indexOf(token.getType()) >= 0);
}

module.exports = isStyle;

var objectValues = require('object-values');
var BLOCKS = require('../constants/blocks');

var BLOCK_TYPES = objectValues(BLOCKS);

/**
 * Return true if a token is a block
 *
 * @param {Token}
 * @return {Boolean}
 */
function isBlock(token) {
    return (BLOCK_TYPES.indexOf(token.getType()) >= 0);
}

module.exports = isBlock;

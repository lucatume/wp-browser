var objectValues = require('object-values');
var ENTITIES = require('../constants/entities');

var ENTITY_TYPES = objectValues(ENTITIES);

/**
 * Return true if a token is an entity
 *
 * @param {Token}
 * @return {Boolean}
 */
function isEntity(token) {
    return (ENTITY_TYPES.indexOf(token.getType()) >= 0);
}

module.exports = isEntity;

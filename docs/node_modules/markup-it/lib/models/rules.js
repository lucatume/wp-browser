var Immutable = require('immutable');
var is = require('is');
var inherits = require('util').inherits;

var Rule = require('./rule');

var RulesSetRecord = Immutable.Record({
    rules: new Immutable.List()
});

function RulesSet(rules) {
    if (!(this instanceof RulesSet)) return new RulesSet(rules);

    rules = RulesList(rules);

    RulesSetRecord.call(this, {
        rules: rules
    });
}
inherits(RulesSet, RulesSetRecord);


// ---- GETTERS ----

RulesSet.prototype.getRules = function() {
    return this.get('rules');
};

// ---- METHODS ----

/**
 * Add a rule / or rules
 * @param {Rule|RulesSet|Array} rules
 * @return {RulesSet}
 */
RulesSet.prototype.add = function(newRules) {
    var rules = this.getRules();

    // Prepare new rules
    newRules = RulesList(newRules);

    // Concat rules
    rules = newRules.concat(rules);

    return this.set('rules', rules);
};

/**
 * Add a rule / or rules at the beginning
 * @param {Rule|RulesSet|Array} rules
 * @return {RulesSet}
 */
RulesSet.prototype.unshift = function(newRules) {
    var rules = this.getRules();

    // Prepare new rules
    newRules = RulesList(newRules);

    // Add rules
    rules = rules.unshift.apply(rules, newRules.toArray());

    return this.set('rules', rules);
};


/**
 * Remove a rule by its type
 * @param {String} ruleType
 * @return {RulesSet}
 */
RulesSet.prototype.del = function(ruleType) {
    var rules = this.getRules();

    rules = rules.filterNot(function(rule) {
        return rule.getType() == ruleType;
    });

    return this.set('rules', rules);
};

/**
 * Replace a rule type by a new rule
 * @param {Rule} rule
 * @return {RulesSet}
 */
RulesSet.prototype.replace = function(rule) {
    return this
        .del(rule.getType())
        .add(rule);
};

/**
 * Get a specific rule using its type
 * @param {String} ruleType
 * @return {Rule}
 */
RulesSet.prototype.getRule = function(ruleType) {
    var rules = this.getRules();

    return rules.find(function(rule) {
        return rule.getType() == ruleType;
    });
};

/**
 * Build a list of rules
 * @param {Rule|RulesSet|Array} rules
 * @return {List<Rule>}
 */
function RulesList(rules) {
    if (rules instanceof Rule) {
        return new Immutable.List([rules]);
    }

    if (is.array(rules)) {
        return new Immutable.List(rules);
    }

    if (rules instanceof RulesSet) {
        return rules.getRules();
    }

    return rules || new Immutable.List();
}

module.exports = RulesSet;

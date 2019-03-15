#! /usr/bin/env node
/* eslint-disable no-console */

var DraftMarkup = require('../');
var utils = require('./utils');

utils.command(function(content) {
    console.log(
        JSON.stringify(
            DraftMarkup.DraftUtils.encode(content),
            null, 4
        )
    );
});

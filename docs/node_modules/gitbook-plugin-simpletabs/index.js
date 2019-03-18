var _ = require('lodash');
var markdown = require('gitbook-markdown');

module.exports = {
    website: {
        assets: "./assets",
        js: [
          "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js",
          "tabs.js"
        ],
        css: [
          "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css",
          "tabs.css"
        ]
    },

    // Map of hooks
    hooks: {},

    // Map of new blocks
    blocks: {
        tabs: {
            blocks: ['content'],
            process: function(block) {
                var book = this.book;
                var content = "<ul class='nav nav-tabs' role='tablist'>";
                var classData = "active";
                _.map(block.kwargs, function(value, key) {
                    if (!_.startsWith(key, "__")) {
                        content += `<li role="presentation" class="${classData}"><a href="#${key}" aria-controls="${key}" role="tab" data-toggle="tab">${value}</a></li>`;
                        classData = "";
                    }
                });
                content += "</ul>";
                content +="<div class='tab-content'>";
                var activeState = 'active';
                _.map(block.blocks, function(b) {
                    var markup = markdown.page(b.body).content;
                    content += `<div role="tabpanel" class="tab-pane ${activeState}" id="${b.args[0]}">${markup}</div>`;
                    activeState = "";
                });
                content += "</div>";
                return content;
            }
        }
    },

    // Map of new filters
    filters: {}
};

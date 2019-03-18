/*
    Generate HTML for the tab in the header

    @param {Block}
    @param {Boolean}
    @return {String}
*/
function createTab(block, i, isActive) {
    return '<div class="mdtab' + (isActive? ' active' : '') + '" data-mdtab="' + i + '">' + block.kwargs.title + '</div>';
}

/*
    Generate HTML for the tab's content

    @param {Block}
    @param {Boolean}
    @return {String}
*/
async function createTabBody(book, block, i, isActive) {
    let body = await book.renderBlock('markdown', block.body);
    return '<div class="mdtab' + (isActive? ' active' : '') + '" data-mdtab="' + i + '">'
        + body
        + '</div>';
}

module.exports = {
    book: {
        assets: './assets',
        css: [
            'mdtabs.css'
        ],
        js: [
            'mdtabs.js'
        ]
    },

    blocks: {
        mdtabs: {
            blocks: ['mdtab'],
            process: async function(parentBlock) {
                var blocks = [parentBlock].concat(parentBlock.blocks);
                var tabsContent = '';
                var tabsHeader = '';
                var i = 0;
                for (const block of blocks) {
                    var isActive = (i == 0);

                    if (!block.kwargs.title) {
                        throw new Error('Tab requires a "title" property');
                    }

                    tabsHeader += createTab(block, i, isActive);
                    tabsContent += await createTabBody(this.book, block, i, isActive);
                    i++;
                };


                return '<div class="mdtabs">' +
                    '<div class="mdtabs-header">' + tabsHeader + '</div>' +
                    '<div class="mdtabs-body">' + tabsContent + '</div>' +
                '</div>';
            }
        }
    }
};

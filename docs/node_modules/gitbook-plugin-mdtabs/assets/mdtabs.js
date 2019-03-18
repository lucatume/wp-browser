require([
    'jquery'
], function($) {
    $(document).on('click.plugin.mdtabs', '.mdtabs .mdtabs-header .mdtab', function(e) {
        var $btn = $(e.target);
        var $tabs = $btn.parents('.mdtabs');
        var tabId = $btn.data('mdtab');

        $tabs.find('.mdtab').removeClass('active');
        $tabs.find('.mdtab[data-mdtab="' + tabId + '"]').addClass('active');
    });
});

require(["gitbook", "jquery"], function (gitbook, $) {
    gitbook.events.bind("page.change", function () {
        $('ul[role="tablist"] a').on('click', function(e) {
            $(this).tab('show');
        });

        $('ul[role="tablist"] a:first').tab('show');
    });
});
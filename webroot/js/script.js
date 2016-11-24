(function ($) {
    function MessagingCenter()
    {}

    /**
     * Enable folder messages links
     * @param  {object} options
     * @return {undefined}
     */
    MessagingCenter.prototype.enableLinks = function (options) {
        if (options.hasOwnProperty('tableId')) {
            $(options.tableId + ' tr').click(function () {
                window.document.location = $(this).data('url');
            });
        }
    };

    $messaging_center = new MessagingCenter();

    $messaging_center.enableLinks({
        'tableId': '#folder-table'
    });
})(jQuery);
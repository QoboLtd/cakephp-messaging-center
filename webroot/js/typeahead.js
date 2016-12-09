var messaging_center_typeahead = messaging_center_typeahead || {};

(function ($) {
    /**
     * Typeahead Logic.
     * @param {object} options configuration options
     */
    function Typeahead()
    {
        //
    }

    /**
     * Initialize method.
     * @return {void}
     */
    Typeahead.prototype.init = function (options) {
        this.min_length = options.hasOwnProperty('min_length') ? options.min_length : 1;
        this.timeout = options.hasOwnProperty('timeout') ? options.timeout : 300;
        this.api_token = options.hasOwnProperty('api_token') ? options.api_token : null;
        this.typeahead_id = '[data-type="typeahead"]';

        var that = this;

        // loop through typeahead inputs
        $(this.typeahead_id).each(function () {
            hidden_input = $('[name=' + $(this).data('name') + ']');

            // enable typeahead functionality
            that._enable(this, hidden_input);
        });

        // clear inputs on double click
        $(this.typeahead_id).dblclick(function () {
            hidden_input = $('[name=' + $(this).data('name') + ']');
            that._clearInputs(this, hidden_input);
        });
    };

    /**
     * Method used for clearing typeahead inputs.
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @return {void}
     */
    Typeahead.prototype._clearInputs = function (input, hidden_input) {
        if ($(input).is('[readonly]')) {
            $(input).prop('readonly', false);
            $(input).val('');
            $(hidden_input).val('');
        }
    };

    /**
     * Method that enables typeahead functionality on specified input
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @return {void}
     * {@link plugin: http://plugins.upbootstrap.com/bootstrap-ajax-typeahead/}
     */
    Typeahead.prototype._enable = function (input, hidden_input) {
        var that = this;
        console.log(that.api_token);
        // enable typeahead
        $(input).typeahead({
            // ajax
            ajax: {
                url: $(input).data('url'),
                timeout: that.timeout,
                triggerLength: that.min_length,
                method: 'get',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + that.api_token
                },
                preProcess: function (data) {
                    if (data.success === false) {
                        // Hide the list, there was some error
                        return false;
                    }
                    result = [];
                    $.each(data.data, function (k, v) {
                        result.push({
                            id: k,
                            name: v
                        });
                    });

                    return result;
                }
            },
            onSelect: function (data) {
                that._onSelect(input, hidden_input, data);
            },
            // No need to run matcher as ajax results are already filtered
            matcher: function (item) {
                return true;
            },
        });
    };

    /**
     * Method responsible for handling behavior on typeahead option selection.
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @param  {object} data         ajax call returned data
     * @return {void}
     */
    Typeahead.prototype._onSelect = function (input, hidden_input, data) {
        $(hidden_input).val(data.value);
        $(input).prop('readonly', true);
    };

    messaging_center_typeahead = new Typeahead();

})(jQuery);

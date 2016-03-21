var typeahead = typeahead || {};

(function($) {
    /**
     * Typeahead Logic.
     * @param {object} options configuration options
     */
    function Typeahead(options) {
        this.min_length = options.hasOwnProperty('min_length') ? options.min_length : 4;
    }

    /**
     * Initialize method.
     * @return {void}
     */
    Typeahead.prototype.init = function() {
        that = this;

        // loop through typeahead inputs
        $('[data-type="typeahead"]').each(function() {
            hidden_input = $('[name=' + $(this).data('name') + ']');

            // enable typeahead functionality
            that._enable(this, hidden_input);

            // clear inputs on double click
            $(this).dblclick(function() {
                that._clearInputs(this, hidden_input);
            });
        });
    };

    /**
     * Method used for clearing typeahead inputs.
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @return {void}
     */
    Typeahead.prototype._clearInputs = function(input, hidden_input) {
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
    Typeahead.prototype._enable = function(input, hidden_input) {
        that = this;

        // enable typeahead
        $(input).typeahead({
            // ajax
            ajax: {
                url: $(input).data('url'),
                timeout: 500,
                triggerLength: 4,
                method: 'get',
                preProcess: function(data) {
                    if (data.success === false) {
                        // Hide the list, there was some error
                        return false;
                    }
                    result = [];
                    $.each(data.data, function(k, v) {
                        result.push({
                            id: k,
                            name: v
                        });
                    });

                    return result;
                }
            },
            onSelect: function(data) {
                that._onSelect(input, hidden_input, data);
            }
        });
    };

    /**
     * Method responsible for handling behavior on typeahead option selection.
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @param  {object} data         ajax call returned data
     * @return {void}
     */
    Typeahead.prototype._onSelect = function(input, hidden_input, data) {
        $(hidden_input).val(data.value);
        $(input).prop('readonly', true);
    };

    typeahead = new Typeahead([]);

    typeahead.init();

})(jQuery);

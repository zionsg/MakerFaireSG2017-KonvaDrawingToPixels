/**
 * Utility functions
 */

var utils = (function () {
    // Self reference - all public vars/methods will be stored in here and returned as public interface
    var self = {};

    var endpointUrl = 'app/';

    /**
     * Send drawing to endpoint
     *
     * @param  string imageDataUri
     * @param  callable responseCallback Takes in (isSuccess, statusCode, responseData) and returns void
     * @return void
     */
    self.sendDrawing = function (cells, responseCallback) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: endpointUrl,
            data: {
                cells: cells
            }
        }).done(function (data, textStatus, jqXHR) {
            var isSuccess = true,
                statusCode = jqXHR.status,
                responseData = data;

            console.log(statusCode, responseData);
            responseCallback(isSuccess, statusCode, responseData);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var isSuccess = false,
                statusCode = jqXHR.status,
                responseData = jqXHR.responseJSON;

            console.log(statusCode, responseData);
            responseCallback(isSuccess, statusCode, responseData);
        });
    };

    /**
     * Get random color
     *
     * @return string Hexadecimal code for color including leading #
     */
    self.getRandomColor = function () {
        var letters = '0123456789ABCDEF';
        var result = '#';

        for (var i = 0; i < 6; i++) {
            result += letters[Math.floor(Math.random() * 16)];
        }

        return result;
    };

    /**
     * Simple string replacement function
     *
     * @example sprintf('<img src="%s" class="%s" />', 'a.png', 'beta') => <img src="a.png" class="beta" />
     * @param   string format    Use "%s" as placeholder
     * @param   ...    arguments Add as many arguments as there are %s after the format
     * @return  string
     */
    self.sprintf = function (format) {
        for (var i=1; i < arguments.length; i++) {
            format = format.replace(/%s/, arguments[i]);
        }

        return format;
    }

    // Return public interface
    return self;
})();

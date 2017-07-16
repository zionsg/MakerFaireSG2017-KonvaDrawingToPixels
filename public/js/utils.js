/**
 * Utility functions
 */

var utils = (function () {
    // Self reference - all public vars/methods will be stored in here and returned as public interface
    var self = {};

    /**
     * Send drawing to endpoint
     *
     * Layout of LED wall if config.isFirstRowLeftToRight = true
     *   1  2  3
     *   6  5  4
     *
     * Layout of LED wall if config.isFirstRowLeftToRight = false
     *   3  2  1
     *   4  5  6
     *
     * @param  object config
     * @param  string imageDataUri
     * @param  callable responseCallback Takes in (isSuccess, statusCode, responseData) and returns void
     * @return void
     */
    self.sendDrawing = function (config, cells, responseCallback) {
        var grid = [];
        for (row = 0; row < config.cellsPerColumn; row++) {
            var rowInfo = [];

            for (col = 0; col < config.cellsPerRow; col++) {
                cell = cells[row][col];

                // Blank cell shows as gray on screen but send black to endpoint
                if (config.COLOR_BLANK_DISPLAY === cell) {
                    cell = config.COLOR_BLANK_VALUE;
                }

                if (config.isFirstRowLeftToRight) {
                    if (0 === (row % 2)) {
                        rowInfo.push(cell);
                    } else {
                        rowInfo.unshift(cell);
                    }
                } else {
                    if (0 === (row % 2)) {
                        rowInfo.unshift(cell);
                    } else {
                        rowInfo.push(cell);
                    }
                }
            }

            grid.push(rowInfo.join());
        }
        console.log(grid);

        $.ajax({
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded; charset=utf-8', // Arduino board set to this content type
            dataType: 'text',
            url: config.endpointUrl,
            data: {
                data: grid.join()
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

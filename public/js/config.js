/**
 * Config
 */

var config = (function () {
    // Self reference - all public vars/methods will be stored in here and returned as public interface
    var self = {
        endpointUrl: 'http://127.0.0.1/test_endpoint.php',
        cellWidth: 100,
        cellsPerRow: 11,
        cellsPerColumn: 6
    };

    // Return public interface
    return self;
})();

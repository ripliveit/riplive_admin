/**
 * Define a custom filter for convert bytes in Kilobytes
 */
app.filter('sizeFilter', function () {
    return function (size) {
        return Math.round(size / 1000) + ' KB';
    };
});
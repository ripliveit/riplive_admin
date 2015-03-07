/**
 * Custom service that return an instance of id3,
 * a library used to retrieve mp3's ID3 metadata. 
 */
app.factory('ID3', function () {
    return window['ID3'];
});
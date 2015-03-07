/**
 * Custom Service that return an istance of Amazon S3 client.
 */
app.factory('S3', function (AWS) {
    return {
        getBucket: function () {
            var bucket = new AWS.S3({
                params: {
                    Bucket: 'riplive.it-podcast'
                }
            });

            return bucket;
        }
    };
});
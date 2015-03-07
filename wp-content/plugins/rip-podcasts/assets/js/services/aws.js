/**
 * Custom Service that return an instance of Amazon AWS API Client.
 */
app.factory('AWS', function () {
    AWS.config.update({
        accessKeyId: 'AKIAJMWTVZGFOYVC6FRA',
        secretAccessKey: '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'
    });

    AWS.config.region = 'eu-west-1';

    return AWS;
});
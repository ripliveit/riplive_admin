/**
 * A service that implements useful method used by PodcastCtrl.
 */
app.factory('podcastsService', function (ID3) {
    return {
        /*
         * Retrieve all tag from an mp3 file using id3 Library.
         * @param {object} file
         * @param {callback} cb
         */
        getAllTags: function (file, cb) {
            ID3.loadTags(file, function () {
                var tags = ID3.getAllTags(file);
                cb(tags);
            }, {
                dataReader: FileAPIReader(file)
            });
        },
        /**
         * Return a date from a string formatted in this manner:
         * back-to-the-movies_s01e01_19-05-2013
         * 
         * @param {string} string
         * @returns {string}
         */
        getPodcastEpisodeDate: function (string) {
            var parts = this.stripFileExtension(string).split('_');
            var date = parts[2] !== undefined ? parts[2] : new Date();

            return date;
        },
        /**
         * Strip the file extension from a string.
         * @param {string} string
         * @returns {string}
         */
        stripFileExtension: function (string) {
            var filename = string.split('.');
            return filename[0];
        }
    };
});
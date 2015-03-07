/**
 * A Service that implement two method to send and retrieve data from the server.
 */
app.factory('dataService', function ($http, $upload) {
    return {
        /**
         * Load data performing a GET request.
         * 
         * @param {object} params
         * @returns {unresolved}
         */
        loadData: function (params) {
            var params = params || {};

            var promise = $http({
                method: 'GET',
                url: '/wp-admin/admin-ajax.php',
                params: params
            }).then(function (data) {
                if (data)
                    return data;
            });

            return promise;
        },
        
        /**
         * Make an Http POST
         * 
         * @param {Object} params
         * @param {Object} data
         * @returns {unresolved}             
         */
        postData: function (params, data) {
            var params = params || {};
            var data = jQuery.param(data) || {};

            var promise = $http({
                method: 'POST',
                url: '/wp-admin/admin-ajax.php',
                params: params,
                data: data,
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).then(function (data) {
                if (data)
                    return data;
            });

            return promise;
        },
        
        /**
         * Upload a file.
         * 
         * @param {Object} params
         * @param {Object} file
         * @returns {unresolved}
         */
        uploadFile: function (params, file) {
            var params = params || {};
            var file = file || {};

            var promise = $upload.upload({
                url: '/wp-admin/admin-ajax.php?action=rip_podcasts_upload_podcast_image',
                params: params,
                file: file
            });

            return promise;
        }
    };
});
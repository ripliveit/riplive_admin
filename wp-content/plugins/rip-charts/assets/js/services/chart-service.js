app.factory('chartsService', function ($http) {
    return {
        loadData: function (params) {
            var params = params || {};

            return $http({
                method: 'GET',
                url: '/wp-admin/admin-ajax.php',
                params: params
            }).then(function (data) {
                if (data)
                    return data;
            });
        },
        postData: function (params, data) {
            var params = params || {};
            var data = jQuery.param(data) || {};

            return $http({
                method: 'POST',
                url: '/wp-admin/admin-ajax.php',
                params: params,
                data: data,
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            });
        },
        checkSongInChart: function (idSong, chart) {
            for (var i = 0; i < chart.length; i++) {
                if (parseInt(idSong) === parseInt(chart[i].id_song)) {
                    return true;
                }
            }

            return false;
        },
        createChartPositions: function (length) {
            var ar = [];

            for (var i = 0; i < length; i++) {
                ar.push(i);
            }

            return ar;
        },
        createChartPositionsWithId: function (array) {
            var positions = this.createChartPositions(array.length);

            return positions.map(function (item) {
                return {
                    position: item + 1,
                    id_chart_song: array[item].id_chart_song
                };
            });
        },
        getDate: function () {
            return  (new Date()).toISOString().slice(0, 10);
        }
    };
});
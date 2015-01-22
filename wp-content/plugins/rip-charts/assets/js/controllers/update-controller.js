/**
 * Handle the update of a saved chart. 
 */
app.controller('UpdateCtrl', function ($scope, $routeParams, $location, $timeout, chartsService) {
    var numberOfPositions = 0;

    chartsService.loadData({
        action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
        slug: $routeParams.chart_archive_slug
    }).then(function (res) {
        $scope.loaded = true;

        $scope.chart = res.data.complete_chart;
        $scope.positions = chartsService.createChartPositionsWithId($scope.chart['songs']);
        numberOfPositions = parseInt($scope.chart['chart_songs_number']);
    });

    chartsService.loadData({
        action: 'rip_songs_get_all_songs'
    }).then(function (res) {
        $scope.songs = res.data.songs;
    });

    chartsService.loadData({
        action: 'rip_songs_get_songs_genres'
    }).then(function (res) {
        $scope.genres = res.data.genres;
    });

    $scope.add = function (item) {
        if ($scope.chart['songs'].length === numberOfPositions) {
            return false;
        }

        if (chartsService.checkSongInChart(item.id_song, $scope.chart['songs'])) {
            return false;
        }

        // Retrieve the id of the song that was in nth positions,
        // so an update can be performed. 
        // Position was actual length of the array cause each song is
        // pushed to the end of chart's array.
        var currentPosition = $scope.chart['songs'].length;
        item.id_chart_song = $scope.positions[currentPosition].id_chart_song;
        item.user_vote = 0;
        $scope.chart['songs'].push(item);
    };

    $scope.remove = function (index) {
        $scope.chart['songs'].splice(index, 1);

        // Recalculate all chart_song_id.
        $scope.chart['songs'].forEach(function (item, index) {
            item.id_chart_song = $scope.positions[index].id_chart_song;
        });
    };

    $scope.save = function () {
        if ($scope.chart['songs'].length !== numberOfPositions) {
            alert('Devi includere obbligatoriamente ' + numberOfPositions + ' brani');
            return false;
        }

        chartsService.postData({
            action: 'rip_charts_update_complete_chart'
        }, {
            complete_chart: $scope.chart
        }).success(function (res) {
            if (res) {
                alert('Inserimento avvenuto con successo!');

//                    $timeout(function() {
//                        $location.path('/');
//                    }, 1000);
            }
        }).error(function (err, status) {
            console.log(err);
        });

    };
});
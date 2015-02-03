/**
 * Handle the update of a saved chart. 
 */
app.controller('UpdateCtrl', function ($scope, $routeParams, $location, $rootScope, $timeout, chartsService) {
    var numberOfPositions = 0;
    $scope.chartLoading = true;
    $scope.songsLoading = true;

    chartsService.loadData({
        action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
        slug: $routeParams.chart_archive_slug
    }).then(function (res) {
        $scope.chartLoading = false;

        $scope.chart = res.data.complete_chart;
        $scope.positions = chartsService.createChartPositionsWithId($scope.chart['songs']);
        numberOfPositions = parseInt($scope.chart['chart_songs_number']);
    });

    chartsService.loadData({
        action: 'rip_songs_get_all_songs'
    }).then(function (res) {
        $scope.songsLoading = false;
        $scope.songs = res.data.songs;
    });

    chartsService.loadData({
        action: 'rip_songs_get_songs_genres'
    }).then(function (res) {
        $scope.genres = res.data.genres;
    });

    // Filter
    // songs by the selected genre.
    $scope.filterBySongGenre = function (value, index) {
        // If there's no
        // genre return the song,
        if (!$scope.searchedGenre || $scope.searchedGenre.name === '') {
            return value;
        }

        // Return song with the same
        // selected genre.
        return value.song_genre[0].name === $scope.searchedGenre.name ? value : false;
    };

    $scope.goToIndex = function () {
        $timeout(function () {
            $location.path('/');
        }, 2500);
    };

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
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: 'Devi includere obbligatoriamente ' + numberOfPositions + ' brani'
            });

            return false;
        }

        chartsService.postData({
            action: 'rip_charts_update_complete_chart'
        }, {
            complete_chart: $scope.chart
        }).then(function (res) {
            $rootScope.$broadcast('alert:message', {
                type: 'success',
                message: 'Inserimento avvenuto con successo!'
            });

            $scope.goToIndex();

        }, function (err) {
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: err.data.message
            });

            $scope.goToIndex();
        });
    };
});
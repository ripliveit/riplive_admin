/**
 *  Display a list of empty positions that can be filled with a song.
 *  Load all songs.
 */
app.controller('CreateCtrl', function ($scope, $routeParams, $location, $rootScope, $timeout, chartsService) {
    var numberOfPositions = parseInt($routeParams.position) || 50;
    $scope.chart_slug = $routeParams.slug;
    $scope.chartLoading = true;
    $scope.songsLoading = true;

    chartsService.loadData({
        action: 'rip_charts_get_chart_by_slug',
        slug: $routeParams.slug
    }).then(function (res) {
        $scope.chartLoading = false;
        $scope.chart = res.data.chart;
        $scope.chart['chart_archive_slug'] = $scope.chart['chart_slug'] + '-' + chartsService.getDate();
        $scope.chart['chart_songs_number'] = numberOfPositions;
        $scope.chart['songs'] = [];
        $scope.positions = chartsService.createChartPositions(numberOfPositions);
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

    // Check if all position are filled with songs,
    // or if a song is already present in the list,
    // then add an a song to the cart.
    $scope.add = function (item) {
        if ($scope.chart['songs'].length === numberOfPositions) {
            return false;
        }

        if (chartsService.checkSongInChart(item.id_song, $scope.chart['songs'])) {
            return false;
        }

        item.user_vote = 0;
        $scope.chart['songs'].push(item);
    };

    $scope.remove = function (index) {
        $scope.chart['songs'].splice(index, 1);
    };

    $scope.save = function () {
        if ($scope.chart['songs'].length < numberOfPositions) {
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: 'Devi includere obbligatoriamente ' + numberOfPositions + ' brani'
            });

            return false;
        }

        $scope.chartLoading = true;

        chartsService.postData({
            action: 'rip_charts_insert_complete_chart'
        }, {
            complete_chart: $scope.chart
        }).then(function (res) {
            if (res) {
                $scope.chartLoading = false;
                $rootScope.$broadcast('alert:message', {
                    type: 'success',
                    message: 'Inserimento avvenuto con successo!'
                });

                $scope.goToIndex();
            }
        }, function (err) {
            $scope.chartLoading = false;
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: err.data.message
            });

            $scope.goToIndex();
        });
    };
});
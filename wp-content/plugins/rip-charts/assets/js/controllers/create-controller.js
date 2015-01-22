/**
 *  Display a list of empty positions that can be filled with a song.
 *  Load all songs.
 */
app.controller('CreateCtrl', function ($scope, $routeParams, $location, $timeout, chartsService) {
    var numberOfPositions = parseInt($routeParams.position) || 50;
    $scope.chart_slug = $routeParams.slug;

    chartsService.loadData({
        action: 'rip_charts_get_chart_by_slug',
        slug: $routeParams.slug
    }).then(function (res) {
        $scope.loaded = true;
        $scope.chart = res.data.chart;
        $scope.chart['chart_archive_slug'] = $scope.chart['chart_slug'] + '-' + chartsService.getDate();
        $scope.chart['chart_songs_number'] = numberOfPositions;
        $scope.chart['songs'] = [];
        $scope.positions = chartsService.createChartPositions(numberOfPositions);
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
            alert('Devi includere obbligatoriamente ' + numberOfPositions + ' brani');
            return false;
        }

        $scope.loaded = false;

        chartsService.postData({
            action: 'rip_charts_insert_complete_chart'
        }, {
            complete_chart: $scope.chart
        }).then(function (res) {
            if (res) {
                $scope.loaded = true;
                alert('Inserimento avvenuto con successo!');

                $timeout(function () {
                    $location.path('/');
                }, 1000);
            }
        }, function (err) {
            alert(err.data.message);
        });

    };
});
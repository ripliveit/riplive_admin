<div id="admin-charts" ng-app="adminChart">
    <h1>
        Gestione Classifiche
    </h1>

    <section id="charts">
        <div ng-view></div>
    </section>
</div>

<script>
    var app = angular.module('adminChart', ['ngRoute']);

    app.config(function($routeProvider) {
        $routeProvider.when('/', {
            controller: 'ChartsListCtrl',
            templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/charts.html'
        }).when('/charts/:page', {
            controller: 'ChartsListCtrl',
            templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/charts.html'
        }).when('/chart/:slug/:chart_archive_slug', {
            controller: 'ModifyChartCtrl',
            templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/chart.html'
        }).when('/new/:slug/positions/:position', {
            controller: 'NewChartCtrl',
            templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/chart.html'
        }).otherwise({
            redirectTo: "/"
        });
    });

    app.factory('chartsService', function($http) {
        return {
            loadData: function(params) {
                var params = params || {};

                var promise = $http({
                    method: 'GET',
                    url: '/wp-admin/admin-ajax.php',
                    params: params
                }).then(function(data) {
                    if (data)
                        return data;
                });

                return promise;
            },
            postData: function(params, data) {
                var params = params || {};
                var data = jQuery.param(data) || {};

                var promise = $http({
                    method: 'POST',
                    url: '/wp-admin/admin-ajax.php',
                    params: params,
                    data: data,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                }).then(function(data) {
                    if (data)
                        return data;
                });

                return promise;
            },
            checkSongInChart: function(idSong, chart) {
                for (var i = 0; i < chart.length; i++) {
                    if (parseInt(idSong) === parseInt(chart[i].id_song)) {
                        return true;
                    }
                }

                return false;
            },
            createChartPositions: function(length) {
                var ar = [];

                for (var i = 0; i < length; i++) {
                    ar.push(i);
                }

                return ar;
            },
            createChartPositionsWithId: function(array) {
                var positions = this.createChartPositions(array.length);

                return positions.map(function(item) {
                    return {
                        position: item + 1,
                        id_chart_song: array[item].id_chart_song
                    };
                });
            },
            getDate: function() {
                return  (new Date()).toISOString().slice(0, 10);
            }
        };
    });

    /**
     * Display the list of all charts.
     * Load all charts data to populate the select box
     * and all archive charts to show them on a list.
     */
    app.controller('ChartsListCtrl', function($scope, $routeParams, $location, chartsService) {
        var page = $routeParams.page || 1;

        chartsService.loadData({
            action: 'rip_charts_get_all_charts'
        }).then(function(res) {
            $scope.allCharts = res.data.charts;
        });

        chartsService.loadData({
            action: 'rip_charts_get_all_complete_charts',
            page: page
        }).then(function(res) {
            $scope.charts = res.data.complete_charts;
        });

        // Load the new views on select box change only if
        // a chart with same date and slug IS NOT already present in wp_charts_archive.
        $scope.$watch('selectedChart', function(newValue) {
            if (newValue !== undefined) {
                var slug = newValue.chart_slug + '-' + chartsService.getDate();

                chartsService.loadData({
                    action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
                    slug: slug
                }).then(function(res) {
                    if (res.data.status === 'ok') {
                        alert('Puoi inserire al massimo una classifica dello stesso tipo al giorno');
                        return false;
                    }

                    $scope.chartPosition = $scope.chartPosition === undefined ? 50 : $scope.chartPosition;
                    $location.path('/new/' + newValue.chart_slug + '/positions/' + $scope.chartPosition);
                });
            }
        });

        $scope.duplicate = function(ChartArchiveSlug) {
            chartsService.loadData({
                action: 'rip_charts_duplicate_complete_chart',
                slug: ChartArchiveSlug
            }).then(function(res) {
                $scope.charts.unshift(res.data);
            }, function(err) {
                if (err.data.status === 'error') {
                    alert(err.data.message);

                    return false;
                }
            });
        };

        $scope.destroy = function(ChartArchiveSlug, $index) {
            chartsService.loadData({
                action: 'rip_charts_delete_complete_chart',
                slug: ChartArchiveSlug
            }).then(function(res) {
                $scope.charts.splice($index, 1);
            });
        };
    });

    /**
     *  Display a list of empty positions that can be filled with a song.
     *  Load all songs.
     */
    app.controller('NewChartCtrl', function($scope, $routeParams, $location, $timeout, chartsService) {
        var numberOfPositions = parseInt($routeParams.position) || 50;
        $scope.chart_slug = $routeParams.slug;

        chartsService.loadData({
            action: 'rip_charts_get_chart_by_slug',
            slug: $routeParams.slug
        }).then(function(res) {
            $scope.loaded = true;
            $scope.chart = res.data.chart;
            $scope.chart['chart_archive_slug'] = $scope.chart['chart_slug'] + '-' + chartsService.getDate();
            $scope.chart['chart_songs_number'] = numberOfPositions;
            $scope.chart['songs'] = [];
            $scope.positions = chartsService.createChartPositions(numberOfPositions);
        });

        chartsService.loadData({
            action: 'rip_songs_get_all_songs'
        }).then(function(res) {
            $scope.songs = res.data.songs;
        });

        chartsService.loadData({
            action: 'rip_songs_get_songs_genres'
        }).then(function(res) {
            $scope.genres = res.data.genres;
        });

        // Check if all position are filled with songs,
        // or if a song is already present in the list,
        // then add an a song to the cart.
        $scope.add = function(item) {
            if ($scope.chart['songs'].length === numberOfPositions) {
                return false;
            }

            if (chartsService.checkSongInChart(item.id_song, $scope.chart['songs'])) {
                return false;
            }
            item.user_vote = 0;
            $scope.chart['songs'].push(item);
        };

        $scope.remove = function(index) {
            $scope.chart['songs'].splice(index, 1);
        };

        $scope.save = function() {
            if ($scope.chart['songs'].length < numberOfPositions) {
                alert('Devi includere obbligatoriamente ' + numberOfPositions + ' brani');
                return false;
            }
            
            $scope.loaded = false;
            
            chartsService.postData({
                action: 'rip_charts_insert_complete_chart'
            }, {
                complete_chart: $scope.chart
            }).then(function(res) {
                if (res) {
                    $scope.loaded = true;
                    alert('Inserimento avvenuto con successo!');
                    
                    $timeout(function() {
                        $location.path('/');
                    }, 1000);
                }
            });

        };
    });

    /**
     * Handle the update of a saved chart. 
     */
    app.controller('ModifyChartCtrl', function($scope, $routeParams, $location, $timeout, chartsService) {
        var numberOfPositions = 0;

        chartsService.loadData({
            action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
            slug : $routeParams.chart_archive_slug
        }).then(function(res) {
            $scope.loaded = true;
            
            $scope.chart = res.data.complete_chart;
            $scope.positions = chartsService.createChartPositionsWithId($scope.chart['songs']);
            numberOfPositions = parseInt($scope.chart['chart_songs_number']);
        });

        chartsService.loadData({
            action: 'rip_songs_get_all_songs'
        }).then(function(res) {
            $scope.songs = res.data.songs;
        });

        chartsService.loadData({
            action: 'rip_songs_get_songs_genres'
        }).then(function(res) {
            $scope.genres = res.data.genres;
        });

        $scope.add = function(item) {
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

        $scope.remove = function(index) {
            $scope.chart['songs'].splice(index, 1);

            // Recalculate all chart_song_id.
            $scope.chart['songs'].forEach(function(item, index) {
                item.id_chart_song = $scope.positions[index].id_chart_song;
            });
        };

        $scope.save = function() {
            if ($scope.chart['songs'].length !== numberOfPositions) {
                alert('Devi includere obbligatoriamente ' + numberOfPositions + ' brani');
                return false;
            }

            chartsService.postData({
                action: 'rip_charts_update_complete_chart'
            }, {
                complete_chart: $scope.chart
            }).then(function(res) {
                if (res) {
                    alert('Inserimento avvenuto con successo!');

                    $timeout(function() {
                        $location.path('/');
                    }, 1000);
                }
            });

        };
    });

    /**
     * Draw all pagination link 
     */
    app.controller('PaginationCtrl', function($scope, chartsService) {
        chartsService.loadData({
            action: 'rip_charts_get_complete_charts_number_of_pages'
        }).then(function(res) {
            $scope.pages = [];

            for (var i = 1; i <= res.data.number_of_pages.pages; i++) {
                $scope.pages.push({
                    page_number: i,
                    link: '#/charts/' + i
                });
            }
        });
    });
</script>
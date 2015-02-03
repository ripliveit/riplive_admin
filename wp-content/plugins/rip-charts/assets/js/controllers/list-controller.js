/**
 * Display the list of all charts.
 * Load all charts data to populate the select box
 * and all archive charts to show them on a list.
 */
app.controller('ListCtrl', function ($scope, $routeParams, $location, $rootScope, chartsService) {
    var page = $routeParams.page || 1;

    chartsService.loadData({
        action: 'rip_charts_get_all_charts'
    }).then(function (res) {
        $scope.allCharts = res.data.charts;
    });

    chartsService.loadData({
        action: 'rip_charts_get_all_complete_charts',
        page: page
    }).then(function (res) {
        $scope.charts = res.data.complete_charts;
    });

    // Insert a new chart only if
    // a chart with same date and slug IS NOT already present into the database.
    $scope.$watch('selectedChart', function (newValue) {
        if (newValue !== undefined) {
            var slug = newValue.chart_slug + '-' + chartsService.getDate();

            chartsService.loadData({
                action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
                slug: slug
            }).then(function (res) {
                if (res.data.status === 'ok') {
                    $rootScope.$broadcast('alert:message', {
                        type: 'error',
                        message: 'Puoi inserire al massimo una classifica dello stesso tipo al giorno'
                    });
                    return false;
                }

            }, function (err) {
                if (err.status !== 404) {
                    $rootScope.$broadcast('alert:message', {
                        type: 'error',
                        message: err.data.message
                    });
                }

                $scope.chartPosition = $scope.chartPosition === undefined ? 50 : $scope.chartPosition;
                $location.path('/new/' + newValue.chart_slug + '/positions/' + $scope.chartPosition);
            });
        }
    });

    $scope.duplicate = function (ChartArchiveSlug) {
        chartsService.loadData({
            action: 'rip_charts_duplicate_complete_chart',
            slug: ChartArchiveSlug
        }).then(function (res) {
            $scope.charts.unshift(res.data.complete_chart);
            $rootScope.$broadcast('alert:message', {
                type: 'success',
                message: 'Duplicazione avvenuta con successo'
            });

        }, function (err) {
            if (err.data.status === 'error') {
                $rootScope.$broadcast('alert:message', {
                    type: 'error',
                    message: 'Puoi inserire al massimo una classifica dello stesso tipo al giorno'
                });

                return false;
            }
        });
    };

    $scope.destroy = function (ChartArchiveSlug, $index) {
        chartsService.loadData({
            action: 'rip_charts_delete_complete_chart',
            slug: ChartArchiveSlug
        }).then(function (res) {
            $scope.charts.splice($index, 1);
        }, function (err) {
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: err.data.message
            });
        });
    };
});
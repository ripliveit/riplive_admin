/**
 * Display the list of all charts.
 * Load all charts data to populate the select box
 * and all archive charts to show them on a list.
 */
app.controller('ListCtrl', function ($scope, $routeParams, $location, chartsService) {
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

    // Load the new views on select box change only if
    // a chart with same date and slug IS NOT already present in wp_charts_archive.
    $scope.$watch('selectedChart', function (newValue) {
        if (newValue !== undefined) {
            var slug = newValue.chart_slug + '-' + chartsService.getDate();

            chartsService.loadData({
                action: 'rip_charts_get_complete_chart_by_chart_archive_slug',
                slug: slug
            }).then(function (res) {
                if (res.data.status === 'ok') {
                    alert('Puoi inserire al massimo una classifica dello stesso tipo al giorno');
                    return false;
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
            $scope.charts.unshift(res.data);
        }, function (err) {
            if (err.data.status === 'error') {
                if(err.data.type === 'duplicate') {
                    err.data.message = 'Puoi inserire al massimo una classifica dello stesso tipo al giorno';
                } 
                
                alert(err.data.message);

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
        });
    };
});
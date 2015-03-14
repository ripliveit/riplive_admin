/*
 * Handle all the view logic oh the program view.
 */
app.controller('ProgramsCtrl', function ($scope, $routeParams, dataService) {
    dataService.loadData({
        action: 'rip_programs_get_all_programs',
        'status[]': ['publish', 'pending']
    }).then(function (res) {
        $scope.programs = res.data.programs;
    });

    $scope.generateXML = function (program, index) {
        $scope.programs[index].computing = true;

        dataService.loadData({
            action: 'rip_podcasts_generate_podcasts_xml',
            slug: program.slug
        }).then(function (res) {
            $scope.programs[index].message = 'Feed XML correttamente generato';
            $scope.programs[index].remoteFeed = res.data.remote_path;
        }, function (err) {
            $scope.programs[index].message = err.data.message;
        }).finally(function () {
            $scope.programs[index].computing = false;
        });
    };
});
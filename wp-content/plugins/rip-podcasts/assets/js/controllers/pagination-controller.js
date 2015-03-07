/**
 * Pagination controller.
 * Create an array with the total number of pages and the relative links.
 */
app.controller('PaginationCtrl', function ($scope, $routeParams, dataService) {
    var programSlug = $routeParams.program_slug || null;

    dataService.loadData({
        action: 'rip_podcasts_get_podcasts_number_of_pages',
        slug: programSlug
    }).then(function (res) {
        $scope.pages = [];

        for (var i = 1; i <= res.data.number_of_pages.pages; i++) {
            $scope.pages.push({
                page_number: i,
                link: '#/podcasts/' + programSlug + '/page/' + i
            });
        }
    });
});
/**
 * Draw all pagination link 
 */
app.controller('PaginationCtrl', function ($scope, chartsService) {
    chartsService.loadData({
        action: 'rip_charts_get_complete_charts_number_of_pages'
    }).then(function (res) {
        $scope.pages = [];

        for (var i = 1; i <= res.data.number_of_pages.pages; i++) {
            $scope.pages.push({
                page_number: i,
                link: '#/charts/' + i
            });
        }
    }, function(err) {
        alert('Error in drawing Chart pages');
    });
});
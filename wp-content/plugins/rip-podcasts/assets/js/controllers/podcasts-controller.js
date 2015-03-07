/**
 * Handle all the logic of the podcast view.  
 * Display all the podcast of a particular program and handle the erase of a single podcast,
 * both from local database and the remote Amazon S3 repository.
 */
app.controller('PodcastsCtrl', function ($scope, $routeParams, S3, $rootScope, dataService) {
    var programSlug = $routeParams.program_slug || null;
    var pageNumber = $routeParams.page || 1;
    var bucket = S3.getBucket();
    var date = new Date();

    $scope.year = date.getFullYear();

    dataService.loadData({
        action: 'rip_podcasts_get_all_podcasts_by_program_slug',
        slug: programSlug,
        page: pageNumber
    }).then(function (res) {
        $scope.podcasts = res.data.podcasts;

        if ($scope.podcasts.length === 0) {
            $scope.empty = true;
            $scope.slug = programSlug;
        }
    });

    $scope.remove = function (podcast, index) {
        var key = podcast.program_slug + '/' + podcast.year + '/' + podcast.file_name;

        var params = {
            Bucket: 'riplive.it-podcast',
            Key: key
        };

        var request = bucket.deleteObject(params);

        // When the podcast is removed from Amazon than a delete 
        // operation is performed on local database.
        request.on('complete', function (res) {
            if (res.err) {
                console.log(err);

                $rootScope.$broadcast('alert:message', {
                    type: 'error',
                    message: 'Errore nella rimozione del podcast, contattare il webmaster'
                });
            } else {
                dataService.loadData({
                    action: 'rip_podcasts_delete_podcast',
                    id_podcast: podcast.id
                }).then(function (res) {
                    $scope.podcasts.splice(index, 1);
                    
                    $rootScope.$broadcast('alert:message', {
                        type: 'success',
                        message: 'Rimozione avvenuta con successo!'
                    });
                }, function (err) {
                    $rootScope.$broadcast('alert:message', {
                        type: 'error',
                        message: 'Errore nella rimozione del podcast, contattare il webmaster'
                    });
                });
            }
        });

        request.send();
    };
});
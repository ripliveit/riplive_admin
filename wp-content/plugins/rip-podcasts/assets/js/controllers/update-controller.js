/**
 * Update a single podcast
 */
app.controller('UpdateCtrl', function ($scope, $routeParams, $timeout, $location, $rootScope, dataService) {
    var programSlug = $routeParams.program_slug || null;
    var idPodcast = $routeParams.id;

    $scope.file;

    dataService.loadData({
        action: 'rip_podcasts_get_podcast_by_id',
        id_podcast: idPodcast
    }).then(function (res) {
        $scope.podcast = res.data.podcast;
        $scope.podcast.uploading = false;
        $scope.podcast.computing = false;
    });

    $scope.goToIndex = function () {
        $timeout(function () {
            $location.path('/');
        }, 2500);
    };

    $scope.onFileSelect = function ($files) {
        $scope.uploading = true;

        for (var i = 0; i < $files.length; i++) {
            if ($files[i].type !== 'image/jpeg') {
                $rootScope.$broadcast('alert:message', {
                    type: 'error',
                    message: 'Puoi caricare solamente immagini di formato jpg'
                });
                
                return false;
            }

            $files[i].progress = 0;

            $scope.file = $files[i];
        }
    };

    $scope.uploadImage = function (podcast, index, event) {
        if ($scope.file) {
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: 'Modifica il podcast prima di salvare'
            });
            
            return false;
        }
       
        $scope.podcast.computing = true;

        dataService.uploadFile({
            id_podcast: podcast.id
        }, $scope.file).success(function (data, status, headers, config) {
            if (data.status === 'error') {
                alert(data.message);
                return false;
            }
            alert('File caricato con successo!');
            $scope.podcast.podcast_images.thumbnail = data.podcast_images.thumbnail;
            $scope.podcast.uploading = false;
            $scope.podcast.computing = false;
        });
    };

    $scope.save = function () {
        dataService.postData({
            action: 'rip_podcasts_update_podcast',
            id_podcast: $scope.podcast.id
        }, {
            podcast: $scope.podcast
        }).then(function (res) {
            $rootScope.$broadcast('alert:message', {
                type: 'success',
                message: 'Podcast modificato correttamente!'
            });
            
            $scope.goToIndex();
        }, function (err) {
            $rootScope.$broadcast('alert:message', {
                type: 'error',
                message: 'Modifica il podcast prima di salvare'
            });
        });
    };
});
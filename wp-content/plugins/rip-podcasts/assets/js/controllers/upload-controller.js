/**
 * Handle all the upload logic. 
 */
app.controller('UploadCtrl', function ($scope, $routeParams, S3, dataService, podcastsService) {
    var programSlug = $routeParams.program_slug || null;
    var bucket = S3.getBucket();
    var url = 'https://s3-eu-west-1.amazonaws.com/riplive.it-podcast/';

    dataService.loadData({
        action: 'rip_programs_get_program_by_slug',
        slug: programSlug
    }).then(function (res) {
        $scope.program = res.data.program;
    });

    $scope.uploading = false;
    $scope.files = [];

    // Every time a file is selected is stored in $scope.files array.
    // Attacch a progress property on each file.
    $scope.onFileSelect = function ($files) {
        $scope.uploading = true;
        for (var i = 0; i < $files.length; i++) {
            if ($files[i].type !== 'audio/mp3' && $files[i].type !== 'audio/mpeg') {
                continue;
            }

            $files[i].progress = 0;

            $scope.files.push($files[i]);
        }
    };

    $scope.upload = function (file, index, event) {
        if (file.year === undefined) {
            alert('Seleziona l\'anno di archiviazione del podcast');
            return false;
        }

        //Unbind to prevent multiple upload of the same file.
        //angular.element(event.currentTarget).unbind();
        var key = $scope.program.slug + '/' + file.year + '/' + file.name;

        var params = {
            ACL: 'public-read',
            Key: key,
            ContentType: file.type,
            Body: file
        };

        var request = bucket.putObject(params);

        // Handle the progress event, displayng the progress bar.
        request.on('httpUploadProgress', function (e) {
            $scope.$apply(function () {
                file.progress = parseInt(100.0 * e.loaded / e.total);

                file.uploading = true;
                // hide the progress bar.
                if (file.progress === 100) {
                    file.computing = true;
                }
            });
        });

        // If the upload to S3 is successfull that insert a record into the local db.
        request.on('complete', function (e) {
            if (e.error) {
                file.computing = false;
                file.error = true;
                return false;
            } else {
                podcastsService.getAllTags(file, function (tags) {
                    var data = {
                        id_program: $scope.program.id_program,
                        title: tags.title,
                        summary: $scope.program.program_excpert,
                        genre: 'Music',
                        authors: tags.artist,
                        file_name: file.name,
                        file_length: file.size,
                        duration: '60:00',
                        year: file.year,
                        date: podcastsService.getPodcastEpisodeDate(file.name),
                        url: url + key
                    };

                    dataService.postData({
                        action: 'rip_podcasts_insert_podcast'
                    }, {
                        podcast: data
                    }).then(function (res) {
                        if (res.status !== 200) {
                            file.computing = false;
                            file.error = true;
                            return false;
                        } else {
                            file.computing = false;
                            file.complete = true;
                        }
                    });
                });
            }
        });

        request.send();
    };

    $scope.remove = function (index) {
        $scope.files.splice(index, 1);
    };
});
<div id="admin-podcasts" ng-app="adminPodcasts">
    <h1>
        Gestione Podcasts
    </h1>

    <section id="podcasts">
        <div ng-view></div>
    </section>
</div>
<script>
    var app = angular.module('adminPodcasts', ['ngRoute', 'angularFileUpload']);

    /**
     * Routing configuration.
     * @param {object} param
     */
    app.config(function($routeProvider) {
        $routeProvider.when('/', {
            controller: 'ProgramsCtrl',
            templateUrl: '/wp-content/plugins/rip-podcasts/view-helpers/partials/angular-template/programs.html'
        }).when('/podcasts/:program_slug/page/:page', {
            controller: 'PodcastsCtrl',
            templateUrl: '/wp-content/plugins/rip-podcasts/view-helpers/partials/angular-template/podcasts.html'
        }).when('/update/:program_slug/:id', {
            controller: 'UpdateCtrl',
            templateUrl: '/wp-content/plugins/rip-podcasts/view-helpers/partials/angular-template/update.html'
        }).when('/upload/:program_slug', {
            controller: 'UploadCtrl',
            templateUrl: '/wp-content/plugins/rip-podcasts/view-helpers/partials/angular-template/upload.html'
        }).otherwise({
            redirectTo: "/"
        });
    });

    /**
     * Define a custom filter for convert bytes in Kilobytes
     */
    app.filter('sizeFilter', function() {
        return function(size) {
            return Math.round(size / 1000) + ' KB';
        };
    });

    /**
     * Custom Service that return an instance of Amazon AWS API Client.
     */
    app.factory('AWS', function() {
        AWS.config.update({
            accessKeyId: 'AKIAJMWTVZGFOYVC6FRA',
            secretAccessKey: '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'
        });

        AWS.config.region = 'eu-west-1';

        return AWS;
    });

    /**
     * Custom Service that return an istance of Amazon S3 client.
     */
    app.factory('S3', function(AWS) {
        return {
            getBucket: function() {
                var bucket = new AWS.S3({
                    params: {
                        Bucket: 'riplive.it-podcast'
                    }
                });

                return bucket;
            }
        };
    });

    /**
     * Custom service that return an instance of id3,
     * a library used to retrieve mp3's ID3 metadata. 
     */
    app.factory('ID3', function() {
        return window['ID3'];
    });

    /**
     * A Service that implement two method to send and retrieve data from the server.
     */
    app.factory('dataService', function($http, $upload) {
        return {
            /**
             * Load data performing a GET request.
             * @param {object} params
             * @returns {unresolved}
             */
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
            /**
             * 
             * @param {type} params
             * @param {type} data
             * @returns {unresolved}             
             */
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
            uploadFile: function(params, file) {
                var params = params || {};
                var file = file || {};

                var promise = $upload.upload({
                    url: '/wp-admin/admin-ajax.php?action=rip_podcasts_upload_podcast_image',
                    params: params,
                    file: file
                });

                return promise;
            }
        };
    });

    /**
     * A service that implements useful method used by PodcastCtrl.
     */
    app.factory('podcastsService', function(ID3) {
        return {
            /*
             * Retrieve all tag from an mp3 file using id3 Library.
             * @param {object} file
             * @param {callback} cb
             */
            getAllTags: function(file, cb) {
                ID3.loadTags(file, function() {
                    var tags = ID3.getAllTags(file);
                    cb(tags);
                }, {
                    dataReader: FileAPIReader(file)
                });
            },
            /**
             * Return a date from a string formatted in this manner:
             * back-to-the-movies_s01e01_19-05-2013
             * 
             * @param {string} string
             * @returns {string}
             */
            getPodcastEpisodeDate: function(string) {
                var parts = this.stripFileExtension(string).split('_');
                var date = parts[2] !== undefined ? parts[2] : new Date();

                return date;
            },
            /**
             * Strip the file extension from a string.
             * @param {string} string
             * @returns {string}
             */
            stripFileExtension: function(string) {
                var filename = string.split('.');
                return filename[0];
            }
        };
    });


    /*
     * Handle all the view logic oh the program view.
     */
    app.controller('ProgramsCtrl', function($scope, $routeParams, dataService) {
        dataService.loadData({
            action: 'rip_programs_get_all_programs_for_podcasts'
        }).then(function(res) {
            $scope.programs = res.data.programs;
        });

        $scope.generateXML = function(program, index) {
            dataService.loadData({
                action: 'rip_podcasts_generate_podcasts_xml',
                slug: program.slug
            }).then(function(res) {
                if (res.data.status === 'ok') {
                    $scope.programs[index].message = 'Feed XML correttamente generato';
                    $scope.programs[index].remoteFeed = res.data.remote_path;
                } else {
                    $scope.programs[index].message = res.data.message;
                }
            });
        };
    });


    /**
     * Handle all the logic of the podcast view.  
     * Display all the podcast of a particular program and handle the erase of a single podcast,
     * both from local database and the remote Amazon S3 repository.
     */
    app.controller('PodcastsCtrl', function($scope, $routeParams, S3, dataService) {
        var programSlug = $routeParams.program_slug || null;
        var pageNumber = $routeParams.page || 1;
        var bucket = S3.getBucket();
        var date = new Date();

        $scope.year = date.getFullYear();

        dataService.loadData({
            action: 'rip_podcasts_get_all_podcasts_by_program_slug',
            slug: programSlug,
            page: pageNumber
        }).then(function(res) {
            $scope.podcasts = res.data.podcasts;

            if ($scope.podcasts.length === 0) {
                $scope.empty = true;
                $scope.slug = programSlug;
            }
        });

        $scope.remove = function(podcast, index) {
            var key = podcast.program_slug + '/' + podcast.year + '/' + podcast.file_name;

            var params = {
                Bucket: 'riplive.it-podcast',
                Key: key
            };

            var request = bucket.deleteObject(params);

            // When the podcast is removed from Amazon than a delete 
            // operation is performed on local database.
            request.on('complete', function(res) {
                if (res.err) {
                    console.log(err);
                    alert('Errore nella rimozione del podcast, contattare il webmaster');
                } else {
                    dataService.loadData({
                        action: 'rip_podcasts_delete_podcast',
                        id_podcast: podcast.id
                    }).then(function(res) {
                        if (res.status !== 200) {
                            alert('Errore nella rimozione del podcast, contattare il webmaster');
                            return false;
                        } else {
                            alert('Rimozione avvenuta con successo!');
                            $scope.podcasts.splice(index, 1);
                        }
                    });
                }
            });

            request.send();
        };
    });

    /**
     * Update a single podcast
     */
    app.controller('UpdateCtrl', function($scope, $routeParams, $timeout, $location, dataService) {
        var programSlug = $routeParams.program_slug || null;
        var idPodcast = $routeParams.id;

        $scope.file;

        dataService.loadData({
            action: 'rip_podcasts_get_podcast_by_id',
            id_podcast: idPodcast
        }).then(function(res) {
            $scope.podcast = res.data.podcast;
            $scope.podcast.uploading = false;
            $scope.podcast.computing = false;
        });

        $scope.onFileSelect = function($files) {
            $scope.uploading = true;

            for (var i = 0; i < $files.length; i++) {
                if ($files[i].type !== 'image/jpeg') {
                    continue;
                }

                $files[i].progress = 0;

                $scope.file = $files[i];
            }
        };

        $scope.uploadImage = function(podcast, index, event) {
            $scope.podcast.computing = true;

            dataService.uploadFile({
                id_podcast: podcast.id
            }, $scope.file).success(function(data, status, headers, config) {
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

        $scope.save = function() {
            dataService.postData({
                action: 'rip_podcasts_update_podcast',
                id_podcast: $scope.podcast.id
            }, {
                podcast: $scope.podcast
            }).then(function(res) {
                if (res.data.status === 'error') {
                    alert(res.data.message);
                    return false;
                } else {
                    alert('Podcast modificato correttamente!');
                    
                    $timeout(function() {
                        $location.path('/podcasts/' + programSlug + '/page/1');
                    }, 1000);
                }
            });
        };
    });

    /**
     * Handle all the upload logic. 
     */
    app.controller('UploadCtrl', function($scope, $routeParams, S3, dataService, podcastsService) {
        var programSlug = $routeParams.program_slug || null;
        var bucket = S3.getBucket();
        var url = 'https://s3-eu-west-1.amazonaws.com/riplive.it-podcast/';

        dataService.loadData({
            action: 'rip_programs_get_program_by_slug',
            slug: programSlug
        }).then(function(res) {
            $scope.program = res.data.program;
        });

        $scope.uploading = false;
        $scope.files = [];

        // Every time a file is selected is stored in $scope.files array.
        // Attacch a progress property on each file.
        $scope.onFileSelect = function($files) {
            $scope.uploading = true;
            for (var i = 0; i < $files.length; i++) {
                if ($files[i].type !== 'audio/mp3' && $files[i].type !== 'audio/mpeg') {
                    continue;
                }

                $files[i].progress = 0;

                $scope.files.push($files[i]);
            }
        };

        $scope.upload = function(file, index, event) {
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
            request.on('httpUploadProgress', function(e) {
                $scope.$apply(function() {
                    file.progress = parseInt(100.0 * e.loaded / e.total);

                    file.uploading = true;
                    // hide the progress bar.
                    if (file.progress === 100) {
                        file.computing = true;
                    }
                });
            });

            // If the upload to S3 is successfull that insert a record into the local db.
            request.on('complete', function(e) {
                if (e.error) {
                    file.computing = false;
                    file.error = true;
                    return false;
                } else {
                    podcastsService.getAllTags(file, function(tags) {
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
                        }).then(function(res) {
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

        $scope.remove = function(index) {
            $scope.files.splice(index, 1);
        };
    });

    /**
     * Pagination controller.
     * Create an array with the total number of pages and the relative links.
     */
    app.controller('PaginationCtrl', function($scope, $routeParams, dataService) {
        var programSlug = $routeParams.program_slug || null;

        dataService.loadData({
            action: 'rip_podcasts_get_podcasts_number_of_pages',
            slug: programSlug
        }).then(function(res) {
            $scope.pages = [];

            for (var i = 1; i <= res.data.number_of_pages.pages; i++) {
                $scope.pages.push({
                    page_number: i,
                    link: '#/podcasts/' + programSlug + '/page/' + i
                });
            }
        });
    });
</script>
var app = angular.module('adminPodcasts', [
    'ngRoute', 'angularFileUpload', 'ui.tinymce'
]);

/**
 * Routing configuration.
 * 
 * @param {object} param
 */
app.config(function ($routeProvider) {
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
























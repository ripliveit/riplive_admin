var app = angular.module('adminChart', ['ngRoute']);

app.config(function ($routeProvider) {
    $routeProvider.when('/', {
        controller: 'ListCtrl',
        templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/charts.html'
    }).when('/charts/:page', {
        controller: 'ListCtrl',
        templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/charts.html'
    }).when('/chart/:slug/:chart_archive_slug', {
        controller: 'UpdateCtrl',
        templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/chart.html'
    }).when('/new/:slug/positions/:position', {
        controller: 'CreateCtrl',
        templateUrl: '/wp-content/plugins/rip-charts/view-helpers/partials/angular-template/chart.html'
    }).otherwise({
        redirectTo: "/"
    });
});
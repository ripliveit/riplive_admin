app.directive('alert', function ($rootScope, $timeout) {
    return {
        restrisct: 'EA',
        scope: true,
        link: function link(scope, el, attrs) {
            var overlay = el.find('#overlay');
            var type = el.find('#alert-type');
            var placeholder = el.find('#alert-message');

            $rootScope.$on('alert:message', function (e, data) {
                placeholder.text(data.message);
                type.text(data.type);
                overlay.addClass('visible');
            });

            scope.$on('$routeChangeStart', function (next, current) {
                scope.closeAlert();
            });

            scope.closeAlert = function () {
                overlay.removeClass('visible');
            };
        }
    };
});
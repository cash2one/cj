angular.module('app.modules.setUp', ['ui.router', 'app.modules.api', 'ui.bootstrap', 'ng.poler.plugins.pc','ng.poler.plugins.cycpupload', 'ng.poler.plugins.submit', 'ng.poler.plugins.dateplugin'])
.config(['$stateProvider', '$urlRouterProvider',
    function($stateProvider, $urlRouterProvider) {
        $urlRouterProvider.otherwise('app/page/score/setup');
    }
]);

angular.module('app.modules.campaignsDatabase', ['ui.router', 'app.modules.api', 'ui.bootstrap','ng.poler.plugins.pc', 'ng.poler.plugins.submit'])
    .config(['$stateProvider', '$urlRouterProvider',
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('app/page/campaigns/database');
        }
    ]);
angular.module('app.modules.campaignsAdd', ['ui.router', 'app.modules.api', 'ui.bootstrap',
    'ng.poler.plugins.pc', 'ng.poler.plugins.submit','ng.poler.plugins.cycpupload','ng.poler.plugins.campaignsPlugins'])
    .config(['$stateProvider', '$urlRouterProvider',
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('app/page/campaigns/add');
        }
    ]);
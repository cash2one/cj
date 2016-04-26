angular.module('app.modules.campaignsList', ['ui.router', 'app.modules.api', 'ui.bootstrap', 'ng.poler.plugins.pc', 'ng.poler.plugins.submit', 'ng.poler.plugins.dateplugin','ng.poler.plugins.cycpupload','ng.poler.plugins.campaignsPlugins'])
    .config(['$stateProvider', '$urlRouterProvider',
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('app/page/campaigns/list');
        }
    ]);
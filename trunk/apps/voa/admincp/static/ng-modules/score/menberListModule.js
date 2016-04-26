angular.module('app.modules.memberList', ['ui.router', 'app.modules.api', 'ui.bootstrap', 'ng.poler.plugins.pc', 'ng.poler.plugins.submit', 'ng.poler.plugins.dateplugin'])
.config(['$stateProvider', '$urlRouterProvider',
    function($stateProvider, $urlRouterProvider) {
    	//alert(2);
        $urlRouterProvider.otherwise('app/page/score/menber_list');
    }
]);
//笔记列表

angular.module('app.modules.notelist', ['ui.router', 'app.modules.api', 'ui.bootstrap', 'ng.poler.plugins.pc', 'ng.poler.plugins.submit', 'ng.poler.plugins.dateplugin','ngSanitize'])
.config(['$stateProvider', '$urlRouterProvider',
    function($stateProvider, $urlRouterProvider) {

        $urlRouterProvider.otherwise('app/page/note/list');
    }
]);

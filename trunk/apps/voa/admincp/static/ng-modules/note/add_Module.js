//添加笔记

angular.module('app.modules.noteadd', ['ui.router', 'app.modules.api', 'ng.poler.plugins.pc'])
.config(['$stateProvider', '$urlRouterProvider',
    function($stateProvider, $urlRouterProvider) {
        $urlRouterProvider.otherwise('app/page/note/add');
    }
]);

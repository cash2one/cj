/**
 * Created by three on 15/12/18.
 */

/**
 * ng.poler.embed
 * angularjs 嵌入框架
 * 使angular作为一个组件库嵌入到原始js中使用
 */
(function (app) {

    app.run(['$q','$rootScope',function ($q,$rootScope) {
        $('body').on('click',function documentClickEvent(event) {
            $rootScope.$apply(function () {
                $rootScope.$broadcast('document:click',event);
            })
        });
    }]);

    app.controller('BaseCtrl', ['$scope','$rootScope','$document',function ($scope, $rootScope, $document) {

    }])

})(angular.module('ng.poler.embed',['ui.router','ng.poler','ui.bootstrap']).config([
    '$stateProvider', '$urlRouterProvider', 'RouterRoleProvider'/*由ng.poler提供的路由解析器*/,
    function ($stateProvider, $urlRouterProvider, RouterRoleProvider) {

        /*$locationProvider.html5Mode(true);
         $locationProvider.hashPrefix("#!");*/
        RouterRoleProvider.config({
            pathVar: 'funcPath',
            commonViews_404: 'app/components/error/views/404.html',
            commonViews_500: 'app/components/error/views/404.html',
            commonViews_502: 'app/components/error/views/404.html',
            componentsPath: '/admincp/static/ng-modules',
            viewPath: 'views',
            defaultFuncView: 'index'
        });
        $stateProvider
            .state('app', {
                abstract: true,
                controller:'BaseCtrl',
                url: '/app',
                templateUrl: '/admincp/static/ng-modules/ng.poler.embed/views/app-view.html'
            })
            .state('app.page', {
                url: '/page/{funcPath:.*}',
                templateUrl: RouterRoleProvider.parseFuncViewUrlFactory()
            })
            .state('app.module', {
                abstract: true,
                url: '/module',
                templateUrl: '/admincp/static/ng-modules/ng.poler.embed/views/app-view.html'
            })
        ;
    }
]));
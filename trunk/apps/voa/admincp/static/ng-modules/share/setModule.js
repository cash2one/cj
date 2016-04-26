/**
 * Created by three on 16/4/11. 魏世超
 */

angular.module('app.modules.shareSet', ['ui.router','app.modules.api','ui.bootstrap','ng.poler.plugins.pc','ng.poler.plugins.submit'])
    .config(['$stateProvider', '$urlRouterProvider',
        function ($stateProvider, $urlRouterProvider) {

            $urlRouterProvider.otherwise('app/page/share/set');
        }])
        /**
 * Created by Administrator on 2016/4/11.
 */

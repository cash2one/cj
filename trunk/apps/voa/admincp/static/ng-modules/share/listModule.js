/**
 * Created by three on 16/4/11. 魏世超
 */

angular.module('app.modules.shareList', ['ui.router','app.modules.api','ui.bootstrap','ng.poler.plugins.pc','ng.poler.plugins.submit','ng.poler.plugins.dateplugin','ngSanitize'])
    .config(['$stateProvider', '$urlRouterProvider',
        function ($stateProvider, $urlRouterProvider) {

            $urlRouterProvider.otherwise('app/page/share/list');
        }
    ]);

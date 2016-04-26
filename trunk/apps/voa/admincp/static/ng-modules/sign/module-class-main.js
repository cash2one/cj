/**
 * Created by three on 15/12/18.
 */

angular.module('app.modules.sign.class.main', ['ui.router','app.modules.api','ui.bootstrap','ng.poler.plugins.pc','ng.poler.plugins.submit','ng.poler.plugins.dateplugin'])
    .config(['$stateProvider', '$urlRouterProvider',
        function ($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('app/page/sign/sign-class-main')
        }])
;
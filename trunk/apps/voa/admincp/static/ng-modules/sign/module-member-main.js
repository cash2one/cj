/**
 * Created by three on 15/12/18.
 */

angular.module('app.modules.sign.member.main', ['ui.router','app.modules.api','ui.bootstrap','ng.poler.plugins.pc','ng.poler.plugins.map','ng.poler.plugins.submit','ng.poler.plugins.dateplugin'])
    .config(['$stateProvider', '$urlRouterProvider',
        function ($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('app/page/sign/sign-member-main')
        }])
;
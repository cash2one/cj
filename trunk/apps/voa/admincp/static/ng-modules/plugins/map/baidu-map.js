/**
 *
 * MAP插件 百度地图引入指令指令
 */
(function (app) {
    app.directive('baiduMap',['$parse',function ($parse) {
        return {
            restrict : 'A',
            templateUrl:'/admincp/static/ng-modules/plugins/map/baidu-map.html',
            link : function(scope, element, attrs) {
                scope.$on('$stateChangeStart',function(){
                    $('.tangram-suggestion-main').hide();
                });
            }
        }
    }]);
})(angular.module('ng.poler.plugins.map',[]));
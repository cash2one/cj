/**
 *
 * 日期插件 datepicker 分装指令
 */
(function (app) {
    app.directive('datePicker',['$parse',function ($parse) {
        return {
            restrict : 'A',
            link : function(scope, element, attrs) {
                element.datepicker({ dateFormat: 'yy-mm-dd'});
            }
        }
    }]).directive('timePicker',['$parse',function ($parse) {
        return {
            restrict : 'A',
            link : function(scope, element, attrs) {
                element.timepicker({
                    showMeridian: false
                });
            }
        }
    }]);
})(angular.module('ng.poler.plugins.dateplugin',[]));
/**
 * Created by three on 15/12/18.
 */
/**
 * ng.poler.embed.utils
 *
 * ng.poler.embed 提供常用组件
 */
(function (app) {

    /**
     * Dialog 弹框组件
     */
    app.factory('DialogTool',['$modal', function ($modal) {
        return {
            open: function (config) {
                config.controller = ['$scope', 'params', function ($scope, params) {
                    for(var k in params) {
                        $scope[k] = params[k]();
                    }
                }];
                var old_resolve = config.params;
                config.resolve = {
                    params: function(){
                        return old_resolve;
                    }
                };
                return $modal.open(config);
            }
        }
    }]);

    /**
     * Tips 消息提示框
     */
    app.factory('Tips', [function () {
        return {
            show: function (config) {
                alert(config.message);
            }
        }
    }]);

})(angular.module('ng.poler.embed'));
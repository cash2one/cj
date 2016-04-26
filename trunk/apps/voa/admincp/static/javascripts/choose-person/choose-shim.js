/**
 * Created by Administrator on 2015/11/4 0004.
 */
(function (app,window) {

    /**
     * 通过 Dom 元素启动angular
     *
     * @demo：
     * <link rel="stylesheet" href="css/ng.poler.plugins.pc.min.css">
     *  <script src="js/ng.poler.min.js"></script>
     *  <script src="js/ng.poler.plugins.pc.min.js"></script>
     *
     *  <div id='angular-area' data-ng-controller="ChooseShimCtrl">
     *      <h1>angularJs</h1>
     *      <button data-ng-click="selectPerson('s','selectedPersonCallBack')">选人！</button>
     *      <button data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选部门！</button>
     *  </div>
     *
     * <script>
     *      angular.bootstrap(document.getElementById('angular-area'),['ng.poler.plugins.pc']);
     * </script>
     */
    app.controller('ChooseShimCtrl', ['$scope', 'PersonChooser','DepartmentChooser', function ($scope, PersonChooser, DepartmentChooser) {
        /**
         * 解析用户的参数
         * @param paramName
         * @returns {{selected: Object}}
         */
        function calShimParams(paramName) {
            var shimParams = eval(paramName);
            var selected = [];
            var config = {
                singleSelect:false
            };
            if(config instanceof Array) {
                selected = shimParams;
            } else {
                for(var k in config) {
                    config[k] = shimParams.hasOwnProperty(k)?shimParams[k]:config[k];
                }
                selected = shimParams.hasOwnProperty('selected')?shimParams['selected']:[];
            }
            return {
                selected:selected,
                config: config
            };
        }
        /**
         * 选人ctrl
         */
        $scope.selectPerson = function(selectedPerson, callback, customParams) {
            var shimParams = calShimParams(selectedPerson);
            PersonChooser.choose(shimParams.selected,null,shimParams.config).result.then(function (data) {
                var fun = eval(callback);
                fun(data,customParams);
            });
        };
        /**
         * 选部门ctrl
         */
        $scope.selectDepartment = function(selectedDepartment, callback,customParams) {
            var shimParams = calShimParams(selectedDepartment);
            DepartmentChooser.choose(shimParams.selected,null,shimParams.config).result.then(function (data) {
                var fun = eval(callback);
                fun(data,customParams);
            });
        };
    }]);


    /**
     * 使用原始事件调用
     * @type {null}
     */
    var ShimApp = (function () {
        var app = null;
        return function () {
            if(!app) {
                app = angular.bootstrap(angular.element('<div></div>'),['ng.poler.plugins.pc']);
                //angular.injector(['ng', 'ng.poler.plugins.pc']);
            }
            return app;
        }
    })();

    window.shimChoosePerson = function (selectedPerson,config, callbackFun) {
        ShimApp().invoke(['$q', 'PersonChooser', function ($q, PersonChooser) {
            PersonChooser.choose(selectedPerson,null,config).result.then(function (data) {
                    callbackFun(data);
                });
        }]);
    };

    window.shimChooseDepartment = function (selectedDepartment,config, callbackFun) {
        ShimApp().invoke(['$q','DepartmentChooser', function ($q, DepartmentChooser) {
            DepartmentChooser.choose(selectedDepartment,null,config).result.then(function (data) {
                callbackFun(data);
            });
        }]);
    };


    window.advancedChoose = function(selecteds, config, callbackFun) {
        var cc = angular.bootstrap(angular.element('<div></div>'),['ng.poler.plugins.pc']);   //防止invoke报错  什么原因 莫名
        ShimApp().invoke(['AdvancedChooser', function(AdvancedChooser) {
            AdvancedChooser.choose(selecteds, null, config).result.then(function (data) {
                callbackFun(data);
            });
        }]);
    };

    /**
     * 配置接口url地址
     */
    window.ChooseApiConfig = function (conf) {
        ShimApp().invoke(['ApiUtil', function (ApiUtil) {
            ApiUtil.config({
                URL_PREFIX: conf.hostPath
            })
        }]);
    }
})(angular.module('ng.poler.plugins.pc'),window);
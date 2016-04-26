/** 许西 2016.02.29  设置主页 **/
(function(app){

    app.controller('SignSettingsMainCtrl',['$scope', 'Tips', 'SignApi', 'Page',function($scope, Tips, SignApi, Page){


        /**
         * 初始化
         */
        (function(){
            SignApi.confQuery({type : 4}).then(function(data){
                if(data.errcode == 0){
                    $scope.buttonSwitch = data.result.config;
                }else{
                    Tips.show({
                        message : data.errmsg
                    });
                }
            },function(){
                Tips.show({
                    message : '网络错误'
                });
            });

        })();


        /**
         *  todo 提交后锁定
         *
         * 数据开关
         * @param buttonText
         * @constructor
         */
        $scope.SwitchOperation = function(buttonText,buttonType){
            var value = $scope.buttonSwitch[buttonText];
            if(value){
                value = value == 1 ? 2 : 1;
                $scope.buttonSwitch[buttonText] = value;

                var params = {};
                params['type'] = buttonType;
                params[buttonText] = value;

                SignApi.updateSwith(params).then(function(data){
                    if(data.errcode == 0){

                    }else{
                        Tips.show({
                            message : data.errmsg
                        });
                    }
                },function(){
                    Tips.show({
                        message : '网络错误'
                    });
                });
            }
        }

    }]);
})(angular.module('app.modules.sign.settings'));
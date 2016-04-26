/** 许西 2016.02.30  设置微信 **/
(function(app){

    app.controller('SignSettingsWeixinCtrl',['$scope', 'Tips', 'SignApi', 'Page', '$q',function($scope, Tips, SignApi, Page, $q){


        $scope.number = ['一','二','三'];

        /**
         * 初始化
         */
        (function(){
            SignApi.confQuery({type : 3}).then(function(data){
                if(data.errcode == 0){
                    $scope.wxcpmenu = data.result.config.wxcpmenu;
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
         * 返回设置主页
         */
        $scope.returnPage = function(){
            Page.goPage('app/page/sign/sign-settings-main');
        };


        /**
         *
         *
         * 菜单保存
         * @param buttonText
         * @constructor
         */
        $scope.menuSave = function(){

            return $q(function (resolve, reject){
                SignApi.updateMenu({wxcpmenu : $scope.wxcpmenu}).then(function(data){
                    if(data.errcode == 0){
                        Tips.show({
                            message : '微信菜单设置保存成功'
                        });
                        Page.refreshState();
                    }else{
                        Tips.show({
                            message : data.errmsg
                        });
                        resolve({
                            flag : true
                        });
                    }
                },function(){
                    Tips.show({
                        message : '网络错误'
                    });
                    resolve({
                        flag : true
                    });
                });
            });


        }

    }]);
})(angular.module('app.modules.sign.settings'));
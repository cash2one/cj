/** 许西 2016.02.29  设置签到 **/
(function(app){

    /**
     * (正则)数字字符串匹配
     * @param type
     * @param text
     * @returns {*}
     * @private
     */
    function _regularNumber(type,text){
        var pattern = /[^\d]/g;
        if(text && type == 'number'){
            return text.replace(pattern,'');
        }
    }

    /**
     * 空值表单验证
     * @private
     */
    function _emptyValueCheck(Tips,text, element, name){
        if(!element[name]){
            Tips.show({
                message : '请输入正确的' + text
            });
            return true;
        }

    }

    app.controller('SignSetSignInCtrl',['$scope','Tips', 'SignApi', 'Page', '$q', function($scope,Tips, SignApi, Page, $q){

        /**
         * init
         */
        (function(){
            SignApi.confQuery({type : 1}).then(function(data){
                if(data.errcode == 0){
                    $scope.signIn = data.result.config;
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
         * 保存签到设置
         */
        $scope.signInSave = function(){


            var defer = $q.defer(); //构建承诺
            defer.resolve({
                flag : true
            });

            if($scope.signIn.sign_start_range < $scope.signIn.sign_remind_on_rage){
                Tips.show({
                    message : '迟到提醒不得大于签到提醒'
                });
                return defer.promise;
            }

            if(_emptyValueCheck(Tips, '签到时间范围', $scope.signIn, 'sign_start_range')){
                return defer.promise;
            }

            if(_emptyValueCheck(Tips, '签到时间范围', $scope.signIn, 'sign_start_range')){
                return defer.promise;
            }

            if(_emptyValueCheck(Tips, '迟到规则', $scope.signIn, 'sign_come_late_range')){
                return defer.promise;
            }
            if(_emptyValueCheck(Tips, '迟到提醒', $scope.signIn, 'sign_remind_on_rage')){
                return defer.promise;
            }
            if(_emptyValueCheck(Tips, '迟到提醒内容', $scope.signIn, 'sign_remind_on')){
                return defer.promise;
            }



            return $q(function (resolve, reject){

                SignApi.updateSignIn($scope.signIn).then(function(data){
                    if(data.errcode == 0){
                        Tips.show({
                            message : '签到设置保存成功'
                        });
                        Page.goPage('app/page/sign/sign-settings-main',{});
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


        };


        /**
         * 返回设置主页
         */
        $scope.returnPage = function(){
            Page.goPage('app/page/sign/sign-settings-main',{});
        };

        /**
         * 键盘输入检查数字合法性
         */
        $scope.checkValMax = function(name,min,max){
            if(/[\.]/gi.test(name)){
                if($scope[name.split('.')[0]][name.split('.')[1]] && $scope[name.split('.')[0]][name.split('.')[1]].length > 0
                    && /[^0-9\.]/gi.test($scope[name.split('.')[0]][name.split('.')[1]])){
                    console.log('验证其他字符');
                    $scope[name.split('.')[0]][name.split('.')[1]] = $scope[name.split('.')[0]][name.split('.')[1]].replace(/[^0-9\.]/gi,'');
                }
                if($scope[name.split('.')[0]][name.split('.')[1]] && $scope[name.split('.')[0]][name.split('.')[1]].length > 0){
                    if(Number($scope[name.split('.')[0]][name.split('.')[1]]) > max){
                        $scope[name.split('.')[0]][name.split('.')[1]] = max;
                    }
                    if(Number($scope[name.split('.')[0]][name.split('.')[1]]) < min){
                        $scope[name.split('.')[0]][name.split('.')[1]] = min;
                    }
                }
            }else{
                if($scope[name] && $scope[name].length > 0 && /[^0-9\.]/gi.test($scope[name])){
                    console.log('验证其他字符');
                    $scope[name] = $scope[name].replace(/[^0-9\.]/gi,'');
                }
                if($scope[name] && $scope[name].length > 0){
                    if(Number($scope[name]) > max){
                        $scope[name] = max;
                    }
                    if(Number($scope[name]) < min){
                        $scope[name] = min;
                    }
                }
            }
        };

    }]);
})(angular.module('app.modules.sign.settings'));
(function(app){
    app.controller('AddPropsDialogCtrl',['$scope','Tips','AddPropsDialogServer',function($scope,Tips,AddPropsDialogServer){

        /**
         * 初始化
         */
        (function(){

            $scope.tempField = JSON.parse(JSON.stringify(AddPropsDialogServer.tempField));
            var count = 0;
            if($scope.tempField != null){
                count = Object.getOwnPropertyNames($scope.tempField).length;
            }else{
                $scope.tempField = {}
            }

            if(count == 0){
                $scope.tempField['ext1'] = {
                    open : 0,
                    required : 0,
                    view : 0,
                    number : '',
                    level : 3,
                    fieldName : 'ext1'
                }
            }

        })();

        /**
         * 新增属性
         */
        $scope.createField = function(){
            var count = Object.getOwnPropertyNames($scope.tempField).length;
            if(count >= 10){
                Tips.show({
                    message:'最多10条自定义字段'
                });
                return ;
            }
            for(var i=1; i <= 10; i++){
                if(!$scope.tempField.hasOwnProperty('ext' + i)){
                    $scope.tempField['ext' + i] = {
                        open : 0,
                        required : 0,
                        view : 0,
                        number : '',
                        level : 3,
                        fieldName : 'ext' + i
                    };
                    return ;
                }
            }
        };

        /**
         * 去除属性
         */
        $scope.moveField = function(field){
            var count = Object.getOwnPropertyNames($scope.tempField).length;
            if(count <= 1){
                field['name'] = '';
                return ;
            }
            delete $scope.tempField[field.fieldName];
        }
    }]);



    app.factory('AddPropsDialogServer',[function(){
        return {}; //只用于返回数据
    }]);
})(angular.module('app.modules.member'));
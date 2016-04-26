/**
 *
 * 提交组件
 */
(function (app) {
    app.directive('repeatSubmit',['$parse','Tips',function ($parse, Tips) {
        return {
            restrict : 'A',
            link : function(scope, iEle, iAttrs) {
                var clickFun = $parse(iAttrs.repeatSubmit);
                var flag = true;
                iEle.bind('click', function(){
                    if(flag){
                        flag = false;
                        var promise = clickFun(scope); //执行方法返回的承诺

                        if(promise){
                            promise.then(function(data){ // flag:true才执行方法
                                if(data && data['flag']){
                                    flag = data['flag'];
                                }
                            });
                        }
                    }else{
                        Tips.show({
                            message : '请不要重复提交'
                        });
                    }
                });
            }
        }
    }]);
})(angular.module('ng.poler.plugins.submit',[]));
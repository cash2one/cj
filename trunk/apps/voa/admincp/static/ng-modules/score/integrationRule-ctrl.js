/**
 * Created by liangpure on 2016/4/15.
 */
(function(app){
    app.controller('integrationRuleCtrl',['$scope','$location', '$timeout' ,'Page', 'IntegralApi',function($scope,$location,$timeout,Page,IntegralApi){
        $scope.app_types = {};
        $scope.app_type='';
         $scope.listData = [];
        //保存修改
        $scope.rules = {
        };
        $scope.saveHint = '保存成功';
        $scope.saveRules = function(){
          //console.log($scope.rules);
            var rulesArr = [];
            function createArr(rule_id, obj){
                 var _obj = {};
                 for(var innerPro in obj){
                     _obj[innerPro] = obj[innerPro];
                 }
                _obj['rule_id'] = rule_id;
                rulesArr.push(_obj);
            }
           for(var prop in $scope.rules){
               //console.log(prop);
               createArr(prop, $scope.rules[prop]);
           }
            IntegralApi.postUpdateRules({
                rules: rulesArr
            }).then(function(data){
                if(data.errcode == 0){
                    console.log(data);
                    angular.element(document.getElementById('waiting-dot')).css('display', 'none');
                    //显示提示框
                    $scope.saveHint = '保存成功';
                    var element = angular.element(document.getElementById('tishi'));
                    element.css('display', 'block');
                    $timeout(function(){
                        element.css('display', 'none');
                    }, 3000);
                }
            }, function(error){
                console.log(error);
                angular.element(document.getElementById('waiting-dot')).css('display', 'none');
                //显示提示框
                $scope.saveHint = '保存失败，请再试一次';
                var element = angular.element(document.getElementById('tishi'));
                element.css('display', 'block');
                $timeout(function(){
                    element.css('display', 'none');
                }, 3000);
            });
            angular.element(document.getElementById('waiting-dot')).css('display', 'inline-block')
        };
        //获取列表
        var getRuleList = function(params){
            IntegralApi.getRuleList(params).then(function(data){
                if(data.errcode == 0){
                    console.log(data.result.list);
                    $scope.listData = data.result.list;
                    $scope.listData.forEach(function(item, num){
                        $scope.rules[item.rule_id] = {
                            score: item.score,
                            loop: item.loop,
                            limit: item.limit
                        };
                    });
                }
            }, function(error){
                console.log(error);
            });
        };
        getRuleList({app_type: ''});
        //监听app_type的值是否改变
        $scope.$watch('app_type', function(newVal, oldVal){
            if(newVal != oldVal){
                getRuleList({app_type: $scope.app_type});
                //console.log($scope.app_type);
            }
        });
        //禁用应用项目
        $scope.offApp = function(ev){
            //console.log(ev.target.nodeName.toLowerCase() !== 'a');
            if(ev.target.nodeName.toLowerCase() !== 'span')  return;
            console.log(ev.target.parentNode.dataset.ruleId , '   ', ev.target.dataset.status);
            IntegralApi.changeRuleStatus({
                rule_id: ev.target.parentNode.dataset.ruleId,
                status:  ev.target.dataset.status
            }).then(function(data){
                console.log(data);
                if(data.errcode == 0){
                     console.log(data);
                    getRuleList({app_type: $scope.app_type});
                }
            }, function(error){
                console.log(error);
                alert("网络错误,请重试");
            });
        };

    }])
})(angular.module('app.modules.setUp'));
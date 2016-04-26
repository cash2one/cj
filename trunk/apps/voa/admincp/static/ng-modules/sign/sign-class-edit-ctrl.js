/**
 * 新增班次，江潮，2016-03-03
 */
(function(app){
app.controller('SignClassEditCtrl',['$scope','$location','SignApi','DialogTool','Page','$q', function($scope,$location,SignApi,DialogTool,Page,$q){

    var classAddParams = { };
    var id = $location.search().id;
    classAddParams.sbid = id;
    var curType = '';
    //初始化 -- 赋值 -- 修改 -- 提交
    (function(){
        SignApi.classEditInit({
            'id': id,
            'type': 'edit'
        }).then(function(data){
            if(data.errcode == 0){
                //获取初始化时从类型判断
                // 如果是类型1
                // 只取1的值
                //如果是类型2
                //
                $scope.nameClass = data.result.name;
                curType = Number(data.result.type);
                if(data.result.type == 1){  //类型1，只取
                    $scope.defaultToggleRule(Number(data.result.rule));
                }else{  //类型2，取2的值，和全局规则
                    $scope.flexData = data.result;
                    $scope.flexData.come_late_range = $scope.flexData.come_late_range == 0 ? '' : $scope.flexData.come_late_range/60;
                    $scope.flexData.late_work_time_on = $scope.flexData.late_work_time_on == 1 ? true : false;
                    $scope.flexData.absenteeism_range_on = $scope.flexData.absenteeism_range_on == 1 ? true : false;
                    $scope.flexData.sign_on = $scope.flexData.sign_on == 1 ? true : false;
                    $scope.flexData.sign_off = $scope.flexData.sign_off == 1 ? true : false;
                    $scope.defaultToggleRule(1);
                }
                $scope.tabClassFn(Number(data.result.type));
            }else{
                alert(data.errmsg);
            }
        },function(error){
            alert(error);
        })
    })();

    /**
     * tab切换班次类型
     */
    $scope.isShow = false;
    $scope.tabClass = [
        {name: '常规班次',isActive:false},
        {name: '弹性班次',isActive:false}
    ];
    $scope.tabClassFn = function (num) {
        for(var i=0;i<$scope.tabClass.length;i++){
            $scope.tabClass[i].isActive = false;
        }
        $scope.isShow = true;
        $scope.tabClass[num-1].isActive = true;
        //$scope.classInit.type = num;  //班次类型 1:常规班次,2:弹性班次
        classAddParams.type = num;
    };


    /**
     * tab常规班次规则
     */
    function defaultClassRule(numRule){
        $scope.flexData = {};
        if(numRule == 1){
            $scope.isDefaultRule = true;
            $scope.isRuleDefault = true;
            $scope.isRuleFlex = false;
            $scope.defultData = {};
            SignApi.classEditInit({
                'id':id,
                'type': 'edit'
            }).then(function(data){
                if(data.errcode == 0){
                    //$scope.defultData = data.result;
                    $scope.defultData._str_work_begin = data.result._str_work_begin;
                    $scope.defultData._str_work_end = data.result._str_work_end;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            });
            SignApi.defult_rule().then(function(data){
                if(data.errcode == 0){
                    var oldRule = data.result.defult_rule;
                    $scope.defultData._str_sign_start_range = oldRule.sign_start_range;
                    $scope.defultData._str_sign_end_range = oldRule.sign_end_rage;
                    $scope.defultData.come_late_range = oldRule.sign_come_late_range;
                    $scope.defultData._str_leave_early_range = oldRule.sign_leave_early_range;
                    $scope.defultData.late_range = oldRule.sign_late_range;
                    $scope.defultData._str_remind_on_rage = oldRule.sign_remind_on_rage;
                    $scope.defultData.remind_on = oldRule.sign_remind_on;
                    $scope.defultData._str_remind_off_rage = oldRule.sign_remind_off_rage;
                    $scope.defultData.remind_off = oldRule.sign_remind_off;
                    $scope.defultData.late_range_on = true;
                    $scope.defultData.sign_on = true;
                    $scope.defultData.sign_off = true;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            });

        }else{
            $scope.isDefaultRule = false;
            $scope.isRuleDefault = false;
            $scope.isRuleFlex = true;
            SignApi.classEditInit({
                'id':id,
                'type': 'edit'
            }).then(function(data){
                if(data.errcode == 0){
                    $scope.defultData = data.result;
                    $scope.defultData.come_late_range = data.result.come_late_range/60;
                    $scope.defultData.late_range = data.result.late_range/60;
                    $scope.defultData.late_range_on = $scope.defultData.late_range_on == 1 ? true : false;
                    $scope.defultData.sign_on = $scope.defultData.sign_on == 1 ? true : false;
                    $scope.defultData.sign_off = $scope.defultData.sign_off == 1 ? true : false;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            });
        }
    }
    function flexClassRule(numType){
        if(numType == 1){
            $scope.isDefaultRule = true;
            $scope.isRuleDefault = true;
            $scope.isRuleFlex = false;
            $scope.defultData = {};
            SignApi.defult_rule().then(function(data){
                if(data.errcode == 0){
                    var oldRule = data.result.defult_rule;
                    $scope.defultData._str_sign_start_range = oldRule.sign_start_range;
                    $scope.defultData._str_sign_end_range = oldRule.sign_end_rage;
                    $scope.defultData.come_late_range = oldRule.sign_come_late_range;
                    $scope.defultData._str_leave_early_range = oldRule.sign_leave_early_range;
                    $scope.defultData.late_range = oldRule.sign_late_range;
                    $scope.defultData._str_remind_on_rage = oldRule.sign_remind_on_rage;
                    $scope.defultData.remind_on = oldRule.sign_remind_on;
                    $scope.defultData._str_remind_off_rage = oldRule.sign_remind_off_rage;
                    $scope.defultData.remind_off = oldRule.sign_remind_off;
                    $scope.defultData.late_range_on = true;
                    $scope.defultData.sign_on = true;
                    $scope.defultData.sign_off = true;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            });

        }else{
            $scope.isDefaultRule = false;
            $scope.isRuleDefault = false;
            $scope.isRuleFlex = true;
        }
    }
    $scope.isRuleDefault = false;
    $scope.isRuleFlex = false;
    $scope.defaultToggleRule = function (num) {
        classAddParams.rule = num;  //考勤规则 1默认规则，2自定义规则
        console.log(curType);
        if(curType == 1){
            defaultClassRule(num);
        }else{
            flexClassRule(num);
        }

    };


    /**
     * 单值检测赋值
     * @param checkData
     * @param endData
     */
    function onlyDataCheck(checkData,endData){
        if(String(checkData) || String(checkData).length > 0){
            classAddParams[endData] = String(checkData);
        }else{
            classAddParams[endData] = '';
        }
    }
    function onlyDataCheckStatus(status,statusData,checkData,endData){
        if(status){
            classAddParams[statusData] = '1';
            if(String(checkData) || String(checkData).length > 0){
                classAddParams[endData] = String(checkData);
            }else{
                classAddParams[endData] = '';
            }
        }else{
            classAddParams[statusData] = '0';
            delete classAddParams[endData];
        }
    }
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

    /**
     * 验证取值
     */
    function classAddDataCheck(){
        //检查班次名称
        onlyDataCheck($scope.nameClass, 'name');

        //检查班次类型
        if(classAddParams.type == 1){  //常规班次

            //上班时间
            if($scope.defultData._str_work_begin){
                if($scope.defultData._str_work_begin.length == 5){
                    /*
                    00:00  5
                    01 00:00 7
                    次日00:00 7
                    */
                    onlyDataCheck($scope.defultData._str_work_begin,'work_begin');
                }else{
                    onlyDataCheck($scope.defultData._str_work_begin.split(' ')[1],'work_begin');
                }
            }else{
                onlyDataCheck('','work_begin');
            }

            //下班时间
            if($scope.defultData._str_work_end){
                if($scope.defultData._str_work_end.length == 5){  //没改
                    onlyDataCheck($scope.defultData._str_work_end,'work_end');
                }else if($scope.defultData._str_work_end.length > 5){  //改了
                    if($scope.defultData._str_work_end.slice(0,2) == '次日'){
                        $scope.defultData._str_work_end = $scope.defultData._str_work_end;
                    }else{
                        if(new Date().getDate() != $scope.defultData._str_work_end.split(' ')[0]){
                            $scope.defultData._str_work_end = '次日'+ $scope.defultData._str_work_end.split(' ')[1];
                        }else{
                            $scope.defultData._str_work_end = $scope.defultData._str_work_end.split(' ')[1];
                        }
                    }
                    onlyDataCheck($scope.defultData._str_work_end,'work_end');
                }

            }else{
                onlyDataCheck('','work_end');
            }

            // 考勤时间范围--上班时间点前XX分钟开始签到
            onlyDataCheck($scope.defultData._str_sign_start_range,'sign_start_range');

            // 考勤时间范围--下班时间点后XX分钟结束签退
            onlyDataCheck($scope.defultData._str_sign_end_range,'sign_end_range');

            //迟到规则
            onlyDataCheck($scope.defultData.come_late_range,'come_late_range');

            //早退规则
            onlyDataCheck($scope.defultData._str_leave_early_range,'leave_early_range');

            //启用加班
            onlyDataCheckStatus($scope.defultData.late_range_on,'late_range_on',$scope.defultData.late_range,'late_range');

            //签到时间点前XX分钟提醒
            onlyDataCheckStatus($scope.defultData.sign_on,'sign_on',$scope.defultData._str_remind_on_rage,'remind_on_rage');

            //签到提醒
            onlyDataCheckStatus($scope.defultData.sign_on,'sign_on',$scope.defultData.remind_on,'remind_on');

            //签退时间点前XX分钟提醒
            onlyDataCheckStatus($scope.defultData.sign_off,'sign_off',$scope.defultData._str_remind_off_rage,'remind_off_rage');

            //签退提醒
            onlyDataCheckStatus($scope.defultData.sign_off,'sign_off',$scope.defultData.remind_off,'remind_off');

        }else{  //弹性班次

            delete classAddParams.rule;

            //上班时间
            if($scope.flexData._str_work_begin){
                if($scope.flexData._str_work_begin.length == 5){
                    onlyDataCheck($scope.flexData._str_work_begin,'work_begin');
                }else{
                    onlyDataCheck($scope.flexData._str_work_begin.split(' ')[1],'work_begin');
                }
            }else{
                onlyDataCheck('','work_begin');
            }

            //下班时间
            if($scope.flexData._str_work_end){
                if($scope.flexData._str_work_end.length == 5){  //没改
                    onlyDataCheck($scope.flexData._str_work_end,'work_end');
                }else if($scope.flexData._str_work_end.length > 5){  //改了
                    if($scope.flexData._str_work_end.slice(0,2) == '次日'){
                        $scope.flexData._str_work_end = $scope.flexData._str_work_end;
                    }else{
                        if(new Date().getDate() != $scope.flexData._str_work_end.split(' ')[0]){
                            $scope.flexData._str_work_end = '次日'+ $scope.flexData._str_work_end.split(' ')[1];
                        }else{
                            $scope.flexData._str_work_end = $scope.flexData._str_work_end.split(' ')[1];
                        }
                    }
                    onlyDataCheck($scope.flexData._str_work_end,'work_end');
                }
            }else{
                onlyDataCheck('','work_end');
            }

            //最小工作时长
            onlyDataCheck($scope.flexData.min_work_hours,'min_work_hours');

            //最晚上班时间
            if($scope.flexData.late_work_time_on){
                if($scope.flexData._str_late_work_time){
                    console.log('有值');
                    if($scope.flexData._str_late_work_time.length == 5){
                        onlyDataCheckStatus($scope.flexData.late_work_time_on,'late_work_time_on',
                            $scope.flexData._str_late_work_time,'late_work_time');
                    }else{
                        onlyDataCheckStatus($scope.flexData.late_work_time_on,'late_work_time_on',
                            $scope.flexData._str_late_work_time.split(' ')[1],'late_work_time');
                    }
                }
            }else{
                onlyDataCheckStatus($scope.flexData.late_work_time_on,'late_work_time_on',' ','late_work_time');
            }


            //迟到规则
            if($scope.flexData.late_work_time_on){
                onlyDataCheckStatus($scope.flexData.late_work_time_on,'come_late_range_on',$scope.flexData.come_late_range,'come_late_range');
            }else{
                onlyDataCheckStatus($scope.flexData.late_work_time_on,'come_late_range_on','','come_late_range');
            }

            //旷工规则
            onlyDataCheckStatus($scope.flexData.absenteeism_range_on,'absenteeism_range_on',$scope.flexData.absenteeism_range,'absenteeism_range');

            //签到时间点前XX分钟提醒
            onlyDataCheckStatus($scope.flexData.sign_on,'sign_on',$scope.flexData._str_remind_on_rage,'remind_on_rage');

            //签到提醒
            onlyDataCheckStatus($scope.flexData.sign_on,'sign_on',$scope.flexData.remind_on,'remind_on');

            //签退时间点前XX分钟提醒
            onlyDataCheckStatus($scope.flexData.sign_off,'sign_off',$scope.flexData._str_remind_off_rage,'remind_off_rage');

            //签退提醒
            onlyDataCheckStatus($scope.flexData.sign_off,'sign_off',$scope.flexData.remind_off,'remind_off');

        }
    }

    /**
     * 提交新增
     */
    var lackData = false;
    $scope.classAddSuccess = function () {

        var defer = $q.defer(); //构建承诺
        defer.resolve({
            flag : true
        });

        //取值
        classAddDataCheck();

        console.log(classAddParams);
        //验证
        for(var i in classAddParams){
            if(!classAddParams[i] && classAddParams[i].length == 0){
                lackData = true;
            }
        }
        if(lackData){
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-warn-dialog.html',
                params:{
                    warnTxt: function () {
                        return '请填写完整的必须信息!';
                    }
                }
            }).result.then(function ok() {
                    // 无确认按钮
                }, function () {
                    lackData = false;
                });
            return defer.promise;
        }
        
        //传递,是否有问题
        return $q(function (resolve, reject){
            SignApi.classUpdate(classAddParams).then(function (data) {
                if(data.errcode == 0){
                    DialogTool.open({
                        templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-warn-dialog.html',
                        params:{
                            warnTxt: function () {
                                return '修改成功';
                            }
                        }
                    }).result.then(function ok() {
                            // 无确认按钮
                        }, function () {
                            Page.goPage('app/page/sign/sign-class-main');
                            lackData = false;
                        });

                }else{
                    DialogTool.open({
                        templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-warn-dialog.html',
                        params:{
                            warnTxt: function () {
                                return data.errmsg;
                            }
                        }
                    }).result.then(function ok() {
                            // 无确认按钮
                        }, function () {
                            lackData = false;
                            resolve({
                                flag : true
                            });
                        });

                }
            }, function (error) {
                resolve({
                    flag : true
                });
                console.log(error);
            })
        });
    };

    /**
     * 取消编辑班次二次操作
     */
    $scope.classAddCancel = function () {
        DialogTool.open({
            templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-cancel-dialog.html'
        }).result.then(function(ok){
            Page.goPage('app/page/sign/sign-class-main');
        })
    }


}]);
})(angular.module('app.modules.sign.class.main'));
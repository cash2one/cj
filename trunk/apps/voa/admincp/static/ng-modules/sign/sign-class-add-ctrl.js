/**
 * 新增班次，江潮，2016-03-03
 */
(function(app){
app.controller('SignClassAddCtrl',['$scope','SignApi','DialogTool','Page','$q',function($scope,SignApi,DialogTool,Page,$q){

    var classAddParams = { };
    $scope.defult_rule = {};

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
        $scope.tabClass[num].isActive = true;
        classAddParams.type = num +1;  //班次类型 1:常规班次,2:弹性班次
        if(num == 0){  //获取全局默认规则
            SignApi.defult_rule().then(function(data){
                if(data.errcode == 0){
                    $scope.defult_rule = data.result.defult_rule;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            })
        }

    };
    $scope.tabClassFn(0);

    /**
     * tab常规班次规则
     */
    $scope.defaultToggleRule = function (num) {
        classAddParams.rule = num;  //考勤规则 1默认规则，2自定义规则
        if(num == 1){
            $scope.isDefaultRule = true;
            $scope.late_range_on = true;
            $scope.default_sign_on = true;
            $scope.default_sign_off = true;
            SignApi.defult_rule().then(function(data){
                if(data.errcode == 0){
                    $scope.defult_rule = data.result.defult_rule;
                }else{
                    alert(data.errmsg);
                }
            },function(error){
                alert(error);
            })
        }else{
            $scope.isDefaultRule = false;
        }
    };
    $scope.defaultToggleRule(1);

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
        onlyDataCheck($scope.nameClass , 'name');

        //检查班次类型
        if(classAddParams.type == 1){  //常规班次

            //上班时间
            if($scope.defaultInTime){
                onlyDataCheck($scope.defaultInTime.split(' ')[1],'work_begin');
            }else{
                onlyDataCheck('','work_begin');
            }

            //下班时间
            if($scope.defaultOutTime){
                if($scope.defaultOutTime.length == 5){
                    onlyDataCheck($scope.defaultOutTime,'work_end');
                }else if($scope.defaultOutTime.length > 5){
                    if($scope.defaultOutTime.slice(0,2) == '次日'){
                        onlyDataCheck($scope.defaultOutTime,'work_end');
                    }else{
                        if(new Date().getDate() != $scope.defaultOutTime.split(' ')[0]){
                            $scope.defaultOutTime = '次日'+ $scope.defaultOutTime.split(' ')[1];
                            onlyDataCheck($scope.defaultOutTime,'work_end');
                        }else{
                            $scope.defaultOutTime = $scope.defaultOutTime.split(' ')[1];
                            onlyDataCheck($scope.defaultOutTime,'work_end');
                        }
                    }
                }
            }else{
                onlyDataCheck('','work_end');
            }

            // 考勤时间范围--上班时间点前XX分钟开始签到
            onlyDataCheck($scope.defult_rule.sign_start_range,'sign_start_range');

            // 考勤时间范围--下班时间点后XX分钟结束签退
            onlyDataCheck($scope.defult_rule.sign_end_rage,'sign_end_range');

            //迟到规则
            onlyDataCheck($scope.defult_rule.sign_come_late_range,'come_late_range');

            //早退规则
            onlyDataCheck($scope.defult_rule.sign_leave_early_range,'leave_early_range');

            //启用加班
            onlyDataCheckStatus($scope.late_range_on,'late_range_on',$scope.defult_rule.sign_late_range,'late_range');

            //签到时间点前XX分钟提醒
            onlyDataCheckStatus($scope.default_sign_on,'sign_on',$scope.defult_rule.sign_remind_on_rage,'remind_on_rage');

            //签到提醒
            onlyDataCheckStatus($scope.default_sign_on,'sign_on',$scope.defult_rule.sign_remind_on,'remind_on');

            //签退时间点前XX分钟提醒
            onlyDataCheckStatus($scope.default_sign_off,'sign_off',$scope.defult_rule.sign_remind_off_rage,'remind_off_rage');

            //签退提醒
            onlyDataCheckStatus($scope.default_sign_off,'sign_off',$scope.defult_rule.sign_remind_off,'remind_off');

        }else{  //弹性班次

            delete classAddParams.rule;

            //上班时间
            if($scope.flexInTime){
                onlyDataCheck($scope.flexInTime.split(' ')[1],'work_begin');
            }else{
                onlyDataCheck('','work_begin');
            }

            //下班时间
            if($scope.flexOutTime){
                if($scope.flexOutTime.length == 5){
                    onlyDataCheck($scope.flexOutTime,'work_end');
                }else if($scope.flexOutTime.length > 5){
                    if($scope.flexOutTime.slice(0,2) == '次日'){
                        $scope.flexOutTime = $scope.flexOutTime;
                    }else{
                        if(new Date().getDate() != $scope.flexOutTime.split(' ')[0]){
                            $scope.flexOutTime = '次日'+ $scope.flexOutTime.split(' ')[1];
                        }else{
                            $scope.flexOutTime = $scope.flexOutTime.split(' ')[1];
                        }
                    }
                    onlyDataCheck($scope.flexOutTime,'work_end');
                }

            }else{
                onlyDataCheck('','work_end');
            }

            //最小工作时长
            onlyDataCheck($scope.min_work_hours,'min_work_hours');

            //最晚上班时间
            if($scope.late_work_time_on){
                if($scope.late_work_time){
                    onlyDataCheckStatus($scope.late_work_time_on,'late_work_time_on',
                        $scope.late_work_time.split(' ')[1],'late_work_time');
                }else{
                    onlyDataCheckStatus($scope.late_work_time_on,'late_work_time_on',' ','late_work_time');
                }
            }else{
                onlyDataCheckStatus($scope.late_work_time_on,'late_work_time_on',' ','late_work_time');
            }


            //迟到规则
            if($scope.late_work_time_on){
                onlyDataCheckStatus($scope.late_work_time_on,'come_late_range_on',$scope.come_late_range,'come_late_range');
            }else{
                onlyDataCheckStatus($scope.late_work_time_on,'come_late_range_on','','come_late_range');
            }

            //旷工规则
            onlyDataCheckStatus($scope.absenteeism_range_on,'absenteeism_range_on',$scope.absenteeism_range,'absenteeism_range');

            //签到时间点前XX分钟提醒
            onlyDataCheckStatus($scope.flex_sign_on,'sign_on',$scope.remind_on_rage,'remind_on_rage');

            //签到提醒
            onlyDataCheckStatus($scope.flex_sign_on,'sign_on',$scope.remind_on,'remind_on');

            //签退时间点前XX分钟提醒
            onlyDataCheckStatus($scope.flex_sign_off,'sign_off',$scope.remind_off_rage,'remind_off_rage');

            //签退提醒
            onlyDataCheckStatus($scope.flex_sign_off,'sign_off',$scope.remind_off,'remind_off');

        }
    }

    /**
     * 提交新增
     */
    var lackData = false;
    $scope.classAddSuccess = function () {
        console.log(classAddParams);

        var defer = $q.defer(); //构建承诺
        defer.resolve({
            flag : true
        });

        //取值
        classAddDataCheck();

        //验证
        for(var i in classAddParams){
            if(!classAddParams[i] && classAddParams[i].length == 0 ){
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
            SignApi.classAdd(classAddParams).then(function (data) {
                if(data.errcode == 0){
                    DialogTool.open({
                        templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-warn-dialog.html',
                        params:{
                            warnTxt: function () {
                                return '新增成功';
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
     * 取消新增班次二次操作
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
/** 许西 2016.03.04  排班编辑 **/
(function(app){

    app.controller('SignMemberEditCtrl',['$scope', 'Tips', 'SignApi', '$location','DepartmentChooser', 'DialogTool', 'Page', '$q', function($scope, Tips, SignApi, $location, DepartmentChooser, DialogTool, Page, $q){

        /**
         * 初始化周期规则
         */
        $scope.scheduling = {
            week: {
                type: 2,
                name: '周',
                value: _generateInitModule(['星期一','星期二','星期三','星期四','星期五','星期六','星期天'],'week_')
            },
            day: {
                type: 1,
                name: '天',
                value: _generateInitModule(['第一天','第二天','第三天','第四天','第五天','第六天','第七天'],'day_')
            },
            month: {
                type: 3,
                name: '月',
                value: _generateInitModule(_GenerateMonthContentArray(),'month_')
            }
        };

        $scope.selected = $scope.scheduling.week; //默认周期为'周'

        $scope.isOpen = false; // 范围开关

        $scope.datetime = {  //排除&新增日期
            addDate: {
                custom: []
            },
            legaDate: {
                custom: []
            }
        };

        $scope.id = $location.search().id;


        /**
         * 获取编辑信息
         */
        (function(){
            SignApi.getDetail({
                id:$scope.id,
            }).then(function(data){
                if(data.errcode == 0){
                    var edit = data.result.data;
                    console.log(edit);

                    $scope.type = edit.type;

                    /**
                     * 排班开始时间&结束时间
                     */
                    $scope.schedule_begin_time = edit._schedule_begin_time;
                    $scope.schedule_end_time = edit._schedule_end_time;

                    /**
                     * 部门
                     */
                    $scope.depList = edit.cd_info;

                    /**
                     * 定位相关
                     */
                    $scope.isOpen = edit.range_on  == 1 ? true : false ;
                    $scope.address_range = edit.address_range;

                    if($scope.isOpen){
                        setInfo(edit.latitude,edit.longitude,edit.address);
                    }

                    /**
                     * 判断周期类型
                     */
                    if(edit.cycle_unit == 1){
                        $scope.selected = $scope.scheduling.day; //默认周期为'天'
                        $scope.countDay = edit.cycle_num;
                    }else if(edit.cycle_unit == 2){
                        $scope.selected = $scope.scheduling.week; //默认周期为'周'
                    }else if(edit.cycle_unit == 3){
                        $scope.selected = $scope.scheduling.month; //默认周期为'月'
                    }

                    /**
                     * 日期
                     */
                    if(edit.remove_day){
                        $scope.datetime.legaDate.legal = edit.remove_day.public;
                        $scope.datetime.legaDate.custom = edit.remove_day.user;
                    }
                    $scope.datetime.addDate.custom = edit.add_work_day;

                    /**
                     * 还原数据
                     */
                    var i =0;
                    for(var k in $scope.selected.value){
                        var detail = edit.schedule_everyday_detail[i];

                        $scope.selected.value[k].selectedClass = _.filter(detail,function(c){


                            if(!!c){
                                c.sbid = c.id;
                                return true;
                            }
                        });
                        i++;
                    }

                    console.log('$scope.selected.value: ',$scope.selected.value);

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
         * 选择部门
         */
        $scope.depList = []; //初始化
        $scope.selectDepartment = function (event) {
            event.stopPropagation();
            DepartmentChooser.choose($scope.depList).result.then(function(data) {
                if(data){
                    $scope.depList = data;
                }
            })
        };

        /**
         * 删除部门搜索条件
         * @param dep
         */
        $scope.delDep = function (dep){
            $scope.depList.splice($scope.depList.indexOf(dep),1);
        };


        /**
         * 全选
         */
        $scope.checkedAll = function(){
            var now = $scope.selected;
            if (now == $scope.scheduling.week) {
                for (var i = 1; i <= 7; i++) {
                    now.value['week_' + i].checked = now['checkedAll'];
                }
                return;
            }
            if(now == $scope.scheduling.day){

                for (var i = 1; i <= 7; i++) {
                    now.value['day_' + i].checked = now['checkedAll'];
                }
                return ;
            }
            if(now == $scope.scheduling.month){
                $scope.monthCheckAll = !$scope.monthCheckAll;
                for (var i = 1; i <= 31; i++) {
                    now.value['month_' + i].checked = $scope.monthCheckAll;
                }
                return ;
            }
        };


        /**
         * 添加法定假日
         */
        $scope.addLegalHoliday = function(){

            SignApi.legalDates({}).then(function(data){
                if(data.errcode == 0){

                    $scope.datetime.legaDate.legal = _.map(data.result.legalYearDateList, function(date){
                        return {
                            startTime: date.startTime,
                            endTime: date.endTime
                        }
                    });

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
        };


        /**
         * 添加日期到指定数组
         * @param dateArray
         */
        $scope.addCustomDate = function(startName, endName, dateArray){
            console.log(dateArray);

            if($scope[startName] && $scope[endName]){
                dateArray.push({
                    startTime: $scope[startName],
                    endTime:$scope[endName]
                });
            }else{
                alert('请完整选择时间段');
            }
        };

        /**
         * 删除日期
         * @param date
         * @param dateList
         */
        $scope.delDate = function(date, dateList){
            dateList.splice(dateList.indexOf(date), 1);
        };

        /**
         * 查询考勤规则
         */
        $scope.selectClassDialog = function () {

            /** 查询选择的天数或者周期**/
            var selectedValue = _.filter($scope.selected.value,function(value){
                if(value['checked']){
                    return value['checked'];
                }
            });

            if(selectedValue.length <= 0){
                Tips.show({
                    message : '未勾选任何周期范围'
                });
                return ;
            }

            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/sign/views/sign-member-tpl/sign-member-selectclass-dialog.html',
                params:{

                }
            }).result.then(function(data){

                if(data){ //不是休息

                    /**
                     * 合并数组
                     */
                    var selectedClass = data;

                    /** 获取选择的排班**/
                    selectedClass = _.filter(selectedClass,function(value){
                        if(value['checked']){
                            return value;
                        }
                    });

                    /**
                     * 设置选择的班次 (班次)
                     */
                    _.map(selectedValue,function(value){
                        value.selectedClass = JSON.parse(JSON.stringify(selectedClass));
                    });

                }else{ //休息
                    /**
                     * 设置选择的班次 (休息)
                     */
                    _.map(selectedValue,function(value){
                        value.selectedClass = null;
                    });
                }

                /**
                 * 勾去选择
                 */
                _.map(selectedValue, function(obj){
                    obj.checked = false;
                });
                $scope.selected.checkedAll = false;

            });
        };



        /**
         * 保存排班设置
         */
        $scope.save = function(){

            var defer = $q.defer(); //构建承诺
            defer.resolve({
                flag : true
            });


            /** 表单验证 ----------------------- **/

            //部门
            if (!$scope.depList || $scope.depList.length == 0) {
                _emptyValueAlert(Tips,'请选择排班适用对象(选择部门)');
                return defer.promise;
            }

            //排班起止日期
            if (!$scope.schedule_begin_time) {
                _emptyValueAlert(Tips,'请选择正确的排班起止日期');
                return defer.promise;
            }

            //周期类型为 天
            if ($scope.selected == $scope.scheduling.day && !$scope.countDay) {
                _emptyValueAlert(Tips,'请输入周期天数');
                return defer.promise;
            }

            //考勤范围开关
            if($scope.isOpen){
                if(!$scope.address_range){
                    _emptyValueAlert(Tips,'请输入考勤地址范围');
                    return defer.promise;
                }
                if(!isPosition()){
                    _emptyValueAlert(Tips,'请选择考勤定位地址');
                    return defer.promise;
                }
            }




            /** 获取部门编号 */
            var cd_id = _.map($scope.depList, function(dep){
                return dep.id;
            });

            /**
             * 获取排班信息
             */
            var schedule_array =_.map($scope.selected.value, function(data){

                if(!data.selectedClass || data.selectedClass.length <= 0){
                    return [{
                        name: '休息',
                        type: 2
                    }];
                }else{
                    return _.map(data.selectedClass,function(selectClass){
                        return {
                            name: selectClass.name,
                            type: 1,
                            id: selectClass.sbid,
                            time: selectClass.work_begin + '-' + selectClass.work_end
                        }
                    });
                }
            });

            console.log($scope.selected.value);

            /**
             * 合并日期
             */
            var add_work_day = _.reduce($scope.datetime.addDate, function(memo,now){
                return memo.concat(now);
            });

            var params = {
                cd_id: cd_id, //部门编号,
                schedule_array: schedule_array, //排班设置
                schedule_begin_time: $scope.schedule_begin_time, //排班开始时间
                schedule_end_time: $scope.schedule_end_time, //排班结束时间
                cycle_unit: $scope.selected.type, //周期类型
                cycle_num: $scope.selected == $scope.scheduling.day ? $scope.countDay : '', //天
                remove_day: {
                    public: $scope.datetime.legaDate.legal,
                    user: $scope.datetime.legaDate.custom,
                }, //排除节假日数组
                add_work_day: add_work_day, //增加上班日期数组
                range_on: $scope.isOpen ? 1:0, //开关范围
                address_range: $scope.address_range, //地址范围
                address : getInfo().address, //地址
                longitude: getInfo().longitude, //精度
                latitude: getInfo().latitude, //纬度
                type: $scope.type,
                id: $scope.id
            };


            console.log('params',params);
            return $q(function (resolve, reject){
                SignApi.modifyScheduling(params).then(function(data){
                    if(data.errcode == 0){
                        console.log(data);
                        Tips.show({
                            message : '保存成功'
                        });
                        Page.goPage('app/page/sign/sign-member-main',{});
                    }else{
                        Tips.show({
                            message : data.errmsg
                        });
                        resolve({
                            flag : true
                        });
                    }
                });
            });

        };

        /**
         * todo 缺少提示
         * 返回取消页
         */
        $scope.returnPage = function(){
            Page.goPage('app/page/sign/sign-member-main',{});
        };

        /**
         * 键盘输入检查数字合法性
         * @param element
         * @param name
         */
        $scope.keyupCheck = function(name,max,min){
            $scope[name] = _regularNumber('number', $scope[name]);
            if((max && min ) && parseInt($scope[name]) > max || parseInt($scope[name]) < min){
                Tips.show({
                    message : '填入范围' + min + '至' + max + '之间'
                });
                $scope[name] = '';
            }
        }
    }]);

    /**
     * 空值表单验证提示
     * @private
     */
    function _emptyValueAlert(Tips,text){
        Tips.show({
            message : text
        });
    }

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
     * 初始化对象模型生成
     */
    function _generateInitModule(textArray, moduleName) {
        var obj = {};
        for (var i = 0; i < textArray.length; i++) {
            obj[moduleName + (i+1)] = {
                text: textArray[i]
            }
        }

        return obj;
    }

    /**
     * 生成月周期的数组
     * @private
     */
    function _GenerateMonthContentArray(){

        var array = [];
        for(var i = 1; i <= 31; i++){
            array[i-1] = i;
        }
        return array;
    }


})(angular.module('app.modules.sign.member.main'));
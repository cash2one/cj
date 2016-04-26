/** 许西 2016.03.03 班次选择弹窗 **/
(function(app){

    app.controller('SignMemberSelectclassDialogCtrl',['$scope', 'Tips', 'SignApi', 'DepartmentChooser', 'DialogTool',function($scope, Tips, SignApi, DepartmentChooser, DialogTool){

        var curPage = '';
        $scope.history = {
            list: []
        };

        $scope.selectedClassList = [];

        /**
         * 分页切换
         * @param page
         */
        $scope.getClassPage = function (page) {
            $scope.classList = [];
            var classQueryParams = {};
            classQueryParams.page = page;
            fetchClassList(classQueryParams);
        };

        /**
         * 顺序选择班次
         * @param clas
         */
        $scope.selectedClass = function(clas){

            if(clas.checked) {
                $scope.selectedClassList.push(clas);
            }else{
                $scope.selectedClassList.splice($scope.selectedClassList.indexOf(clas), 1);
            }
        };

        $scope.apply = function () {
            
            if(_beyondData($scope.selectedClassList)){
                $scope.$close($scope.selectedClassList);
            }else{
                alert("时间区域重叠");
            }

        };


        /**
         * 获取人员列表
         * 设置分页显示信息
         * @param params 查询参数
         */
        function fetchClassList(params) {
            var history = $scope.history;
            curPage = params.page; //当前页

            /**
             * 查询存在历史数据
             */
            if(history['list'] && history['list'][curPage] && history['list'][curPage].length != 0){
                $scope.classList = history['list'][curPage];
                console.log($scope.classList);
                // 分页
                $scope.polerPaginationCtrl.reset({
                    total:history.total,
                    pages:history.pages,
                    curPage:curPage
                });
            }else{
                SignApi.classList(params).then(function (data) {
                    if(data.errcode == 0){

                        history['list'][curPage] = data.result.list; //存入历史
                        history['total'] = data.result.count;
                        history['pages'] = data.result.pages;
                        history['curPage'] = params.page;

                        $scope.classList = history['list'][curPage];
                        // 分页
                        $scope.polerPaginationCtrl.reset({
                            total:data.result.count,
                            pages:data.result.pages,
                            curPage:params.page
                        });
                    }
                    if(data.errcode > 0){
                        alert(data.errmsg);
                    }
                }, function (error) {
                    console.log(error)
                });
            }
        }
        fetchClassList({page: 1});

    }]);

    /**
     * 日期比较
     * @param data
     * @returns {boolean}
     * @private
     */
    function _beyondData(data){
        for(var i = 0;i < data.length-1;i++){
            for(var j= 0;j<data.length;j++){
                if(i== j){
                    continue;
                }else{
                    console.log(data[i]);
                    console.log(data[j]);
                    console.log(_formatTime(data[i].created, data[i].work_end));
                    console.log(_formatTime(data[j].created, data[j].work_begin));
                    console.log(_formatTime(data[j].created, data[j].work_end));
                    console.log(_formatTime(data[i].created, data[i].work_begin));

                    console.log(Number(_formatTime(data[i].created, data[i].work_end)) > Number(_formatTime(data[j].created, data[j].work_begin)));
                    console.log(Number(_formatTime(data[j].created, data[j].work_end)) > Number(_formatTime(data[i].created, data[i].work_begin)));

                    if(Number(_formatTime(data[i].work_begin, data[i].work_end)) > Number(_formatTime(data[j].work_begin, data[j].work_begin)) && Number(_formatTime(data[j].work_begin, data[j].work_end)) > Number(_formatTime(data[i].work_begin, data[i].work_begin))){
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * 格式化特殊日期,转换成当日日期
     * @private
     */
    function _formatTime(oldTime, time){

        var oldTimestamp = oldTime;
        var oldNewDate = new Date();
        oldNewDate.setTime(oldTimestamp * 1000);

        var timestamp = time;
        var newDate = new Date();
        newDate.setTime(timestamp * 1000);


        var now = (parseInt(newDate.format('yyyyMMdd')) > parseInt(oldNewDate.format('yyyyMMdd')) && '1') || '0';

        return now + String(newDate.format('hhmmss'));
    }



    Date.prototype.format = function(format) {
        var date = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S+": this.getMilliseconds()
        };
        if (/(y+)/i.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        for (var k in date) {
            if (new RegExp("(" + k + ")").test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1
                    ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
            }
        }
        return format;
    }

})(angular.module('app.modules.sign.member.main'));
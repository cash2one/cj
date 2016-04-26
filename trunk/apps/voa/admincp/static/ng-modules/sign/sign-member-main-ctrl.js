/** 许西 2016.03.03  排班主页 **/
(function(app){

    app.controller('SignMemberMain',['$scope', 'Tips', 'SignApi', 'DepartmentChooser', 'Page', 'DialogTool',function($scope, Tips, SignApi, DepartmentChooser, Page, DialogTool){

        var curPage; //当前页面

        $scope.classStatusDesc = ['','已禁用','已启用','禁用中'];

        /**
         * 分切切换
         * @param page
         */
        $scope.getClassPage = function (page) {
            $scope.classList = [];
            var classQueryParams = {};
            classQueryParams.page = page;
            fetchMemberList(classQueryParams);
        };

        /**
         * 获取人员列表
         * 设置分页显示信息
         * @param params 查询参数
         */
        function fetchMemberList(params) {
            SignApi.queryMemberClass(params).then(function (data) {
                if(data.errcode == 0){
                    $scope.memberClassList = data.result.list;
                    // 分页
                    curPage = params.page;
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
            })
        }
        fetchMemberList({page: 1});


        /**
         * 搜索
         */
        $scope.searchMemberClass = function () {

            /** 获取部门编号 */
            var cd_id = _.map($scope.depList, function(dep){
                return dep.id;
            });

            fetchMemberList({
                cdids:  cd_id.join(),
                start_time: $scope.startDate,
                end_time: $scope.endDate,
                page: 1
            });
        };

        /**
         * 回车搜索
         */
        $scope.searchEnterDep = function (event) {
            if(event.keyCode == 13){
                $scope.searchMemberClass();
            }
        };



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
         * 启禁开关
         */
        $scope.controlStatus = function(memberClass){

            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/sign/views/sign-member-tpl/sign-member-forbid-dialog.html',
                params: {
                    enabled: function(){
                        return memberClass.enabled;
                    }
                }
            }).result.then(function(){

                var status = memberClass.enabled == 1 ? 2:1;

                SignApi.controlStatus({
                    id: memberClass.id,
                    enabled: status
                }).then(function(data){
                    if(data.errcode == 0){
                        memberClass.enabled = status;
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

            });
        };


        /**
         * 删除排班
         */
        $scope.delMemberClass = function(memberClass){

            if(memberClass.enabled == 2){
                Tips.show({
                    message : '排班状态启用中,禁止删除操作'
                });
                return ;
            }

            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/sign/views/sign-member-tpl/sign-member-deling-dialog.html'
            }).result.then(function(){

                SignApi.delMemberClass({
                    id: memberClass.id
                }).then(function(data){
                    if(data.errcode == 0){
                        Page.refreshState();
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
            });
        };


        $scope.editPage = function(memberClass){
            if(memberClass.enabled == 2){
                Tips.show({
                    message : '排班状态启用中,禁止编辑操作'
                });
                return ;
            }

            Page.goPage('app/page/sign/sign-member-edit',{
                id: memberClass.id
            });
        }

    }]);

})(angular.module('app.modules.sign.member.main'));
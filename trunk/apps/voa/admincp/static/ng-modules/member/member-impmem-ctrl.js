/**
 * Created by three on 15/12/18.
 */
/**
 * 通讯录模块
 */
(function (app) {
    app.controller('ImpmemCtrl', ['$scope','$q','MemberApi','DialogTool', function ($scope,$q,MemberApi,DialogTool) {

        /**
         * 初始化值
         * */
        $scope.isSearchDialog = false;
        $scope.memberList = [];

        /*
        * 搜索弹窗
        * */
        $scope.searchDialog = function () {
            $scope.isSearchDialog = !$scope.isSearchDialog;
        };

        /**
         * 添加部门
         * */
        $scope.addDepartment = function () {
            $scope.isSearchDialog = !$scope.isSearchDialog;
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-department-dialog.html'
            });
            instance.result.then(function ok() {
                alert("添加部门");
            }, function () {
                // 取消不任何操作
            })
        };

        /**
         * 添加员工
         * */
        $scope.addMember = function () {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-member-dialog.html'
            });
            instance.result.then(function ok() {
                alert("添加员工");
            }, function () {
                // 取消不任何操作
            })
        };

        /**
         * 添加部门或子部门
         * @param dep 父级部门，如果没有则为添加顶级部门
         */
        $scope.addChildDepartment = function (dep) {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-department-dialog.html'
            });
            instance.result.then(function ok(childDep) {
                console.log(arguments);
                alert("添加子部门: "+childDep+"   xxxxx ");
            }, function () {
                // 取消不任何操作
            })
        };

        /**
         * 部门编辑
         * */
        $scope.editDepartment = function () {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-department-dialog.html'
            });
            instance.result.then(function ok() {
                alert("编辑部门");
            }, function () {
                // 取消不任何操作
            })
        };

        /**
         * 删除部门
         * */
        $scope.delDepartment = function () {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-del-department-dialog.html'
            });
            instance.result.then(function ok() {
                alert("删除部门");
            }, function () {
                // 取消不任何操作
            })
        };

        function hideEditDepDialog(departmentList) {
            for(var dli=0; dli<departmentList.length; dli++) {
                var itDep = departmentList[dli];
                itDep.isDialog = false;

                if(itDep.childList) {
                    hideEditDepDialog(departmentList[dli].childList);
                }
            }
        }
        $scope.showPopup = function (dep,event) {
            hideEditDepDialog($scope.departmentList);
            dep.isDialog = true;

            event.stopPropagation();
        };

        $scope.hideEditDepDialog = function(){
            hideEditDepDialog($scope.departmentList);
        };

        $scope.toggleDep = function (dep) {
            dep.isOpen=!dep.isOpen;
            if(dep.isOpen && (!dep.childList || dep.childList.length==0)) {
                MemberApi.departmentList({cd_id:dep.id}).then(function (data) {
                    if(data.errcode == 0){
                        dep.childList = data.result.departments;

                    }
                })
            }

        };

        /**
         * 初始化页面
         * 调用所有部门，及默认总部门人员列表
         * todo 任何触发页面刷新的时候要重置页面，传参数的问题
         * */
        (function memberInit(){
            MemberApi.departmentList().then(function (data) {
                if(data.errcode == 0){
                    data.result.main_cdids.childList = data.result.departments;
                    $scope.departmentList = [data.result.main_cdids];
                    //$scope.departmentList = data.result.departments;
                    // 调用人员列表
                    fetchMemberList({
                        cd_id:$scope.departmentList[0].cd_id,
                        page:1
                    });
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function (error) {
                console.log(error);
            })
        })();

        /**
         * 显示部门人员
         * @param dep
         */
        $scope.getDepMemberList = function (dep) {
            fetchMemberList({
                cd_id:dep.id,
                page:1
            });
        };

        /**
         * 显示指定页人员数据
         * @param page
         */
        $scope.getMemberPage = function (page) {
            $scope.memberQueryParams.page = page;
            fetchMemberList($scope.memberQueryParams);
        };

        /**
         * 获取人员列表
         * 设置分页显示信息
         * @param params 查询参数
         */
        function fetchMemberList(params) {
            $scope.memberQueryParams = params;
            MemberApi.memberList(params).then(function (data) {
                if(data.errcode == 0){
                    $scope.memberList = data.result.list;
                    $scope.polerPaginationCtrl.reset({
                        // TODO 根据接口返回设置相应值
                        total:300,
                        pages:40,
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


    }]);
})(angular.module('app.modules.member'));
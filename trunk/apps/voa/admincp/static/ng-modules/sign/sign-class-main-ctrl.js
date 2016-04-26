/**
 * 考勤班次列表，江潮，2016-03-02
 */
(function(app){
    app.controller('SignClassMainCtrl',['$scope','SignApi','DialogTool','Page',function($scope,SignApi,DialogTool,Page){
        var curPage = '';

        /**
         * 分切切换
         * @param page
         */
        $scope.getClassPage = function (page) {
            $scope.classList = [];
            var classQueryParams = {};
            classQueryParams.page = page;
            fetchClassList(classQueryParams);
        };

        /**
         * 获取人员列表
         * 设置分页显示信息
         * @param params 查询参数
         */
        function fetchClassList(params) {
            SignApi.classList(params).then(function (data) {
                if(data.errcode == 0){
                    $scope.classList = data.result.list;
                    // 分页
                    curPage = params.page;
                    $scope.polerPaginationCtrl.reset({
                        total:data.result.count,
                        pages:data.result.pages,
                        curPage:params.page
                    });
                }else{
                    alert(data.errmsg);
                }
            }, function (error) {
                console.log(error)
            })
        }
        fetchClassList({page: 1});

        /**
         * 搜索部门
         */
        $scope.searchClass = function () {
            fetchClassList({name: $scope.searchName});
        };

        /**
         * 回车搜索部门
         */
        $scope.searchEnterDep = function (event) {
            if(event.keyCode == 13){
                $scope.searchClass();
            }
        };

        /**
         * 查询考勤规则
         */
        $scope.queryRule = function (dataClass) {
            if(dataClass.type == 1){  //常规
                DialogTool.open({
                    templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-rule-default-dialog.html',
                    params:{
                        classData: function () {
                            return dataClass;
                        }
                    }
                });
            }else{  //弹性
                DialogTool.open({
                    templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-rule-flex-dialog.html',
                    params:{
                        classData: function () {
                            return dataClass;
                        }
                    }
                });
            }
        };

        /**
         * 删除班次
         */
        $scope.classDel = function (delData) {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-main-del-dialog.html'
            }).result.then(function(ok){
                    SignApi.classDel({id: delData.sbid}).then(function(data){
                        if(data.errcode == 0){
                            DialogTool.open({
                                templateUrl:'/admincp/static/ng-modules/sign/views/sign-class-tpl/sign-class-add-warn-dialog.html',
                                params:{
                                    warnTxt: function () {
                                        return '操作成功';
                                    }
                                }
                            }).result.then(function ok() {
                                    // 无确认按钮
                                }, function () {
                                    Page.refreshState();
                                });
                            return false;
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

                                });
                            return false;
                        }
                    },function(error){
                        alert(error);
                    })
                }, function () {
                    // 取消不做任何操作
                });

        };

        /**
         * 编辑班次
         */
        $scope.classEdit = function (editData) {
            SignApi.classEditInit({
                'id': editData.sbid,
                'type': 'edit'
            }).then(function(data){
                if(data.errcode == 0){
                    Page.goPage('app/page/sign/sign-class-edit',{'id':editData.sbid});
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

                        });
                }
            },function(error){
                alert(error);
            })
        }

    }]);

})(angular.module('app.modules.sign.class.main'));
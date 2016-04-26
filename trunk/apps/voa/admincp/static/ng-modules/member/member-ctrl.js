/**
 * Created by three on 15/12/18.
 */
/**
 * 通讯录模块
 */
(function (app) {
    app.controller('MemberCtrl', ['$scope','$q','$window','$location','MemberApi','DialogTool','Tips','PersonChooser','DepartmentChooser','Page','warnDialog',function ($scope,$q,$window,$location,MemberApi,DialogTool,Tips,PersonChooser,DepartmentChooser,Page,warnDialog) {

        /**
         * 初始化值
         * */
        $scope.isMemberDetail = false;
        $scope.isMemberBatch = false;
        $scope.isSearchDialog = false;
        $scope.memberList = [];
        $scope.memList = [];
        $scope.mbList = [];
        var curPage = "";

        /**
         * 搜索弹窗
         * */
        $scope.searchDialog = function (event) {
            event.stopPropagation();
            clearDepDialog($scope.departmentList);
            $scope.isSearchDialog = !$scope.isSearchDialog;
        };

        /**
         * 添加员工
         * */
        $scope.addMember = function () {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-member-dialog.html'
            });
            instance.result.then(function ok() {
                Page.refreshState();
            }, function () {
                // 取消不任何操作
            })
        };

        /**
         * 修改员工
         * */
        $scope.updateMember = function (member) {
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-edit-member-dialog.html',
                params:{
                    member: function(){
                        return member;
                    }
                }
            });
            instance.result.then(function ok() {
                Page.refreshState();
            }, function () {
                // 取消不任何操作
            })
        };


        /**
         * 添加部门或子部门
         * @param dep 父级部门，如果没有则为添加顶级部门
         */
        $scope.addChildDepartment = function (dep) {
            $scope.isSearchDialog = false;
            clearDepDialog($scope.departmentList);
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-add-department-dialog.html',
                params:{
                    dep: function(){
                        return dep;
                    },
                    topId: function(){
                        return $scope.topId;
                    }
                }
            });
            instance .result.then(function ok() {
                memberInit();
            }, function () {
                // 取消不做任何操作
            })
        };

        /**
         * 编辑部门
         * @param dep 父级部门，如果没有则为添加顶级部门
         */
        $scope.editChildDepartment = function (dep) {
            clearDepDialog($scope.departmentList);
            var instance = DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-edit-department-dialog.html',
                params:{
                    dep:function(){
                        return dep;
                    }
                }
            });
            instance .result.then(function ok() {
                memberInit();
            }, function () {
                // 取消不做任何操作
            })
        };

        /**
         * 删除部门
         * */
        $scope.delDepartment = function (dep) {
            //先判断有没有人
            MemberApi.memberList({cd_id: dep.cd_id}).then(function (data) {
                if(data.errcode == 0){
                    if(data.result.list instanceof  Array && data.result.list.length>0){
                        DialogTool.open({
                            templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-notdel-department-dialog.html'
                        })
                            .result.then(function ok() {
                                // 确定不做任何操作
                            })

                    }
                    else {
                        //没人
                        DialogTool.open({
                            templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-del-department-dialog.html'
                        }).result.then(function ok() {
                                //再判断有没有部门
                                MemberApi.departmentDelete({cd_id: dep.cd_id}).then(function (data) {
                                    if(data.errcode == 0){
                                        //刷新页面
                                        memberInit();
                                    }
                                    if(data.errcode > 0){
                                        //有部门
                                        DialogTool.open({
                                            templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-notdel-department-dialog.html'
                                        }).result.then(function ok() {
                                                // 确定不做任何操作
                                            })
                                    }
                                }, function (error) {
                                    Tips.show({
                                        message: error
                                    });
                                });
                            }, function () {
                                // 取消不做任何操作
                            });

                    }
                }

                if(data.errcode > 0 && data.errcode != 190062){  // 有子部门
                    alert(data.errmsg);
                }
                if(data.errcode == 190062){
                    DialogTool.open({
                        templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/main-notdel-department-dialog.html'
                    }).result.then(function ok() {
                            // 确定不做任何操作
                        })
                }

            });
        };

        /**
         * 页面点击隐藏所有
         * @param departmentList
         */
        function clearDepDialog(departmentList) {
            eachTreeNode(departmentList, 'childList', function (dep) {
                dep.isDialog = false;
            });
        }

        function clearDepActive(departmentList) {
            eachTreeNode(departmentList, 'childList', function (dep) {
                dep.isActive = false;
            });
        }

        function eachTreeNode(nodeList, childField, fn) {
            if(nodeList instanceof Array){
                for(var dli=0; dli<nodeList.length; dli++) {
                    var itDep = nodeList[dli];
                    fn && fn(itDep);
                    if(itDep[childField]) {
                        eachTreeNode(itDep[childField], childField, fn);
                    }
                }
            }
        }

        $scope.showPopup = function (dep,event) {
            event.stopPropagation();
            clearDepDialog($scope.departmentList);
            $scope.isSearchDialog = false;
            dep.isDialog = true;
        };

        $scope.toggleDep = function (dep,event) {
            event.stopPropagation();
            clearDepActive($scope.departmentList);
            dep.isOpen=!dep.isOpen;
            dep.isActive = true;
            $scope.memList = [];
            $scope.isMemberBatch = false;
            $scope.searchMember = '';
            if(dep.isOpen && (!dep.childList || dep.childList.length==0)) {
                MemberApi.departmentList({cd_id:dep.cd_id}).then(function (data) {
                    if(data.errcode == 0){
                        dep.childList = data.result.departments;

                    }
                })
            }
            $scope.cd_id = dep.cd_id;
            fetchMemberList({
                cd_id:dep.cd_id,
                page:1
            });

        };

        /**
         * 初始化页面
         * 调用所有部门，及默认总部门人员列表
         * */
        function memberInit(){
            MemberApi.departmentList().then(function (data) {

                if(data.errcode == 0){
                    $scope.topId=data.result.main_cdids.cd_id;
                    data.result.main_cdids.childList = data.result.departments;
                    $scope.departmentList = [data.result.main_cdids];
                    $scope.departmentList[0].isOpen = true;
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
        }
        memberInit();

        /**
         * 显示部门人员
         * @param dep
         * @param event
         */
        $scope.getDepMemberList = function (dep,event) {
            event.stopPropagation();
            clearDepActive($scope.departmentList);
            $scope.memList = [];
            $scope.isMemberBatch = false;
            $scope.searchMember = '';
            dep.isActive = true;
            $scope.cd_id = dep.cd_id;
            fetchMemberList({
                cd_id:dep.cd_id,
                page:1
            });
        };

        /**
         * 显示指定页人员数据
         * @param page
         */
        $scope.getMemberPage = function (page) {
            $scope.memList = [];
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

        /**
         * 批量导出
         */
        $scope.export = function (){
            if($scope.memberList && $scope.memberList.length == 0){
                return false;
            }else{
                var str = '?';
                if($scope.searchMember != undefined){
                    str = str + 'search='+$scope.searchMember;
                }
                if($scope.cd_id != undefined && str.length > 1){
                    str = str + '&cd_id='+$scope.cd_id;
                }else if($scope.cd_id != undefined && str.length == 1){
                    str = str + 'cd_id='+$scope.cd_id;
                }
                if($scope.memStatus != undefined && str.length > 1){
                    str = str + '&status='+$scope.memStatus;
                }else if($scope.memStatus != undefined && str.length == 1){
                    str = str + 'status='+$scope.memStatus;
                }
                $window.location.href = "http://"+$location.host()+"/PubApi/Apicp/Member/Dump"+str;
            }

        };

        /**
         * 搜索部门
         */
        $scope.search = function () {
            MemberApi.departmentList({
                cd_name: $scope.searchDepartment
            }).then(function (data) {
                if(data.errcode == 0){
                    data.result.main_cdids.childList = data.result.departments;
                    $scope.departmentList = [data.result.main_cdids];
                    $scope.departmentList[0].isOpen = true;
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
            });
        };

        /**
         * 回车搜索部门
         */
        $scope.searchEnterDep = function (event) {
            if(event.keyCode == 13){
                $scope.search();
            }
        };

        /**
         * 搜索人员
         */
        $scope.searchMemberFn = function () {
            fetchMemberList({
                page: 1,
                kw: $scope.searchMember
            });
        };

        /**
         * 回车搜索人员
         */
        $scope.searchEnterMem = function (event) {
            if(event.keyCode == 13){
                $scope.searchMemberFn();
            }
        };

        /**
         * 关注状态
         */
        $scope.memberStatus = function (num) {
            $scope.memStatus = num;
            fetchMemberList({
                cd_id: $scope.cd_id,
                page: 1,
                status: num,
                kw: $scope.searchMember
            });
        };



        $scope.cancelMember = function(event,mem){
            event.stopPropagation();
            mem['ischecked'] = !mem['ischecked'];
            $scope.memList.splice($scope.memList.indexOf(mem),1);

        };

        $scope.closeMemberBatch = function () {
            $scope.isMemberBatch = false;
        };

        $scope.$on('document:click', function () {
            $scope.isMemberBatch = false;
            $scope.isMemberDetail = false;
            $scope.isSearchDialog = false;
            clearDepDialog($scope.departmentList);
        });

        /**
         * 人员详情点击不消失
         */
        $scope.clearClick = function (event) {
            event.stopPropagation();
        };


        //------------------------------------------------------------------------------------------------
        //-----------------------------------------我是华丽的分割线-----------------------------------------
        //------------------------------------------------------------------------------------------------

        /**
         * 全选
         * @param event
         */
        $scope.isAllChecked = false;
        $scope.allChecked = function(event){
            event.stopPropagation();
            $scope.memList = [];
            if($scope.isAllChecked){
                $scope.isMemberBatch = false;
                for(var i = 0; i<$scope.memberList.length; i++) {
                    $scope.memberList[i]['ischecked'] = false;
                }
            }else{
                $scope.isMemberBatch = true;
                for(var i = 0; i<$scope.memberList.length; i++) {
                    $scope.memberList[i]['ischecked'] = true;
                    $scope.memList.push($scope.memberList[i]);
                }

            }
            $scope.isAllChecked = !$scope.isAllChecked;
        };

        /**
         * 选择
         * @param event
         * @param mem
         */
        $scope.checkbox = function (event,mem) {
            event.stopPropagation();
            $scope.isMemberBatch = true;
            if(!mem['ischecked']){
                mem['ischecked'] = true;
            }else{
                mem['ischecked'] = !mem['ischecked'];
            }

            if(mem['ischecked']){
                $scope.memList.push(mem);
            }else{
                $scope.memList.splice($scope.memList.indexOf(mem),1);
            }

            if($scope.memList.length == 0) {
                $scope.isMemberBatch = false;
            }
        };


        /**
         * 查询详情
         */
        $scope.detail = function(event,member){
            event.stopPropagation();
            $scope.isMemberDetail = true;

            MemberApi.memberView({
                m_uid: member.m_uid
            }).then(function(data){
                if(data.errcode == 0 ) {
                    $scope.member = data.result.user_data;
                    $scope.memDetailCustom = data.result.custom;
                    $scope.browseUser = data.result.browse_user;
                    $scope.browseDep = data.result.browse_dep;
                }else{
                    Tips.show({
                        message: data.errmsg
                    });
                }
            },function(data){
                Tips.show({
                    message: data.errmsg
                });
            });
        };


        /**
         * 关闭详情
         */
        $scope.closeMemberDetail = function () {
            $scope.isMemberDetail = false;
        };

        /**
         * 删除人员
         */
        $scope.delMember = function (event,member) {
            event.stopPropagation();
            warnDialog('删除人员','确认删除人员？','',true,function(){
                memeberDel([member.m_uid]);
            });
            $scope.isMemberDetail = false;
        };

        /**
         * 批量删除人员
         */
        $scope.batchMemberDetail = function () {
            $scope.isMemberDetail = false;
            warnDialog('删除人员','确认删除人员？','',true,function(){
                memeberDel(_getArrayToAray($scope.memList,'m_uid'));
            });
        };
        function memeberDel(idList){
            MemberApi.memberDelete(idList).then(function(data){
                if(data.errcode == 0){
                    Page.refreshState();
                }else{
                    warnDialog('删除人员',data.errmsg,'',false,function(){});
                }
            },function(data){
                warnDialog('删除人员',data.errmsg,'',false,function(){});
            });
        }

        /**
         * 禁止和启用
         */
        $scope.toggleMember = function(member){
            fetchMemberList({
                cd_id:$scope.id,
                page:$scope.polerPaginationCtrl.paginationInfo().curPage
            });

            var params = {
                m_uid:member.m_uid,
                active:member.m_active == 1 ? 0:1
            };
            MemberApi.memberBan(params).then(function (data) {
                if(data.errcode == 0) {
                    warnDialog('禁止和启用人员','是否禁止或启用人员？','',true,function(){
                        $scope.isMemberDetail = false;
                        fetchMemberList({
                            cd_id:$scope.id,
                            page:$scope.polerPaginationCtrl.paginationInfo().curPage
                        });
                    });
                }else{
                    warnDialog('禁止和启用人员',data.errmsg,'',false,function(){});
                }
            },function(data){
                warnDialog('禁止和启用人员',data.errmsg,'',false,function(){});
            });
        };

        /**
         * 邀请人员
         */
        $scope.invitePost = function(member){
            inviteApi([member.m_uid]);
        };

        /**
         * 批量邀请人员
         */
        $scope.batInvitePost = function(){
            var tempMuid = [];
            for(var i=0; i<$scope.memList.length; i++) {
                tempMuid.push($scope.memList[i].m_uid);
            }
            inviteApi(tempMuid);
        };
        /**
         *
         *  todo 功能已经删除
         *
         * 浏览权限选人
         */
        $scope.selectLeader = function (event,member) {
            event.stopPropagation();
            PersonChooser.choose([]).result.then(function(data) {
                MemberApi.memberBrowse({
                    m_uid: [member.m_uid],
                    mb_m_uid:_getArrayToAray(data,'m_uid')
                }).then(function (data) {
                    if(data.errcode == 0){
                        memberInit();
                        $scope.memList = [];
                    }
                    if(data.errcode > 0){
                        alert(data.errmsg);
                    }
                },function (error) {
                    console.log(error);
                })

            })
        };

        /**
         * 移动人员
         */
        $scope.moveMem = function(){
            DepartmentChooser.choose([]).result.then(function(data) {
                if(data){
                    MemberApi.memberMove({
                        m_uid:_getArrayToAray($scope.memList,'m_uid'),
                        cd_id:_getArrayToAray(data,'id')
                    }).then(function(data){
                        if(data.errcode == 0){
                            warnDialog('移动人员','操作成功','',false,function(){
                                Page.refreshState();
                            });
                        }else{
                            warnDialog('移动人员',data.errmsg,'',false,function(){});
                        }
                    },function(data){
                        warnDialog('移动人员',data.errmsg,'',false,function(){});
                    });
                }
            })
        };

        function inviteApi(idArray){
            MemberApi.memberInvite( {
                m_uid:idArray
            }).then(function(data){
                if(data.errcode == 0){
                    Tips.show({
                        message:'操作成功'
                    });
                }else{
                    Tips.show({
                        message: data.errmsg
                    });
                }
            },function(data){
                Tips.show({
                    message: data.errmsg
                });
            });
        }

    }]);

    /**
     * 添加部门弹窗 controller
     */
    app.controller('AddDepartmentCtrl',['$scope','$q','MemberApi','DialogTool','PersonChooser','DepartmentChooser', function ($scope,$q,MemberApi,DialogTool,PersonChooser,DepartmentChooser) {
        $scope.ids = [];
        $scope.names = [];
        $scope.depIds = [];
        $scope.depNames = [];
        $scope.power = "0";
        $scope.order = 1;
        var permission_list = null;

        /**
         * 部门权限开关
         */
        if($scope.dep){
            MemberApi.departmentInit({cd_id:$scope.dep.cd_id}).then(function (data) {
                console.log(data);
                if(data.errcode == 0){
                    if(data.result.permission == '2'){
                        $scope.power = "2";
                        permission_list = JSON.parse(data.result.permission_list);
                        $scope.depIds = [];
                        $scope.depNames = [];
                        if(permission_list.length){
                            for(var i=0;i<permission_list.length;i++){
                                $scope.depNames.push(permission_list[i]);
                                $scope.depIds.push(permission_list[i].id);
                            }
                        }
                    }
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            });
        }

        /*
         *权限选择指定部门
         */
        $scope.selectchange = function(){
            if($scope.power == "2"){
                DepartmentChooser.choose($scope.depNames).result.then(function(data) {
                    if(data instanceof Array && data.length > 0){
                        $scope.depIds = [];
                        $scope.depNames = [];
                        for(var i=0;i<data.length;i++){
                            $scope.depIds.push(data[i].id);
                            $scope.depNames.push(data[i]);
                        }
                    }else{
                        alert("请选择指定部门");
                    }
                })
            }
        };

        $scope.deldelNames = function (event,num) {
            event.stopPropagation();
            $scope.depIds.splice(num,1);
            $scope.depNames.splice(num,1);
        };

        /**
         * 选人
         */
        $scope.selectLeader = function (event) {
            event.stopPropagation();
            PersonChooser.choose($scope.names).result.then(function(data) {
                for(var i=0;i<data.length;i++){
                    $scope.ids.push(data[i].m_uid);
                    $scope.names.push(data[i]);
                }
            })
        };
        $scope.delNames = function (num) {
            $scope.ids.splice(num,1);
            $scope.names.splice(num,1);
        };
        $scope.addapply = function () {
            if(!$scope.order){
                alert("排序号应为正整数");
                return false;
            }
            if($scope.dep){
                $scope.id = $scope.dep.cd_id;
            }
            else{
                $scope.id = $scope.topId;
            }
            if(!$scope.depNames.length > 0 && $scope.power == "2"){
                alert("指定部门不能为空");
                return false;
            }
            MemberApi.departmentAdd({
                cd_name: $scope.department,
                cd_connect: $scope.ids.join(','),
                cd_displayorder: $scope.order,
                cd_upid: $scope.id,
                cd_permission: $scope.power,
                permission_list: $scope.depIds.join(','),
                permission_cover: $scope.isDep ? 1 : 0
            }).then(function (data) {
                if(data.errcode == 0){
                    $scope.$close();
                }
                if(data.errcode > 0){

                    alert(data.errmsg);
                }

            }, function (error) {
                console.log(error);
            })
        };

    }]);
    /**
     * 编辑部门弹窗 controller
     */
    app.controller('EditDepartmentCtrl',['$scope','$q','MemberApi','DialogTool','PersonChooser','DepartmentChooser', function ($scope,$q,MemberApi,DialogTool,PersonChooser,DepartmentChooser) {
        $scope.ids = [];
        $scope.names = [];
        $scope.depIds = [];
        $scope.depNames = [];
        $scope.power = "0";
        $scope.order = 1;
        var cd_connect = null;
        var permission_list = null;


        // 编辑初始化人员接口
        if($scope.dep){
            MemberApi.departmentInit({cd_id:$scope.dep.cd_id}).then(function (data) {
                console.log(data);
                if(data.errcode == 0){
                    cd_connect = JSON.parse(data.result.cd_connect);
                    $scope.ids = [];
                    $scope.names = [];
                    if(cd_connect.length){
                        for(var i=0;i<cd_connect.length;i++){
                            $scope.names.push(cd_connect[i]);
                            $scope.ids.push(cd_connect[i].m_uid);
                        }
                    }
                    if(data.result.permission == '2'){
                        permission_list = JSON.parse(data.result.permission_list);
                        $scope.depIds = [];
                        $scope.depNames = [];
                        if(permission_list.length){
                            for(var i=0;i<permission_list.length;i++){
                                $scope.depNames.push(permission_list[i]);
                                $scope.depIds.push(permission_list[i].id);
                            }
                        }
                    }
                    console.log($scope.depNames);
                    console.log($scope.depIds);
                    console.log($scope.ids);
                    console.log($scope.names);
                    $scope.power =data.result.permission;
                    $scope.order=Number(data.result.cd_displayorder);
                    $scope.department = data.result.cd_name;
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            });
        }
        /**
         *权限选择指定部门
         */
        $scope.selectchange=function(){
            if($scope.power == 2){
                DepartmentChooser.choose($scope.depNames).result.then(function(data) {
                    if(data instanceof Array && data.length > 0){
                        $scope.depIds = [];
                        $scope.depNames = [];
                        for(var i=0;i<data.length;i++){
                            $scope.depIds.push(data[i].id);
                            $scope.depNames.push(data[i]);
                        }
                    }else{
                        alert("请选择指定部门");
                    }
                });

            }
        };
        $scope.deldelNames = function (event,num) {
            event.stopPropagation();
            $scope.depIds.splice(num,1);
            $scope.depNames.splice(num,1);
        };
        /**
         * 选人
         */
        $scope.selectLeader = function (event) {
            event.stopPropagation();
            PersonChooser.choose($scope.names).result.then(function(data) {
                $scope.ids = [];
                $scope.names = [];
                for(var i=0;i<data.length;i++){
                    $scope.ids.push(data[i].m_uid);
                    $scope.names.push(data[i]);
                }
            })
        };
        $scope.delNames = function (num) {
            $scope.ids.splice(num,1);
            $scope.names.splice(num,1);
        };

        $scope.apply = function () {
            if(!$scope.department){
                alert("部门名不能为空");
                return false;
            }
            if(!$scope.order){
                alert("排序号应为正整数");
                return false;
            }
            if(!$scope.depNames.length > 0 && $scope.power == "2"){
                alert("指定部门不能为空");
                return false;
            }
            MemberApi.departmentAdd({
                cd_name: $scope.department,
                cd_permission: $scope.power,
                cd_displayorder: $scope.order,
                cd_connect: $scope.ids.join(','),
                cd_id: $scope.dep.cd_id,
                permission_list: $scope.depIds.join(','),
                permission_cover: $scope.isDep ? 1 : 0
            }).then(function (data) {
                if(data.errcode == 0){
                    $scope.$close();
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function (error) {
                console.log(error);
            })
        };

    }]);

    /**
     * 标签对象比较器
     * @param diffName 对象属性比较名
     * @returns {Function}
     * @private
     */
    function _diffTag(diffName) {
        return function(fristTag, secondTag){
            if(fristTag[diffName] >= secondTag[diffName]){
                return 1;
            }
            if (fristTag[diffName] < secondTag[diffName]){
                return -1;
            }
            return 0;
        }
    }

    /**
     * 数组排序对象
     * @param tagArray
     * @param diffFun
     * @returns {*}
     * @private
     */
    function _sortArray (tagArray,diffFun){
        if(!tagArray){
            return [];
        }
        return tagArray.sort(diffFun);
    }

    /**
     * 返回对象 属性值 列表
     * @param obj
     * @returns {Array}
     * @private
     */
    function _values(obj) {
        var list = [];
        for(var k in obj){
            obj[k].fieldName = k; //保存原始变量名
            list.push(obj[k])
        }
        return list;
    }

    /**
     * 人员验证工具
     * @returns {{empty: empty}}
     */
    function checkInfo(Tips){
        return {
            /**
             * 数组提交信息验证
             * @param array
             * @returns {boolean}
             */
            empty: function(array){
                for(var i = 0; i < array.length; i++) {
                    if(array[i].open == 1 && array[i].required == 1 && !array[i].value){
                        Tips.show({
                            message: array[i].name + '不能为空'
                        });
                        return false ;
                    }
                }
                return true;
            },
            /**
             * 对象提交信息验证
             * @param obj
             * @returns {boolean}
             */
            emptyForObj: function(obj){
                for(var i in obj) {
                    if(obj[i].required == 1 && !obj[i].value){
                        Tips.show({
                            message: obj[i].name + '不能为空'
                        });
                        return false ;
                    }
                }
                return true;
            },
            mainEmpty: function(fixed,nameArray){
                var coust = 0;
                var message = '';
                for(var i = 0; i < nameArray.length; i++) {
                    message = message + fixed[nameArray[i]].name +" ";
                    if(fixed[nameArray[i]].value){
                        coust++;
                    }
                }
                if(coust <= 0){
                    Tips.show({
                        message: message + '不能同时为空!'
                    });
                    return false;
                }
                return true;
            },
            /**
             * 验证对象属性值
             * @param checkObj 待验证对象
             * @param checkValue 待验证的值名称
             * @param falgName 判断依据字段
             * @param falgValue 判断依据值
             * @returns {boolean}
             */
            editEmpty: function(checkObj,checkValue,falgName,falgValue){
                for(var k in checkObj) {
                    if(checkObj[k][falgName] == falgValue && checkObj[k][checkValue] == null){
                        Tips.show({
                            message: checkObj[k].name + '不能同时为空!'
                        });
                        return false;
                    }
                }

                return true;
            }
        }
    }

    function _isRepeatSubmit(){
        return {
            _submitFlag: false,
            /**
             * 设置
             */
            setSubmit : function(){
                this._submitFlag = true;
            },
            /**
             * 还原
             */
            restoreSubmit : function(){
                this._submitFlag = false;
            },
            /**
             * 判断提示
             * @param Tips
             * @returns {boolean}
             */
            isSubmitFlag: function(Tips){
                if(this._submitFlag){
                    Tips.show({
                        message: '请不要重复提交'
                    });
                }
                return this._submitFlag;
            }

        }
    }

    /**
     * 添加员工弹窗 controller
     */
    app.controller('AddMemberCtrl',['$scope','$q','MemberApi','DialogTool','PersonChooser','DepartmentChooser','Tips','Page','warnDialog',
        function ($scope,$q,MemberApi,DialogTool,PersonChooser,DepartmentChooser,Tips,Page,warnDialog) {

            /**
             * 人员属性 init
             */
            (function(){
                MemberApi.attrIndex().then(function(data){
                    console.log('MemberApi.attrIndex———————————',data);
                    if(data.errcode == 0){
                        var fixed = data.result.field.fixed;
                        var custom = data.result.field.custom;

                        $scope.fixed = {
                            fixedArray : _sortArray(_values(fixed), _diffTag('number')),
                            customArray : _sortArray(_values(custom), _diffTag('number'))
                        };

                        for(var i = 0; i < $scope.fixed.fixedArray.length; i++){
                            $scope.fixed[$scope.fixed.fixedArray[i].fieldName] = $scope.fixed.fixedArray[i];
                        }

                        for(var i = 0; i < $scope.fixed.customArray.length; i++ ){
                            if('leader' == $scope.fixed.customArray[i].fieldName){
                                $scope.fixed['leader'] = $scope.fixed.customArray[i];
                                $scope.isOpenleader = $scope.fixed['leader'].open;
                                break;
                            }
                        }

                        _.map($scope.fixed.customArray,function (fixed){
                            if(fixed.fieldName == 'birthday' ||
                                fixed.fieldName == 'entrytime' ||
                                fixed.fieldName == 'confirmation' ||
                                fixed.fieldName == 'departure' ){
                                fixed['type'] = 'date';
                            }
                        });

                        console.log($scope.fixed);
                    }else{
                        Tips.show({
                            message: data.errmsg
                        });
                    }
                },function(data){
                    Tips.show({
                        message: data.errmsg
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
             * 选人
             */
            $scope.memList = []; //初始化
            $scope.selectLeader = function (event) {
                event.stopPropagation();
                PersonChooser.choose($scope.memList).result.then(function(data) {
                    $scope.memList = data;
                })
            };


            /**
             * 删除某一部门
             * @param dep
             */
            $scope.clearDep = function (dep){
                $scope.depList.splice($scope.depList.indexOf(dep), 1);
            };

            /**
             * 删除某一选人
             * @param mem
             */
            $scope.clearMem = function (mem){
                $scope.memList.splice($scope.memList.indexOf(mem), 1);
            };

            /**
             * 过过滤字符
             */
            $scope.inputFilterChar = function (name){

                var value = $scope.fixed[name].value;
                var patt = /[\W\s]/g;
                $scope.fixed[name].value = value.replace(patt, "");


            }

            /**
             * 新人员信息提交
             */
            $scope.apply = function () {
                var fixed = $scope.fixed;
                if($scope.depList.length != 0){
                    var depId = [];
                    for(var i = 0; i<$scope.depList.length; i++){
                        depId.push($scope.depList[i].id);
                    }
                    fixed['department'].value = depId;
                }


                if($scope.memList.length != 0){
                    var uid = [];
                    for(var i = 0; i<$scope.memList.length; i++){
                        uid.push($scope.memList[i].m_uid);
                    }
                    fixed['leader'].value = uid;
                }

                if(!checkInfo(Tips).empty(fixed.fixedArray)){ //空判断
                    return false;
                }

                if(!checkInfo(Tips).mainEmpty(fixed,['mobile','weixinid','email'])){ //空判断
                    return false;
                }

                if(!checkInfo(Tips).empty(fixed.customArray)){ //空判断
                    return false;
                }

                var info = {};
                for(var i = 0; i<fixed.fixedArray.length; i++){
                    info[fixed.fixedArray[i].fieldName] = fixed.fixedArray[i].value;
                }
                for(var i = 0; i<fixed.customArray.length; i++){
                    info[fixed.customArray[i].fieldName] = fixed.customArray[i].value;
                }


                console.log('submit info :',info);
                MemberApi.memberAdd(info).then(function(data){
                    if(data.errcode == 0){
                        warnDialog('添加人员','人员添加成功','',false,function(){
                            $scope.$close();
                        });
                    }else{
                        warnDialog('添加人员',data.errmsg,'',false,function(){});
                    }
                },function(data){
                    warnDialog('添加人员',data.errmsg,'',false,function(){});
                })
            };

        }]);


    /**
     * 复制旧对象值,为新对象作属性
     * @param obj 待被设置的对象
     * @param oldObj 待复制的对象
     * @param valueName 待复制对象的属性值名称
     * @returns {*} 返回已被设置的新对象
     * @private
     */
    function _callToObject(obj, oldObj, valueName){
        for(var k in oldObj) {
            obj[k] = oldObj[k][valueName];
        }
        return obj;
    }

    /**
     * 遍历数组复制数组元素对象的属性值
     * @param oldArray 待操作的数组
     * @param valueName 操作数组元素对象的属性值名称
     * @returns {Array}  返回新的对角象值数组
     * @private
     */
    function _getArrayToAray(oldArray,valueName){
        var tempArray = [];
        if(oldArray){
            for(var i = 0; i<oldArray.length; i++){
                tempArray.push(oldArray[i][valueName])
            }
        }
        return tempArray;
    }

    /**
     * 修改员工弹窗 controller
     */
    app.controller('editMemberCtrl',['$scope','$q','MemberApi','DialogTool','PersonChooser','DepartmentChooser','Tips','Page','warnDialog',
        function ($scope,$q,MemberApi,DialogTool,PersonChooser,DepartmentChooser,Tips,Page,warnDialog) {

            /**
             * 人员属性 init
             */
            (function(){
                MemberApi.getMemberEditInfo({
                    m_uid:$scope.member.m_uid
                }).then(function(data){
                    console.log('MemberApi.memberEdit———————————',data);
                    if(data.errcode == 0){

                        $scope.mem = data.result;


                        console.log('mem',$scope.mem);
                        /*   _.map($scope.fixed.customArray,function (fixed){

                         });
                         */
                        for(var i in $scope.mem.fixed){
                            if(i == 'department'){
                                $scope.depList = $scope.mem.fixed[i].value;
                            }
                            if(i == 'leader'){
                                $scope.memList = $scope.mem.fixed[i].value;
                            }
                        }
                        for(var i in $scope.mem.custom){
                            if(i == 'birthday' ||
                                i == 'entrytime' ||
                                i == 'confirmation' ||
                                i == 'departure' ){
                                $scope.mem.custom[i]['type'] = 'date';
                                if($scope.mem.custom[i] != null && $scope.mem.custom[i].value.substring(0,4) == '0000'){
                                    $scope.mem.custom[i].value = '';
                                }
                            }
                        }
                    }else{
                        Tips.show({
                            message: data.errmsg
                        });
                    }
                },function(data){
                    Tips.show({
                        message: data.errmsg
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
             * 选人
             */
            $scope.memList = []; //初始化
            $scope.selectLeader = function (event) {
                event.stopPropagation();
                PersonChooser.choose($scope.memList).result.then(function(data) {
                    $scope.memList = data;
                })
            };

            /**
             * 删除某一部门
             * @param dep
             */
            $scope.clearDep = function (dep){
                $scope.depList.splice($scope.depList.indexOf(dep), 1);
            };

            /**
             * 删除某一选人
             * @param mem
             */
            $scope.clearMem = function (mem){
                $scope.memList.splice($scope.memList.indexOf(mem), 1);
            };

            /**
             * 信息提交
             */
            var applyFlag = _isRepeatSubmit();
            $scope.apply = function () {
                //初始化提交数据
                var newMemInfo = {
                    m_uid:$scope.member.m_uid
                };

                if(!checkInfo(Tips).emptyForObj($scope.mem.fixed)){ //提交验证
                    return ;
                }

                if(!checkInfo(Tips).mainEmpty($scope.mem.fixed,['mobile','weixinid','email'])){ //空判断
                    return;
                }

                if(!checkInfo(Tips).emptyForObj($scope.mem.custom)){ //提交验证
                    return ;
                }

                //便利对象
                $scope.mem.fixed['department'].value = _getArrayToAray($scope.depList,'id');
                $scope.mem.fixed['leader'].value = _getArrayToAray($scope.memList,'m_uid');

                //遍历对象,赋值到新的对象中
                newMemInfo = _callToObject(newMemInfo,$scope.mem.fixed,'value');
                newMemInfo = _callToObject(newMemInfo,$scope.mem.custom,'value');

                applyFlag = true;
                MemberApi.memberEdit(newMemInfo).then(function(data){
                    if(data.errcode == 0){
                        warnDialog('修改人员','人员修改成功','',false,function(){
                            $scope.$close();
                        });

                    }else{
                        warnDialog('修改人员',data.errmsg,'',false,function(){});
                    }
                },function(data){
                    warnDialog('修改人员',data.errmsg,'',false,function(){});
                })
            };
        }]);


    //姓名长度截取过滤器
    app.filter('ignoreLength', function () {
        return function (text,length) {
            if(text.length > length){
                text =  text.substring(0,length) + '...';
            }
            return text;
        };
    });

})(angular.module('app.modules.member'));
(function (app) {
    app.controller('MemberTagCtrl',['$scope','DialogTool','MemberApi', 'PersonChooser','Tips','warnDialog',function ($scope,DialogTool,MemberApi,PersonChooser,Tips,warnDialog) {

        //----------------------- 标签 --------------------

        /**
         * title的显示和隐藏
         */
        $scope.isTitle = false;
        $scope.showTitle = function (event) {
            event.stopPropagation();
            $scope.isTitle = !$scope.isTitle;

        };

        /**
         * 页面点击隐藏所有
         * @param tagList
         */
        function clearDepDialog(tagList) {
            eachTreeNode(tagList,function (tag) {
                tag.isDialog = false;
            });
        }
        function clearDepActive(tagList) {
            eachTreeNode(tagList,function (tag) {
                tag.isActive = false;
            });
        }
        function eachTreeNode(nodeList,fn) {

            if(nodeList){
                for(var dli=0; dli<nodeList.length; dli++) {
                    var itTag = nodeList[dli];
                    fn && fn(itTag);
                    if(itTag) {
                        eachTreeNode(itTag,fn);
                    }
                }
            }
        }
        $scope.showPopup = function (tag,event) {
            clearDepDialog($scope.tagList);
            tag.isDialog = true;
            event.stopPropagation();
        };
        $scope.toggleTag = function (tag,event) {
            event.stopPropagation();
            clearDepActive($scope.tagList);
            tag.isActive = true;
            $scope.isAll = false;
            $scope.memBatchList = [];
            $scope.cd_id = tag.laid;
            fetchMemberList({
                laid:tag.laid,
                page:1
            });
        };
        $scope.$on('document:click', function () {
            clearDepDialog($scope.tagList);
            $scope.isMemDetail = false;
            $scope.isTitle = false;
        });

        /**
         * 初始化
         */
        function init(tagName){
            MemberApi.labelList({name: tagName}).then(function (data) {
                if(data.errcode == 0){
                    if(data.result.list.length > 0){
                        $scope.tagList = data.result.list;
                        $scope.tagList[0].isActive = true;
                    }else{
                        $scope.tagList = '';
                        $scope.memberList = [];
                    }
                    if($scope.tagList){

                        $scope.cd_id = data.result.list[0].laid;
                        // 调用人员列表
                        fetchMemberList({
                            laid: data.result.list[0].laid,
                            page: 1
                        });
                    }
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            })
        }
        init();

        /**
         * 显示指定页人员数据
         * @param page
         */
        $scope.getMemberPage = function (page) {
            $scope.memberQueryParams.page = page;
            fetchMemberList($scope.memberQueryParams);
        };

        /**
         * 刷新标签人员
         */
        function fetchMemberList(params) {
            $scope.memberQueryParams = params;
            MemberApi.labelListMember(params).then(function (data) {
                if(data.errcode == 0){
                    $scope.memberAllList = data.result.all_list;
                    $scope.memberList = data.result.mem_list;
                    // 分页
                    $scope.polerPaginationCtrl.reset({
                        total:data.result.total,
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
         * 添加标签
         */
        $scope.addTag = function () {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/tag-tpl/add-tag-dialog.html'
            }).result.then(function ok() {
                    init();
                });
        };

        /**
         * 编辑标签
         * @param tag 当前标签
         */
        $scope.editTag = function (tag) {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/tag-tpl/edit-tag-dialog.html',
                params:{
                    tag: function () {
                        return tag;
                    }
                }
            }).result.then(function ok() {
                    init();
                });
        };

        /**
         * 删除标签
         */
        $scope.delTag = function (tag) {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/tag-tpl/del-tag-dialog.html'
            }).result.then(function ok() {
                    MemberApi.labelDelete({laid: tag.laid}).then(function (data) {
                        if(data.errcode == 0){
                            init();
                        }
                        if(data.errcode > 0){
                            alert(data.errmsg);
                        }
                    });

                });
        };

        /**
         * 搜索标签
         */
        $scope.search = function () {
            init($scope.searchTag);
        };

        /**
         * 回车搜索标签的方法
         */
        $scope.searchEnterTag = function (event) {
            if(event.keyCode == 13){
                $scope.search();
            }
        };

        //----------------------- 人员 --------------------

        /**
         * 搜索人员
         */
        $scope.searchMemberFn = function () {
            fetchMemberList({
                laid: $scope.cd_id,
                m_username: $scope.searchMember
            });
        };

        /**
         * 回车搜索人员的方法
         */
        $scope.searchEnterMem = function (event) {
            if(event.keyCode == 13){
                $scope.searchMemberFn();
            }
        };

        /**
         * 添加人员
         */
        $scope.addMemberList = [];
        $scope.addMember = function () {
            if(!$scope.tagList || $scope.tagList.length == 0) {
                Tips.show({
                    message:'请先添加标签并选择标签后操作'
                });
                return ;
            }

            PersonChooser.choose($scope.memberAllList).result.then(function(data) {
                $scope.isAll = false;
                $scope.addMemberList = [];
                for(var i=0;i<data.length;i++){
                    $scope.addMemberList.push(data[i].m_uid);
                }
                MemberApi.labelAddMember({
                    laid: $scope.cd_id,
                    m_uid: $scope.addMemberList.join(',').replace(/,$/gi,'')
                }).then(function (data) {
                    fetchMemberList({
                        laid: $scope.cd_id,
                        page: 1
                    });
                });
            });
        };

        /**
         * 人员详情
         */
        $scope.clearClick = function (event) {
            event.stopPropagation();
        };
        $scope.MemDetail = function (event, list) {
            $scope.isTitle = false;
            event.stopPropagation();
            $scope.memDetailData = {};
            $scope.isMemDetail = true;
            //$scope.memDetailData.m_uid = list.m_uid;
            MemberApi.memberView({m_uid:list.m_uid}).then(function (data) {
                if(data.errcode == 0){
                    $scope.memDetailData = data.result.user_data;
                    $scope.memDetailCustom = data.result.custom;
                }else{
                    alert(data.errmsg);
                }

            })

        };

        $scope.closeMemberDetail = function (event) {
            event.stopPropagation();
            $scope.isMemDetail = false;
        };

        $scope.delMemDetail = function (event) {
            event.stopPropagation();
            warnDialog('删除人员','确认删除人员？','',true,function(){
                MemberApi.labelDeleteMem({
                    laid: $scope.cd_id,
                    m_uid: $scope.memDetailData.m_uid
                }).then(function (data) {
                    if(data.errcode == 0){
                        $scope.memDetailData = {};
                        $scope.isMemDetail = false;
                        fetchMemberList({
                            laid: $scope.cd_id,
                            page: 1
                        });
                    }else{
                        warnDialog('删除人员',data.errmsg,'',false,function(){});
                    }
                })
            });

        };

        /**
         * 人员批量操作
         */
        $scope.isAll = false;
        function eachIsActive (result){
            for(var i=0;i<$scope.memberList.length;i++){
                $scope.memberList[i].isActive = result;
            }
        }
        $scope.memBatchList = [];
        $scope.selectAll = function () {  // 选择所有
            $scope.isAll = !$scope.isAll;
            if($scope.isAll){
                eachIsActive($scope.isAll);
                $scope.memBatchList = [];
                for(var i=0;i<$scope.memberList.length;i++){
                    $scope.memBatchList.push($scope.memberList[i]);
                }
            }else{
                eachIsActive($scope.isAll);
                $scope.memBatchList = [];
            }
        };
        function eachMemBatchIsActive(){  //遍历所有的人员进行判断是不是全选
            var num = 0;
            $scope.memBatchList = [];
            for(var i=0;i<$scope.memberList.length;i++){
                if($scope.memberList[i].isActive){
                    num ++;
                    $scope.memBatchList.push($scope.memberList[i]);
                }
            }
            $scope.isAll = (num == $scope.memberList.length) ? true : false;
        }
        $scope.MemBatch = function (event,mem,index) {  //单个操作用来显示是否勾选
            $scope.isTitle = false;
            $scope.isMemDetail = false;
            event.stopPropagation();
            mem.isActive = !mem.isActive;
            if(mem.isActive){
                $scope.memberList[index].isActive = true;
            }else{
                $scope.memberList[index].isActive = false;
            }
            eachMemBatchIsActive();
            console.log($scope.memBatchList);
        };
        $scope.closeMemberBatch = function (event) {  //关闭批量界面
            event.stopPropagation();
            $scope.memBatchList = [];
            $scope.isAll = false;
            for(var i=0;i<$scope.memberList.length;i++){
                $scope.memberList[i].isActive = false;
            }
        };
        $scope.batchClose = function (num) {  //删除单个人
            $scope.memBatchList.splice(num,1);
            console.log($scope.memBatchList);
            if($scope.memBatchList.length == 0 || $scope.memBatchList.length < $scope.memberList.length){
                $scope.isAll = false;
            }
            for(var i=0;i<$scope.memberList.length;i++){
                $scope.memberList[i].isActive = false;
                for(var j=0;j<$scope.memBatchList.length;j++){
                    if($scope.memberList[i].m_uid == $scope.memBatchList[j].m_uid){
                        $scope.memberList[i].isActive = true;
                    }
                }
            }
        };
        var delId = [];
        $scope.delBatch = function () {  //批量移除标签
            delId = [];
            for(var i=0;i<$scope.memBatchList.length;i++){
                delId.push($scope.memBatchList[i].m_uid);
            }
            MemberApi.labelDeleteMem({
                laid: $scope.cd_id,
                m_uid: delId.join(',')
            }).then(function (data) {
                if(data.errcode == 0){
                    warnDialog('删除人员','确认删除人员？','',true,function(){
                        $scope.memBatchList = [];
                        fetchMemberList({
                            laid: $scope.cd_id,
                            page: 1
                        });
                    });
                }
                if(data.errcode > 0){
                    warnDialog('删除人员',data.errmsg,'',false,function(){});
                }
            })
        }
        

    }]);

    /**
     * controller 添加标签
     */
    app.controller("AddTagCtrl",['$scope','MemberApi', function ($scope,MemberApi){
        $scope.apply = function () {
            if(!$scope.tagName){
                alert("标签名不能为空");
                return false;
            }
            MemberApi.labelAdd({
                name: $scope.tagName,
                displayorder: $scope.order
            }).then(function (data) {
                if(data.errcode == 0){
                    $scope.$close();
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function (error) {
                alert(error);
            })

        }

    }]);

    /**
     * controller 编辑标签
     */
    app.controller("EditTagCtrl",['$scope','MemberApi', function ($scope, MemberApi) {
        if($scope.tag){
            MemberApi.labelInit({laid: $scope.tag.laid}).then(function (data) {
                if(data.errcode == 0){
                    console.log(data);
                    $scope.tagName = data.result.name;
                    $scope.order = Number(data.result.displayorder);
                    $scope.id = data.result.laid;
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            });
        }
        $scope.apply = function () {
            if(!$scope.tagName){
                alert("标签名不能为空");
                return false;
            }
            MemberApi.labelEdit({
                laid: $scope.id,
                name: $scope.tagName,
                displayorder: $scope.order
            }).then(function (data) {
                if(data.errcode == 0){
                    $scope.$close();
                }
                if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function (error) {
                alert(error);
            })

        }
    }]);


})(angular.module('app.modules.member'));
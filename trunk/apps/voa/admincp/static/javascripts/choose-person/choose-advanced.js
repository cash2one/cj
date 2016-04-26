/**
 * Created by Administrator on 2016/3/3 0003.
 */

(function (app) {

    app.controller('AdvancedChooseCtrl',['$scope', '$timeout', 'ChoosePersonService', 'app_config', 'TagsService',
        function($scope, $timeout, ChoosePersonService, app_config, TagsService) {

            /**
             * 默认赋值
             */

            (function(){
                var params = $scope.params;

                console.log(params);

                var temp_params = {
                    person: {
                        isShow: (params.person && params.person.isShow) || false,
                        isSingle: (params.person && params.person.isSingle) || false,
                        isPerson: (params.person && params.person.isPerson) || false,
                    },
                    department: {
                        isShow: (params.department && params.department.isShow) || false,
                        isSingle: (params.department && params.department.isSingle) || false,
                        isDepartment: (params.department && params.department.isDepartment) || false,
                    },
                    tag: {
                        isShow: (params.tag && params.tag.isShow) || false,
                        isSingle: (params.tag && params.tag.isSingle) || false,
                        isTags: (params.tag && params.tag.isTags) || false,
                    }
                };
                $scope.params = temp_params;
            })();

            $scope.setShowWhat = {
                person: {
                    isShow: $scope.params.person.isShow
                },
                department: {
                    isShow: $scope.params.department.isShow
                },
                tag: {
                    isShow: $scope.params.tag.isShow
                }
            }

            /**
             * TAB选项切换
             * @param index
             */
            $scope.tabChange = function(index) {
                if( index == 1) {
                    $scope.setShowWhat.person.isShow = true;
                    $scope.setShowWhat.department.isShow = false;
                    $scope.setShowWhat.tag.isShow = false;
                }
                else if( index == 2 ) {
                    $scope.setShowWhat.department.isShow = false;
                    $scope.setShowWhat.person.isShow = false;
                    $scope.setShowWhat.tag.isShow = true;
                }
                else if( index == 3 ) {
                    $scope.setShowWhat.person.isShow = false;
                    $scope.setShowWhat.department.isShow = true;
                    $scope.setShowWhat.tag.isShow = false;
                }
            };


           /***********************************************选人**************************************************/
           (function() {

               $scope.allDep = {};
               $scope.noMembers = false;
               $scope.depQuery = { };
               $scope.isSearchDep = false;
               $scope.selectedMembers = [];
               $scope.memberQuery = {
                   page:1,
                   limit:35
               };
               var depTree = [];


               /**
                *  初始化
                */
               var init = function initFun(){
                   $scope.departmentList = ChoosePersonService.department({}).then(function (data) {
                       if(app_config.API.CHECK_SUCCESS(data)) {
                           depTree = $scope.departmentList = data.result.departments;
                           for(var index in data.result.departments) {
                               var dep = data.result.departments[index];
                               dep.noChild = false;
                               if(dep.id == 0) { // 全部人员做为单独部门处理
                                   dep.noChild = true;
                               }
                               $scope.allDep[dep.id] = dep;
                           }
                           if($scope.departmentList.length>0) {
                               $scope.memberQuery.cd_id = $scope.departmentList[0].id;
                           }
                           refreshMember();
                       }
                   }, function (error) {
                       console.log("选人组件.请求部分信息错误", error)
                   });
                   if(angular.isArray($scope.selectedList.selectedPersons)) {
                       $scope.selectedMembers = $scope.selectedList.selectedPersons;
                       for(var i in $scope.selectedMembers) {
                           $scope.selectedMembers[i].selected = true;
                       }
                   }
               };

               /**
                * 显示隐藏子部门
                * @param id
                */
               $scope.toggleDep = function (id) {
                   // 已经加载过数据，不再加载
                   var curDep = $scope.allDep[id];
                   curDep.isOpen = !curDep.isOpen;

                   // 检查是否需要请求
                   if((curDep.childDep && curDep.childDep.length>0) || curDep.noChild) {
                       return;
                   }

                   $scope.depQuery.cd_id = id;
                   ChoosePersonService.department($scope.depQuery).then(function (data) {
                       if(app_config.API.CHECK_SUCCESS(data)) {
                           var departments = data.result['departments'];
                           if(departments.length>0) {
                               for(var index in departments) {
                                   var dep = departments[index];
                                   dep.noChild = false;
                                   $scope.allDep[dep.id] = dep;
                               }

                               curDep.childDep = departments;
                           } else {
                               curDep.noChild = true;
                           }
                       }
                   }, function (error) {
                       console.log("选人组件.请求部分信息错误", error)
                   });
               };

               /**
                * enter键搜索
                * @param event
                */
               $scope.checkSearchDep = function (event) {
                   if(!$scope.depQuery.keyword) {
                       // 没有搜索内容
                       return;
                   }
                   if(event.keyCode==13) { // 输入enter键
                       ChoosePersonService.department($scope.depQuery).then(function (data) {
                           if(app_config.API.CHECK_SUCCESS(data)) {
                               $scope.departmentList = data.result.departments;
                               for(var i in $scope.departmentList) {
                                   $scope.departmentList[i].noChild = true;
                               }
                           }
                           $scope.isSearchDep = true;
                       }, function (error) {
                           console.log("选人组件.请求部分信息错误", error)
                       });

                   }
               };


               /**
                * 取消搜索部门
                */
               $scope.cancelSearchDep = function () {
                   $scope.departmentList = depTree;
                   $scope.depQuery.keyword = null;
                   $scope.isSearchDep = false;
               };

               var refreshMember = function () {
                   $scope.members = ChoosePersonService.member($scope.memberQuery).then(function (data) {
                       $scope.members = data.result.members;
                       $scope.hasMore = $scope.members.length<data.result.total;
                       for(var index in data.result.members) {
                           for(var j in $scope.selectedMembers) {
                               if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                   data.result.members[index].selected = true;
                                   break;
                               }
                           }
                       }
                       if($scope.members.length==0){
                           $scope.noMembers = true;
                       } else {
                           $scope.noMembers = false;
                       }
                   }, function (error) {
                       console.log("选人组件.请求人员信息错误", error)
                   })
               };

               /**
                * 加载更多人员
                */
               $scope.moreMembers = function () {
                   $scope.memberQuery.page += 1;
                   ChoosePersonService.member($scope.memberQuery).then(function (data) {
                       $scope.members.total = data.result.total;
                       for(var index in data.result.members) {
                           $scope.members.push(data.result.members[index]);
                           for(var j in $scope.selectedMembers) {
                               if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                   data.result.members[index].selected = true;
                                   break;
                               }
                           }
                       }
                       $scope.hasMore = $scope.members.length<data.result.total;
                   }, function (error) {
                       console.log("选人组件.请求人员信息错误", error)
                   })
               };

               /**
                * 弹击部分
                */
               $scope.clickDepartment = function (dep_id) {
                   $scope.memberQuery.page = 1;
                   $scope.memberQuery.cd_id = dep_id;
                   refreshMember();
               };

               $scope.checkSearchMember = function (event) {
                   if(event.keyCode==13) { // 输入enter键
                       $scope.memberQuery.page = 1;
                       refreshMember();
                   }
               };
               $scope.cancelSearchMember = function () {
                   $scope.memberQuery.page = 1;
                   $scope.memberQuery.keyword = null;
                   refreshMember();
               };



                $timeout(function() {
                    /**
                     * 选择人员操作
                     */
                    $scope.fatherDom = $(".per-item-Wrap");
                    $scope.delDomItem = $(".per-item-Wrap .sel-dpm-item");
                    $scope.cumulationWidth = 0;
                    $scope.initWidth = 65;


                    /**
                     * 增加宽度(内容宽度超出父容器宽度出现滚动条)
                     */
                    $scope.setContentWidth = function(width){

                        var contentWidth = width;
                        $scope.delDomItem.css({
                            width:contentWidth + 10
                        });
                        if( contentWidth >= 465 ){
                            $scope.fatherDom.css({
                                width : 465
                            });
                        }
                        if( contentWidth < 465 ){
                            $scope.fatherDom.css({
                                width : contentWidth + 10
                            });
                        }

                    }

                    if($scope.selectedList.selectedPersons){
                        $scope.cumulationWidth = $scope.selectedList.selectedPersons.length * $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                    }

                    $scope.clickMembers = function (index) {
                        var sm = $scope.members[index];
                        sm.selected = !sm.selected;
                        if(sm.selected) { //
                            if($scope.setShowWhat.person.isSingle) {
                                $scope.selectedMembers.forEach(function (obj) {
                                    obj.selected = false;
                                });
                                $scope.selectedMembers.length = 0;
                            }
                            $scope.selectedMembers.push(sm);
                        } else {  // 取消
                            for(var i in $scope.selectedMembers) {
                                if(sm.m_uid==$scope.selectedMembers[i].m_uid) {
                                    $scope.selectedMembers.splice(i, 1);
                                    break;
                                }
                            }
                        }
                        $scope.cumulationWidth = $scope.initWidth * $scope.selectedMembers.length;
                        $scope.setContentWidth($scope.cumulationWidth);
                    };
                    $scope.deleteMember = function (index) {
                        $scope.selectedMembers[index].selected = false;
                        $scope.cumulationWidth -= $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                        if($scope.members) {
                            for(var j in $scope.members) {
                                if($scope.members[j].m_uid == $scope.selectedMembers[index].m_uid) {
                                    $scope.members[j].selected = false;
                                    break;
                                }
                            }
                        }

                        $scope.selectedMembers.splice(index, 1);
                    };
                    /**
                     * 判断是否选择人员
                     * @param member
                     * @returns {boolean}
                     */
                    function isSelected(member) {
                        var len = $scope.selectedMembers.length;
                        for(var i=0; i<len; i++) {
                            var m = $scope.selectedMembers[i];
                            if(m.m_uid == member.m_uid) {
                                return true;
                            }
                        }
                        return false;
                    }
                    /**
                     * 全部选择
                     */
                    $scope.selectAll = function () {
                        //$scope.selectedMembers = [];
                        for(var index in $scope.members) {
                            $scope.members[index].selected = true;
                            if(!isSelected($scope.members[index])) {
                                $scope.selectedMembers.push($scope.members[index]);
                            }
                        }
                        $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                    };
                    /**
                     * 全部取消
                     */
                    $scope.cancelAll = function () {
                        $scope.selectedMembers = [];
                        $scope.cumulationWidth = 0;
                        $scope.setContentWidth($scope.cumulationWidth);
                        for(var index in $scope.members) {
                            $scope.members[index].selected = false;
                        }
                    };
                    /**
                     * 反选
                     */
                    $scope.selectReverse = function () {
                        $scope.selectedMembers = [];
                        for(var index in $scope.members) {
                            $scope.members[index].selected = !$scope.members[index].selected;
                            if($scope.members[index].selected) {
                                $scope.selectedMembers.push($scope.members[index]);
                            }
                        }
                        if($scope.selectedMembers.length){
                            $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
                        }else{
                            $scope.cumulationWidth = 0;
                        }
                        $scope.setContentWidth($scope.cumulationWidth);
                    };


                });

               init();

           })();

            /***********************************************选人**************************************************/

            /***********************************************部门**************************************************/
            (function(){
                var depTree = null;
                $scope.allDep = {};
                $scope.depQuery = {};

                /**
                 * 判断id是否已经选择
                 * @param id
                 */
                function checkInitSelected(id) {
                    for(var i=0; i<$scope.selectedList.selectedDepartment.length; i++){
                        if($scope.selectedList.selectedDepartment[i].id==id) {
                            return true;
                        }
                    }
                    return false;
                }

                /**
                 * 用户取消选择时，情况已选择列表
                 * @param id
                 */
                function cleanInitSelected(id) {
                    var i=0;
                    for(; i<$scope.selectedList.selectedDepartment.length; i++){
                        if($scope.selectedList.selectedDepartment[i].id==id) {
                            break;
                        }
                    }
                    if(i<$scope.selectedList.selectedDepartment.length) {
                        $scope.selectedList.selectedDepartment.splice(i,1);
                    }
                }

                /**
                 * 初始化遍历部门list
                 */
                var init = function initFun(){
                    $scope.departmentList = ChoosePersonService.department({}).then(function (data) {
                        if(app_config.API.CHECK_SUCCESS(data)) {
                            depTree = $scope.departmentList = data.result.departments;
                            for(var index in data.result.departments) {
                                var dep = data.result.departments[index];
                                dep.isChecked = checkInitSelected(dep.id);
                                if(dep.id == 0) { // 全部人员做为单独部门处理
                                    dep.noChild = true;
                                }
                                $scope.allDep[dep.id] = dep;
                            }
                        }
                    }, function (error) {
                        console.log("选人组件.请求部门信息错误", error)
                    });

                };


                /**
                 * 部分查询操作
                 */
                $scope.depQuery = { };
                $scope.isSearchDep = false;
                // 显示隐藏子部门
                $scope.toggleDep = function (id) {

                    // 已经加载过数据，不再加载
                    $scope.allDep[id].isOpen = !$scope.allDep[id].isOpen;
                    if(($scope.allDep[id].childDep && $scope.allDep[id].childDep.length>0) || $scope.allDep[id].noChild) {
                        return;
                    }

                    $scope.depQuery.cd_id = id;
                    $scope.allDep[id]['number'] = 0;
                    ChoosePersonService.department($scope.depQuery).then(function (data) {
                        if(app_config.API.CHECK_SUCCESS(data)) {
                            if(data.result.departments.length>0) {
                                for(var index in data.result.departments) {
                                    var dep = data.result.departments[index];
                                    var have = $scope.allDep.hasOwnProperty(dep.id);
                                    dep['parentDep_id'] = id;
                                    dep['parent_id'] = id;
                                    dep['isChecked'] = checkInitSelected(dep.id)  // 用户之前已经选择
                                        || $scope.allDep[id]['isChecked']         // 上级部门选中状态
                                        || (have && $scope.allDep[dep.id].isChecked); // 用户通过搜索过后的选中状态

                                    $scope.allDep[dep.id] = dep;
                                }
                                $scope.allDep[id].childDep = data.result.departments;

                                if( $scope.allDep[id]['isChecked'] ){
                                    var get_Child_len =  $scope.allDep[id].childDep.length;
                                    $scope.allDep[id]['number'] = get_Child_len;
                                }

                            } else {
                                $scope.allDep[id].noChild = true;
                            }
                        }
                    }, function (error) {
                        console.log("选人组件.请求部分信息错误", error)
                    });
                };


                /**
                 * 子部门 递归
                 * @param node
                 * @param isChecked
                 */
                function childProcess(node, isChecked) {
                    node['isChecked'] = isChecked;
                    node.number = 0;
                    if(node.childDep) {
                        for(var index in node.childDep) {
                            childProcess(node.childDep[index], isChecked);
                        }
                    }
                }

                /**
                 * 父部门递归
                 * @param node
                 * @param isChecked
                 */
                function parentProcess(node, isChecked) {
                    if (isChecked == true) {
                        node.number++;
                    } else {
                        node.number--;
                    }
                    (node.number == node.childDep.length) ? node['isChecked'] = true : node['isChecked'] = false;
                    if(node.parentDep_id) {
                        parentProcess($scope.allDep[node.parentDep_id], isChecked)
                    }
                }

                /**
                 * 点击部门，设置部门选择状态
                 *
                 * @param id
                 */
                $scope.selectDepartment = function (id) {
                    var dep = $scope.allDep[id];

                    dep['isChecked'] = !dep.isChecked;
                    if(!dep.hasOwnProperty('number')){
                        dep['number'] = 0;
                    }

                    if(!dep['isChecked']) {
                        cleanInitSelected(dep.id);
                    }

                    // 处理搜索操作时的对象
                    if($scope.isSearchDep) {
                        for(var index=0;index<$scope.departmentList.length; index++) {
                            if($scope.departmentList[index].id == id) {
                                $scope.departmentList[index].isChecked = !$scope.departmentList[index].isChecked
                            } /*单选处理*/else if($scope.setShowWhat.department.isSingle) {
                                $scope.departmentList[index].isChecked = false;
                            }
                        }
                    }

                    /* 单选处理：清楚其它选择项 */
                    if($scope.setShowWhat.department.isSingle && dep.isChecked) {
                        for(var k in $scope.allDep) {
                            if(k!=id) {
                                $scope.allDep[k].isChecked = false;
                                //$scope.allDep[k] = false;
                            }
                        }
                    }

                    if(dep.childDep && !$scope.isSearchDep)  {
                        // 递归子部门
                        for(var index in dep.childDep) {
                            childProcess(dep.childDep[index], dep['isChecked']);
                        }
                    }
                    // TODO [现在不要操作上级部门] 递归子部门
                    //if(dep.parentDep_id) {
                    //    parentProcess($scope.allDep[dep.parentDep_id], dep['isChecked']);
                    //}

                };

                /*
                 * 计算选中的部门
                 */
                $scope.computeSelectedDepartment = function(tree) {

                    var list = [];

                    for(var key in tree) {
                        var dep = tree[key];
                        if(dep['isChecked']) {
                            list.push(dep);
                        } /*else TODO 返回所有部门，现在需求是：选择部门并不一定会选中下面的子部门  if(dep.childDep) {
                         var childList = computeSelectedDepartment(dep.childDep);
                         childList.forEach(function (item) {
                         list.push(item);
                         })
                         }
                         */
                    }

                    return list;
                }

                /**
                 * 对于用户确定部门选择完成操作，需要在所有部门【$scope.allDep】中遍历查找
                 * @param event
                 */
                $scope.checkSearchDep = function (event) {
                    if(!$scope.depQuery.keyword) {
                        // 没有搜索内容
                        return;
                    }
                    $scope.depQuery.cd_id = null;
                    if(event.keyCode==13) { // 输入enter键
                        ChoosePersonService.department($scope.depQuery).then(function (data) {
                            if(app_config.API.CHECK_SUCCESS(data)) {
                                $scope.departmentList = data.result.departments;
                                for(var i in $scope.departmentList) {
                                    var dep = $scope.departmentList[i];
                                    var have = $scope.allDep.hasOwnProperty(dep.id);
                                    dep.noChild = true;
                                    dep.isChecked = checkInitSelected(dep.id) ||
                                        (have && $scope.allDep[dep.id].isChecked);
                                    // 新对象保存到缓存中
                                    if(!have) {
                                        $scope.allDep[dep.id] = JSON.parse(JSON.stringify(dep));
                                    }
                                }
                            }
                            $scope.isSearchDep = true;
                        }, function (error) {
                            console.log("选人组件.请求部分信息错误", error)
                        });

                    }
                };

                /**
                 * 取消搜索部门
                 */
                $scope.cancelSearchDep = function () {
                    $scope.departmentList = depTree;
                    $scope.depQuery.keyword = null;
                    $scope.isSearchDep = false;
                };

                init();
            })();
            /***********************************************部门**************************************************/

            /***********************************************标签**************************************************/
            (function() {

                /*$scope.members = ChoosePersonService.member($scope.memberQuery).then(function (data) {
                    $scope.members = data.result.members;
                    $scope.hasMore = $scope.members.length<data.result.total;
                    for(var index in data.result.members) {
                        for(var j in $scope.selectedMembers) {
                            if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                data.result.members[index].selected = true;
                                break;
                            }
                        }
                    }
                    if($scope.members.length==0){
                        $scope.noMembers = true;
                    } else {
                        $scope.noMembers = false;
                    }
                }, function (error) {
                    console.log("选人组件.请求人员信息错误", error)
                })*/

                /*$scope.tags = TagsService.tags.length ? TagsService.tags : [{id: 1,tagname: '麦霸'},{id: 2,tagname: '游泳'},{id: 3,tagname: '学习'},
                    {id: 4,tagname: '旅游'},{id: 5, tagname: '电影'},{id: 6, tagname: '看书'},
                    {id: 7, tagname: '健身'},{id: 8, tagname: '泡妞'},{id: 9, tagname: 'K哥'}];*/

                var tagsQuery = {
                    page:1,
                    limit:10
                };
                var total = 0;

                $scope.tags =  ChoosePersonService.tag(tagsQuery).then(function(data) {
                    if(data.errcode == 0) {
                        $scope.tags = TagsService._tags = data.result.list;
                        $scope.hasMoreTags = TagsService.hasMore(data.result.count);
                        for(var index in data.result.list) {
                            for(var j in TagsService.checks) {
                                if(TagsService.checks[j].ccl_id == data.result.list[index].ccl_id) {
                                    data.result.list[index].selected = true;
                                    break;
                                }
                            }
                        }
                    }
                }, function() {
                    console.log(error);
                });

                $scope.moreTags = function() {
                    tagsQuery.page += 1;
                    ChoosePersonService.tag(tagsQuery).then(function(data) {
                        total = data.result.total;
                        for(var index in data.result.list) {
                            TagsService._tags.push(data.result.list[index]);
                            $scope.tags.push(data.result.list[index]);
                        }
                        $scope.hasMoreTags = TagsService.hasMore(data.result.count);
                    });
                };

                /**
                 * 加载更多人员
                 */
               /* $scope.moreMembers = function () {
                    $scope.memberQuery.page += 1;
                    ChoosePersonService.member($scope.memberQuery).then(function (data) {
                        $scope.members.total = data.result.total;
                        for(var index in data.result.members) {
                            $scope.members.push(data.result.members[index]);
                            for(var j in $scope.selectedMembers) {
                                if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                    data.result.members[index].selected = true;
                                    break;
                                }
                            }
                        }
                        $scope.hasMore = $scope.members.length<data.result.total;
                    }, function (error) {
                        console.log("选人组件.请求人员信息错误", error)
                    })
                };*/


                TagsService.init({
                    isSingle: $scope.params.tag.isSingle
                });

                $scope.chooseTag = TagsService.clickTag;
            })();
            /***********************************************标签**************************************************/


            /**
             * 确认选择
             * @constructor
             */
            $scope.OK = function () {
                $scope.selectedList.selectedPersons = $scope.selectedMembers;
                $scope.selectedList.selectedDepartment =  $scope.computeSelectedDepartment($scope.allDep);
                $scope.selectedList.selectedTags = TagsService.checks;
                $scope.doOk($scope.selectedList);
            };


    }]);

    app.service('TagsService',[function() {

        var tag = {
            isSingle: false,        //是否单选
            _tags : [],              //所有标签
            checks: []              //选中标签数据
        };

        tag.init = function(config) {
            for(var k in config) {
                tag[k] = config[k];
            }
        };

        tag.hasMore = function(total) {
          return tag._tags.length < total;
        };

        tag.clickTag = function(index) {
            var currentTag = tag._tags[index];
            currentTag.selected = !currentTag.selected;
            if(currentTag.selected) {
                if(tag.isSingle) {
                    tag.checks.forEach(function (obj) {
                        obj.selected = false;
                    });
                    tag.checks.length = 0;
                }
                tag.checks.push(currentTag);
            }
            else {
                for(var i in tag.checks) {
                    if(currentTag.id == tag.checks[i].id) {
                        tag.checks.splice(i, 1);
                        break;
                    }
                }
            }
        };
        return tag;

    }]);


    app.factory('AdvancedChooser', ['$q','$modal',function ($q,$modal) {
    return {
        choose: function (selected,size,params) {
            var defer = $q.defer();
            var modalInstance = $modal.open({
                templateUrl: 'templates/choose-advanced.html',
                size: size || 'lg',
                resolve: {
                    selected: function () {
                        //return selected?JSON.parse(JSON.stringify(selected)):null;
                        return selected;
                    },
                    params: function () {
                        return params || {};
                    }
                },
                controller:['$scope', '$modalInstance','selected','params',function ($scope, $modalInstance, selected,params) {
                    for(var k in params) {
                        $scope[k] = params[k];
                    }
                    $scope.selectedList = selected;
                    $scope.params = params;

                    $scope.doOk = function (res) {
                        $modalInstance.close(res);
                    };
                    $scope.doCancel = function (res) {
                        $modalInstance.dismiss(res || 'cancel');
                    };
                }]
            });

            modalInstance.opened.then(function(){//模态窗口打开之后执行的函数
                //console.log('modal is opened');
            });
            modalInstance.result.then(function (result) {
                defer.resolve(result);
            }, function (reason) {
                defer.reject(reason);
            });

            return { result:defer.promise };
        }
    };
}]);

})(angular.module('ng.poler.plugins.pc'));

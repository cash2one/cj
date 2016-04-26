/**
 * Created by three on 15/12/30.
 */
(function (app) {
    app.controller('MemberPropsCtrl',['$scope','DialogTool', 'MemberApi', 'AddPropsDialogServer','Tips','Page', function ($scope,DialogTool, MemberApi, AddPropsDialogServer, Tips, Page) {


        $scope.isEditInfo = false;

        /**
         * 根据标签ID数组得到完整的标签信息
         * @param tagArray 完整的标签数组
         * @param tagIdArray 需要的标签ID数组
         * @private
         *
         */
        function _getNeedTag(tagArray, tagIdArray){
            var newTagArray = [];
            if(tagIdArray != null && tagArray != null){
                for(var i=0; i<tagIdArray.length; i++) {
                    for(var j=0; j<tagArray.length;j++) {
                        if(tagArray[j].laid == tagIdArray[i]){
                            newTagArray.push(tagArray[j]);
                            break;
                        }
                    }
                }
            }
            return newTagArray;
        }

        /**
         * tab 切换
         */
        $scope.tab1 = true;
        $scope.propsTab = function (num) {
            if($scope.isEditInfo == true){
                Tips.show({
                    message:'当前页还有未保存'
                });
            }else{
                if(num == 1){
                    $scope.tab1 = true;
                    $scope.tab2 = false;
                }else{
                    $scope.tab1 = false;
                    $scope.tab2 = true;
                }
            }

        };



        /**
         * 取消或返回
         */
        $scope.return = function(){
            if($scope.isEditInfo){
                DialogTool.open({
                        templateUrl: '/admincp/static/ng-modules/member/views/props-tpl/set-props-dialog.html'
                    })
                    .result.then(function ok() {
                    Page.goPage('app/page/member/main',{
                    });
                }, function () {
                    // 取消不做任何操作
                })
            }else{
                Page.goPage('app/page/member/main',{});
            }
        }


        /**
         * 已选属性全属性数组勾选装态
         * @param fixed
         * @param nowTagArray
         * @private
         */
        function _checkedTag(fixed,nowFixed){
            if(fixed != null && nowFixed != null){
                for(var i in nowFixed) {
                    for(var j in fixed) {
                        if(i == j && nowFixed[i]['view'] == 1){
                            fixed[j].ischecked = true;
                            break;
                        }
                    }
                }
            }
            return fixed;
        }

        /**
         * 属性 init
         */
        (function(){
            MemberApi.attrIndex().then(function(data){
                if(data.errcode == 0){
                    $scope.fixed = data.result.field.fixed;
                    $scope.custom = data.result.field.custom;
                    $scope.label = data.result.label;
                    for(var i in $scope.fixed){
                        $scope.fixed[i].fieldName = i;
                    }

                    var tempField = {}; //提取自定义属性信息
                    for(var i in $scope.custom){
                        $scope.custom[i].fieldName = i;
                        if(i.indexOf('ext') != -1){
                            tempField[i] = $scope.custom[i];
                        }
                    }
                    AddPropsDialogServer.tempField = tempField;

                    //标签init
                    MemberApi.labelList().then(function (data){
                        if(data.errcode == '0'){
                            $scope.tagList = data.result.list;

                            var label = $scope.label;
                            $scope.labelList = [];
                            if(label){
                                $scope.isInfoHide = true;
                                for(var i=0; i<label.length; i++){
                                    label[i]['selectedTagList'] =  _getNeedTag($scope.tagList,label[i].laid);//根据id恢复标签信息
                                    $scope.labelList.push(label[i]);
                                }
                            }

                        }  else{
                            Tips.show({
                                message: data.errmsg
                            });
                        }
                    },function(data){
                        Tips.show({
                            message: data
                        });
                    });

                }else{
                    Tips.show({
                        message: data.errmsg
                    });
                }
            },function(data){
                Tips.show({
                    message: data
                });
            });
        })();


        /**
         * CHECKBOX 反选
         * @param fixedName
         * @param fixedField
         */
        $scope.toggleCheckbox = function(fixedName,field, event){
            event.stopPropagation();
            var checkde = field[fixedName];
            console.log(checkde, $scope.custom);


            if(checkde && checkde != 0){
                field[fixedName] = 0;
            }else{
                field[fixedName] = 1
            }
            $scope.isEditInfo = true;

            console.log('$scope.custom:', $scope.custom);
        };

        /**
         * 添加人员属性
         */
        $scope.add = function () {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/props-tpl/add-props-dialog.html'
            }).result.then(function(tempField){

                console.log('$scope.custom:', $scope.custom);

                if(tempField){
                    AddPropsDialogServer.tempField = tempField;

                    for(var i=1; i <= 10; i++){
                    	console.log($scope.custom)
                        delete $scope.custom['ext'+i];
                    }
                    for(var i in tempField){
                        if(tempField[i]['name']){
                            $scope.custom[i] = tempField[i];
                        }
                    }
                    $scope.isEditInfo = true;
                }
            });
        };

        /**
         * 根据正则判断排序编号
         */
        $scope.modifyNo = function(customField){
            console.log(customField.number);
            var pattern=/^\d{0,2}$/;
            var flag = pattern.test(customField.number);
            if(!flag){
                Tips.show({
                    message:'序号只能为数字,且小于100'
                });
                customField.number = '';

            }
            $scope.isEditInfo = true;

        };

        /**
         * 编辑自定义属性
         */
        $scope.edit = function (obj) {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/props-tpl/edit-props-dialog.html'
            });
        };

        /**
         * 删除自定义属性
         */
        $scope.delete = function (customField) {
            DialogTool.open({
                templateUrl:'/admincp/static/ng-modules/member/views/props-tpl/del-props-dialog.html'
            }).result.then(function(data){
                if(data){
                    delete $scope.custom[customField.fieldName];
                    if(AddPropsDialogServer.tempField){
                        delete AddPropsDialogServer.tempField[customField.fieldName];
                    }
                    $scope.isEditInfo = true;
                }
            });
        };


        /**
         * 设置
         */
        var tempLabelList;
        $scope.setLabel = function(){
            $scope.isHide = !$scope.isHide;
            $scope.isInfoHide = !$scope.isInfoHide;

            tempLabelList = JSON.parse(JSON.stringify($scope.labelList));

            if($scope.isHide){
                $scope.fixed.name.ischecked = true;
                if($scope.labelList.length == 0){
                    $scope.labelList.push({
                        selectedTagList:[],
                        fixed:JSON.parse(JSON.stringify($scope.fixed)),
                        custom:JSON.parse(JSON.stringify($scope.custom))
                    });
                }else{
                    for(var i=0; i<$scope.labelList.length; i++){
                        $scope.labelList[i]['fixed'] = _checkedTag(JSON.parse(JSON.stringify($scope.fixed)),$scope.labelList[i]['view']);
                        $scope.labelList[i]['custom'] = _checkedTag(JSON.parse(JSON.stringify($scope.custom)),$scope.labelList[i]['view']);
                    }
                }
            }
        };

        $scope.cancelLabel = function(){
            $scope.labelList = tempLabelList;
            $scope.isHide = !$scope.isHide;
            $scope.isInfoHide = !$scope.isInfoHide;
        }


        /**
         * 继续添加
         */
        $scope.addLabel = function(){
            $scope.labelList.push({
                selectedTagList:[],
                fixed:JSON.parse(JSON.stringify($scope.fixed)),
                custom:JSON.parse(JSON.stringify($scope.custom))
            });
            $scope.isEditInfo = true;
        };

        /**
         * 删除标签权限栏
         * @param tag
         */
        $scope.delLabel = function (label){
            $scope.labelList.splice($scope.labelList.indexOf(label),1)
            $scope.isEditInfo = true;
        };


        /**
         * 标签查询
         */
        $scope.tagList = function(){
            MemberApi.labelList().then(function(data){
                if(data.errcode == 0){
                    console.log(data);
                }
            },function(data){
                Tips.show({
                    message: data.errmsg
                });
            });
        };

        /**
         * 选择新标签
         * @param tag
         */
        $scope.selectedTag = function(label,tag){
            label.isSelectedHied = false;
            var isPull = true;
            for(var i=0 ;i<label.selectedTagList.length ;i++){

                if(label.selectedTagList[i].laid == tag.laid){
                    isPull = false;
                    break;
                }
            }
            if(isPull){
                label.selectedTagList.push(tag);
            }
            $scope.isEditInfo = true;
        };

        /**
         * 删除选中的标签
         * @param event
         * @param tagList
         * @param tag
         */
        $scope.delTag = function(event ,tagList ,tag){
            event.stopPropagation();
            tagList.splice(tagList.indexOf(tag), 1);
            $scope.isEditInfo = true;
        };

        /**
         * 显示标签列表
         */
        $scope.showTag = function(label){
            label.isSelectedHied =! null ? !label.isSelectedHied : true;
            if(!$scope.tagList || $scope.tagList == 0){
                DialogTool.open({
                        templateUrl: '/admincp/static/ng-modules/member/views/props-tpl/set-tagPage-dialog.html'
                    })
                    .result.then(function ok() {
                    Page.goPage('app/page/member/tag',{
                    });
                }, function () {
                    // 取消不做任何操作
                })
            }
        };

        /**
         * 选择属性
         * @param label
         * @param fixed
         */
        $scope.checkedFixed = function(label,fixed,event){
            event.stopPropagation();
            if(!label['view']){
                label['view'] = {}
            }
            if(!fixed.ischecked){
                fixed.ischecked = true;
                fixed.view = 1;
                label['view'][fixed.fieldName] = fixed;
            }else{
                fixed.ischecked = false;
                fixed.view = 0;
                delete label['view'][fixed.fieldName];
            }
            $scope.isEditInfo = true;
        };

        /**
         * 保存
         */
        $scope.save = function(){
            $scope.isEditInfo = true;

            for(var i in $scope.custom){
                if(!$scope.custom[i].number){
                    Tips.show({
                        message: $scope.custom[i].name + '的属性序号不能为空'
                    });
                    return ;
                }
            }


            var label = JSON.parse(JSON.stringify(_filterList($scope.labelList,_labelRely)));
            if(label){
                for(var i=0; i<label.length; i++){
                    var tagList = label[i].selectedTagList;
                    label[i]['laid'] = [];
                    for(var j=0; j<tagList.length; j++){
                        label[i]['laid'].push(tagList[j].laid);
                    }
                    delete label[i].selectedTagList;
                    delete label[i].fixed;
                    delete label[i].custom;
                    delete label[i].isSelectedHied;
                }
            }


            var params = {
                field : {
                    fixed:$scope.fixed,
                    custom:$scope.custom,
                },
                label : label
            };
            console.log('params:', params);


            MemberApi.editField(params).then(function(data){
                if(data.errcode == 0){

                    Tips.show({
                        message: '操作成功'
                    });
                    Page.refreshState();
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

            return ;
        }

    }]);


    /**
     * 过滤数组
     */
    function _filterList(oldList, relyFn){
        var list = [];
        if(oldList){
            _.map(oldList,function (label){
                if(relyFn(label)){
                    list.push(label);
                }
            });
        }
        return list;
    }

    /**
     * 属性栏过滤依据
     * 过滤正确的属性信息
     * @param label
     * @returns {boolean}
     * @private
     */
    function _labelRely(label){

        if(label && label.selectedTagList.length != 0){
            return true;
        }
            return false;
    }



})(angular.module('app.modules.member'));
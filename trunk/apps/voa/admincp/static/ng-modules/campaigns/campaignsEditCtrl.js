(function(app){
    app.controller('editCtrl',['$rootScope','$scope','$window','$location','$filter','CampaignsApi','$filter',
        function($rootScope,$scope,$window,$location,$filter,CampaignsApi){
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-edit page-header-icon\"><\/i>&nbsp;&nbsp;编辑活动");
            var thisCtrl = this ;
            $scope.editor;
            $scope.$on('$destroy', function() {
                $scope.editor.destroy();
            });
            $scope.types =[];
            $rootScope.campaignsEdit = $rootScope.campaignsList;
            (function(){
                function getTypes(){
                    CampaignsApi.CampaignSettingList().then(function(res){
                        $scope.types = res.result;
                    });
                }
                function init(data){
                    $scope.subject = data['subject'];
                    thisCtrl.begintime = convertTime(data['begintime']);
                    thisCtrl.overtime = convertTime(data['overtime']);
                    $scope.content = data['content'];
                    $scope.deps = data['cd_ids'];
                    $scope.id = data['id'];
                    $scope.mems = data['m_uids'];
                    $scope.typeid=data['typeid'];
                    $scope.cover=data['cover'];
                    $scope.picurl = data['picurl'];
                    $scope.dep_arr = [];
                    $scope.m_uid =[];
                    $scope.select_m_uid_name = "" ;
                    $scope.select_dep_name ='';
                    $scope.dep_cd_ids =[];
                    $scope.mem_uids =[];
                    $scope.is_push=data['is_push'];
                    if($scope.editor){$scope.editor.setContent($scope.content);}
                    if( $scope.deps.length!=0 ||$scope.mems.length!=0){
                        $("#user_dep_container").show();
                        $("#specified_btn").addClass('btn-primary');
                        $("#all_btn").removeClass('btn-primary');
                        $scope.select_dep_name = '';
                        $scope.dep_arr = [];
                        $scope.m_uid =[];
                        for (var i = 0; i < $scope.deps.length; i ++) {
                            if($scope.deps[i]['is_show']=='1'){
                                $scope.select_dep_name += $scope.deps[i]['cd_name'] + ' ';
                                $scope.dep_arr.push({
                                    id:$scope.deps[i]['cd_id'],
                                    name:$scope.deps[i]['cd_name'],
                                    isChecked:true
                                });
                                $scope.dep_cd_ids.push({
                                    'id':$scope.deps[i]['cd_id'],
                                    'cd_upid':0
                                })
                            }
                        }
                        $scope.select_m_uid_name = '';
                        for(var i=0;i<$scope.mems.length;i++){
                            $scope.select_m_uid_name += $scope.mems[i]['m_username'] + ' ';
                            $scope.mem_uids.push($scope.mems[i]['m_uid']);
                            var uu = $scope.mems[i];
                            uu['selected'] = true;
                            $scope.m_uid.push(uu);
                        }
                    }
                }

                function getCampaignsById(){
                    var id =$location.search().id;
                    CampaignsApi.GetCampaignsById({'id':id}).then(function(res){
                        if(res.errcode==0){
                            var data = res.result ;
                            init(data);
                        }else{
                            console.log(res.errmsg);
                        }
                    });
                }
                getTypes();
                getCampaignsById();
            })();
            function convertTime (nS){
                var timestamp = parseInt(nS) * 1000;
                return $filter('date')(timestamp,'yyyy-MM-dd HH:mm');
            }
            $('#specified_btn').click(function(){
                $(this).addClass('btn-primary');
                $("#all_btn").removeClass('btn-primary');
                $("#user_dep_container").show();
            });
            $("#all_btn").click(function(){
                $(this).addClass('btn-primary');
                $("#specified_btn").removeClass('btn-primary');
                $("#user_dep_container").hide();
                $scope.deps =[];
                $scope.mems =[];
                $scope.select_m_uid_name ="";
                $scope.select_dep_name = "" ;
                $scope.m_uid =[] ;
                $scope.dep_arr = [];
            });
            // 选择部门回调
            $scope.selectedDepartmentCallBack = function(data){
                var depArr = [];
                var copyArr = [];
                var resArr = [];

                for(var k=0; k<data.length; k++){
                    depArr.push(data[k]);
                    copyArr.push(data[k]);
                }
                var dLen = depArr.length;
                var cLen = copyArr.length;
                for(var i=0; i<cLen; i++){
                    var flag = false;
                    for(var j=0; j<dLen; j++){
                        if(depArr[j].id == copyArr[i].parentDep_id){
                            flag = true;
                        }
                    }
                    if(!flag){
                        resArr.push(copyArr[i]);
                    }
                }
                $scope.dep_arr = resArr;
                $scope.select_dep_name = '';
                var cd_ids = [];
                for (var i = 0; i < $scope.dep_arr.length; i ++) {
                    $scope.select_dep_name += $scope.dep_arr[i]['name'] + ' ';
                    var parentId = $scope.dep_arr[i]['parentDep_id'];
                    parentId = parentId?parentId:0;
                    var o = new Object();
                    o.id = $scope.dep_arr[i]['id'];
                    o.cd_upid = parentId;
                    cd_ids.push(o);
                    $scope.dep_cd_ids = cd_ids;
                }
            }
            // 选择人员选择回调
            $scope.selectedMuidCallBack = function(data) {
                $scope.m_uid = data;
                $scope.mems =[];
                // 页面埋入 选择的值
                $scope.select_m_uid_name = '';
                for (var i = 0; i < data.length; i ++) {
                    $scope.select_m_uid_name += data[i]['m_username'] + ' ';
                    $scope.mem_uids.push(data[i]['m_uid']);
                }
            }

            $scope.publish = function(is_push){
                $scope.content = $scope.editor.getContent();
                var saveData = {
                    actid:$scope.id,
                    subject:$scope.subject,
                    typeid:$scope.typeid,
                    cover:$scope.cover,
                    begintime:thisCtrl.begintime,
                    overtime:thisCtrl.overtime,
                    cd_ids:$scope.dep_cd_ids,
                    m_uids:$scope.mem_uids.join(','),
                    content:$scope.editor.getContent(),
                    is_push:is_push
                }
                if(!validate(saveData)){
                    return ;
                }
                CampaignsApi.CampaignsSave(saveData).then(function(res){
                    if(res.errcode !=0){
                        alert(res.errmsg);
                    }else{
                        $window.location.href = "/admincp/office/campaigns/list/pluginid/50";
                    }
                },function(msg){
                    console.log(msg);
                });
            }
            function validate(saveData){
                if(saveData.subject.length>64){
                    alert("活动名称不能超过64个字符！");
                    return false;
                }else if(saveData.subject==""){
                    alert("活动名称不能为空！");
                    return false;
                }else if(saveData.typeid=="0"){
                    alert("请选择一个活动类型！");
                    return false ;
                }else if(saveData.begintime!=""&&saveData.overtime!=""){
                    var startDate = new Date(saveData.begintime).getTime();
                    var endDate = new Date(saveData.overtime).getTime();
                    if(startDate>endDate){
                        alert("开始时间不能大于结束时间！");
                        return false;
                    }
                }
                if(saveData.is_push==1){
                    if(saveData.content==""){
                        alert("活动内容不能空！");
                        return false;
                    }
                }
                return true;
            }
        }]);
})(angular.module('app.modules.campaignsList'));
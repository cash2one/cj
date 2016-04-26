(function(app){
    app.controller('addCtrl',['$scope' ,'$window','CampaignsApi','$filter',
        function($scope,$window,CampaignsApi,$filter){
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-plus page-header-icon\"><\/i>&nbsp;&nbsp;新增活动");
            $scope.types =[];
            $scope.newActivity ={
                subject:'',
                typeid:"0",
                cover:0,
                begintime:"",
                overtime:"",
                cd_ids:[],
                m_uids:[],
                content:''
            };
            $scope.editor= null;
            $scope.$on('$destroy', function() {
                $scope.editor.destroy();
            });
            $('#specified_btn').click(function(){
                $(this).addClass('btn-primary');
                $("#all_btn").removeClass('btn-primary');
                $("#user_dep_container").show();
            });
            $("#all_btn").click(function(){
                $(this).addClass('btn-primary');
                $("#specified_btn").removeClass('btn-primary');
                $("#user_dep_container").hide();
                $scope.newActivity.cd_ids =[];
                $scope.newActivity.m_uids =[];
                $scope.select_m_uid_name ="";
                $scope.select_dep_name = "" ;
                $scope.m_uid =[] ;
                $scope.dep_arr = [];
            });
            $scope.dep_arr = [];
            $scope.m_uid =[];
            $scope.select_m_uid_name = "" ;
            $scope.select_dep_name ='';
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
                console.log($scope.dep_arr)
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
                    $scope.newActivity.cd_ids = cd_ids;
                }
            }
            // 选择人员选择回调
            $scope.selectedMuidCallBack = function(data) {
                $scope.m_uid = data;
                $scope.newActivity.m_uids =[];
                // 页面埋入 选择的值
                $scope.select_m_uid_name = '';
                for (var i = 0; i < data.length; i ++) {
                    $scope.select_m_uid_name += data[i]['m_username'] + ' ';
                    $scope.newActivity.m_uids.push(data[i]['m_uid']);
                }
            }
            $scope.saveAsDraft = function(is_push){
                $scope.newActivity.content = $scope.editor.getContent();
                var saveData = copyObj($scope.newActivity) ;
                saveData['is_push'] = is_push !=undefined?is_push:0;
                if(!validate(saveData)){
                    return ;
                }
                CampaignsApi.CampaignsAdd(saveData).then(function(res){
                    if(res.errcode!=0){
                        alert(res.errmsg);
                    }else{
                        $window.location.href ="/admincp/office/campaigns/list/pluginid/50";
                    }
                },function(msg){
                    console.log(msg);
                });
            }
            $scope.publish = function(){
                $scope.saveAsDraft(1);
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
            function isArray(obj){
                return Object.prototype.toString.call(obj) === '[object Array]';
            }
            function copyObj(obj){
                var o = {};
                for(var key in obj){
                    o[key] = obj[key] ;
                    if(key=='m_uids'){
                        o[key] = o[key].join(",")
                    }
                }
                return o ;
            }

            function getTypes(){
                CampaignsApi.CampaignSettingList().then(function(res){
                    $scope.types = res.result;
                });
            }
            function init(){
                getTypes();
            }
            init();
        }]);
})(angular.module('app.modules.campaignsAdd'));
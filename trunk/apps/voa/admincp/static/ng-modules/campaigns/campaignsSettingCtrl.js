(function(app,window){
    app.controller('settingCtrl',['$scope','CampaignsApi', function($scope,CampaignsApi){
        $scope.types = [];
        $scope.maxLength = 10 ;
        var delData= []

        $scope.save = function(){
            var saveData = copyArrayObj($scope.types);
            CampaignsApi.CampaignSettingSave({"save_data":saveData,'del_ids':delData.join(",")})
                .then(function(res){
                    if(res.errcode!=0){
                        alert(res.errmsg);
                    }else{
                        alert("保存成功！");
                        window.location.reload(true);
                    }
                },function(msg){
                    console.log(msg);
                });
        }
        $scope.add = function(){
            if($scope.types.length<10){
                var obj = {
                    id:0,
                    order_sort:findMaxOrder()+1,
                    title:''
                }
                $scope.types.push(obj);
            }
        }
        $scope.delete = function(id,$$index){
            var type = findTypeById(id);
            if(id != ""&&id !=null&&id!=0&&type != null){
                if(type['count']<=0){
                    delData.push(type.id);
                    $scope.types.splice($$index,1);
                }else{
                    alert("分类下有内容不能删除！");
                }
            }else if($$index>-1){
                $scope.types.splice($$index,1);
            }
        }
        $scope.cancel = function(){
            window.location.reload(true);
        }
        function findTypeById(id){
            for (var type in $scope.types){
                if($scope.types[type].id==id){
                    return $scope.types[type];
                }
            }
            return null;
        }
        function findMaxOrder(){
            var max = 0;
            if($scope.types.length==1){
                max = parseInt($scope.types[0].order_sort) ;
            }
            for (var i =0;i< $scope.types.length-1;i++){
                max = parseInt($scope.types[i].order_sort) ;
                if(max<=parseInt($scope.types[i+1].order_sort)){
                    max = parseInt($scope.types[i+1].order_sort) ;
                }
            }
            return max;
        }
        function copyArrayObj(obj){
            if(Object.prototype.toString.call(obj) !== '[object Array]'){
                return obj ;
            }
            var copyObj = [];
            for(var i=0;i<obj.length;i++){
                var o = {};
                for(var key in obj[i]){
                    if(key!=="$$hashKey"){
                        if(key!="order_sort"){
                            o[key] = obj[i][key];
                        }else{
                            o[key] = parseInt(obj[i][key]);
                        }
                    }
                }
                copyObj.push(o);
            }
            return copyObj ;
        }
        function getList(){
            CampaignsApi.CampaignSettingList().then(function(data){
                $scope.types = copyArrayObj(data.result);
                console.log($scope.types);
                if($scope.types.length==0){
                    $scope.add();
                }
            },function(errorMsg){
                console.log(errorMsg);
            });
        }
        getList();
    }]);
})(angular.module('app.modules.campaignsSetting'),window);

(function(app){
  app.controller('wechatSettingCtrl',['$scope','DailyReportApi' , function($scope,DailyReportApi){
      DailyReportApi.getWeChatmenu({}).then(function(data){
          if(data.errcode==0){
              $scope.weChatData = data.result;
          };

      })
//保存
      $scope.weChatSave = function(){
          var params = {};
          var d = $scope.weChatData;
          for(var i=0; i< d.length; i++){
              var dItem = d[i];
              params[dItem.form_name] = dItem.name;
              if(dItem.sub_button){
                  for(var j=0; j<dItem.sub_button.length; j++){
                      var cItem = dItem.sub_button[j];
                      params[cItem.form_name] = cItem.name;
                  }
              }
          }
          DailyReportApi.weChatSave(params).then(function(data){
              if(data.errcode==0){
                  alert("保存成功");
                  location.reload();
              }else{
                  alert(data.errmsg);
              }
          })
      }
//一键还原
      $scope.weChatrestore = function(){
          if(window.confirm('确定要还原为默认菜单名称吗？')){
            DailyReportApi.weChatrestore({}).then(function(data){
                if(data.errcode==0){
                    alert("一键还原成功");
                    location.reload();
                }
            })
          }
      }

      $scope.$watch('weChatData',function(){
        if($scope.weChatData){
          for(var i=0; i<$scope.weChatData.length; i++){
            var item = $scope.weChatData[i];
            if($scope.weChatData[i].name.length >= 5){
              item.name = item.name.substr(0,5);
            }
            if(item.sub_button){
              for(var j=0; j<item.sub_button.length; j++){
                var obj = item.sub_button[j];
                if(obj.name.length >= 5){
                  obj.name = obj.name.substr(0,5);
                }
              }
            }
          }
          
        }
      },true)
  }])
})(angular.module('app.modules.dailyreportwechat'));
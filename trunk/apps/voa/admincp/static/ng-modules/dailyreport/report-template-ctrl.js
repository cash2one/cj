(function(app){
  app.controller('reportTemplateCtrl',['$scope','Page','DailyReportApi',function($scope,Page,DailyReportApi){
    $('#sub-navbar').find("h1").html("<i class=\"fa fa-file-text-o page-header-icon\"><\/i>&nbsp;&nbsp;报告模板设置");
    var curPage = "";

    $scope.repList = [];

    $scope.getReportPage = function (page) {
        $scope.repList = [];
        $scope.reportQueryParams.page = page;
        fetchReportList($scope.reportQueryParams);
    };

    function fetchReportList(params){
      $scope.reportQueryParams = params;
      DailyReportApi.getReportList(params).then(function (data) {
          if(data.errcode == 0){
              $scope.modoleListData = data;
              $scope.reportList = data.result.list;
              $scope.resultCount = data.result.count;
              // 分页
              curPage = params.page;
              $scope.polerPaginationCtrl.reset({
                  total:data.result.count,
                  pages:data.result.pages,
                  curPage:params.page
              });
              ;(function(){
                var arr = $scope.modoleListData.result.list,
                    len = arr.length;
                for(var i=0; i<len; i++){
                  arr[i]["drt_switch"+i] = arr[i].drt_switch;
                }
              })()
          }
          if(data.errcode > 0){
              alert(data.errmsg);
          }
      }, function (error) {
          console.log(error)
      })
    }

    fetchReportList({page:1});

    $scope.deleteItem = function(report){
      if(window.confirm('确定要删除该报告类型吗？\n删除后不可恢复！')){
        var index = $scope.modoleListData.result.list.indexOf(report);
        DailyReportApi.delReportList({
          drt_id : $scope.modoleListData.result.list[index].drt_id
        }).then(function(data){
          if (data.errcode==0) {
            //$scope.modoleListData.result.list.splice(index,1);
            //window.location.reload();
            fetchReportList($scope.reportQueryParams);
          }
        })
      }
    }

    $scope.isOpen = function(s){
      var index = $scope.modoleListData.result.list.indexOf(s);
      var val = $scope.modoleListData.result.list[index].drt_switch;
      var id = $scope.modoleListData.result.list[index].drt_id;
      if(val==0){
        if(!window.confirm('确定要立即禁用该报告类型吗？禁用后，手机端无法使用该类型模板')){
          $scope.modoleListData.result.list[index].drt_switch=1;
        }else{
          DailyReportApi.enableSwitch({
            drt_id:id,
            drt_switch:val
          });
        }
      }else{
        if(!window.confirm('确定要立即开启该报告类型吗？')){
          $scope.modoleListData.result.list[index].drt_switch=0;
        }else{
          DailyReportApi.enableSwitch({
            drt_id:id,
            drt_switch:val
          });
        }
      }
    }
  }])
})(angular.module('app.modules.dailyreporttemplate'));
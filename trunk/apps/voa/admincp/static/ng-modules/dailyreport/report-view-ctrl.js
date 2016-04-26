(function(app){
  app.controller('reportViewCtrl',['$rootScope','$scope','$location','DailyReportApi','Page',function($rootScope,$scope,$location,DailyReportApi,Page){
    /*改变标头*/
    $('#sub-navbar').find("h1").html("<i class=\"fa fa-eye page-header-icon\"><\/i>&nbsp;&nbsp;报告详情");
    var curPage = "";
    var reportId = $location.search().id;

    DailyReportApi.getAdminReport({
      dr_id : reportId
    }).then(function(data){
      if(data.errcode==0){
        var cData = data.result;
        cData.dr_reporttime = cData.dr_reporttime*1000;
        $scope.detailMsg = cData;
      }
    })

    $rootScope.dailyreportView = $rootScope.dailyreportMain;

    $scope.repList = [];
    
    $scope.viewType = $location.search().type!=2 ? 1 : 2;

    $scope.changeViewType = function(num){
      $scope.viewType = num;
      if($location.search().type==undefined){
        location.hash = location.hash + '&type=' + num;
      }else{
        var reg = /type=\d*/gi;
        location.hash = location.hash.replace(reg,"type="+num);
      }
    }

    $scope.getReportPage = function (page) {
        $scope.repList = [];
        $scope.reportQueryParams.page = page;
        fetchReportList($scope.reportQueryParams);
    };

    function fetchReportList(params){
      $scope.reportQueryParams = params;
      params.dr_id = reportId;
      DailyReportApi.getAdminReportComments(params).then(function (data) {
          if(data.errcode == 0){
              $scope.modoleListData = data;
              $scope.reportList = data.result.list;
              $scope.comment_count=data.result.count;
            //获取时间戳
              var wData = data.result.list;
              for(var i=0; i<wData.length; i++){
                  wData[i].drp_created *= 1000;
              }
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
          }else{
              $scope.comment_count=0;
              $scope.reportList = [];
          }
      }, function (error) {
          console.log(error)
      })
    }
    fetchReportList({page:1});
    $scope.remove = function(item){
      
      var index = $scope.reportList.indexOf(item);
      var id = $scope.reportList[index].drp_id;
      if(window.confirm('确定要删除该条评论吗？')){
      DailyReportApi.delAdminReportComment({
        drp_id : id
      }).then(function(data){
        if(data.errcode==0){
          //$scope.reportList.splice(index,1);
          //window.location.reload();
          fetchReportList($scope.reportQueryParams);
        }
      })
    }}

    $scope.reportDetail = function(){
      if(window.confirm('确定要删除该条报告吗？\n删除后手机端将一并删除，且不可恢复')){
        DailyReportApi.delWorkReportItem({
          dr_id : reportId
        }).then(function(data){
          if(data.errcode == 0){
            location.href="#/app/page/dailyreport/main";
          }
        })
      }
    }

  }]);
  app.filter('isTextx',function(){
      return function(text){
          return /^(date|time|dateandtime|text|textarea|radio|checkbox|number)$/.test(text);
      }
  });
})(angular.module('app.modules.dailyreport'));
(function(app){
  app.controller('reportCtrl',['$rootScope','$scope','$location','Page','DailyReportApi',function($rootScope,$scope,$location,Page,DailyReportApi){
    $('#sub-navbar').find("h1").html("<i class=\"fa fa-list page-header-icon\"><\/i>&nbsp;&nbsp;报告列表");
    var curPage = "";
    $scope.reportListData = [];
    $scope.reportTypeData = [{ "id": "0","name": "全部"}];
    $scope.report_type = $scope.reportTypeData[0].id;

      if($rootScope.dailyreportView==undefined){
        $scope.export = "/Dailyreport/Apicp/Dailyreport/Export";
        $rootScope.dailyreportMain = {};
        fetchReportList({page:1});
      }else{
        $scope.export = "/Dailyreport/Apicp/Dailyreport/Export";
        $rootScope.dailyreportMain = $rootScope.dailyreportView;
        $scope.submitter = $rootScope.dailyreportMain.submitter;
        $scope.report_type = $rootScope.dailyreportMain.report_type ? $rootScope.dailyreportMain.report_type : '0';
        $scope.forwarded = $rootScope.dailyreportMain.forwarded;
        $scope.receiver = $rootScope.dailyreportMain.receiver;
        $('#id_begin_time').val($rootScope.dailyreportMain.legalStartDate);
        $('#id_end_time').val($rootScope.dailyreportMain.legalEndDate);
        params = {
          page:1,//请求的页码，
          drt_type:$scope.report_type,
          submitter:$scope.submitter || "",
          start_date:isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : Date.parse(new Date($('#id_begin_time').val().replace(/-/gi,'/')+" 00:00:00"))/1000,//unix时间戳
          end_date:isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : Date.parse(new Date($('#id_end_time').val().replace(/-/gi,'/') + " 23:59:59"))/1000,
          receiver:$scope.receiver || "",
          forwarded:$scope.forwarded || ""
        }

        ;(function(){
          var str = "?";
          for(var i in params){
            if(i=="forwarded"){
              str += (i + "=" + params[i]);
            }else{
              str += (i + "=" + params[i] + "&");
            }
          }
          $scope.export += str;
        })();

        fetchReportList(params);
      }

    /*$scope.$watch('submitter',function(){
      $rootScope.dailyreportMain['submitter'] = $scope.submitter;
    },true)

    $scope.$watch('report_type',function(){
      $rootScope.dailyreportMain['report_type'] = $scope.report_type;
    },true)

    $scope.$watch('forwarded',function(){
      $rootScope.dailyreportMain['forwarded'] = $scope.forwarded;
    },true)

    $scope.$watch('receiver',function(){
      $rootScope.dailyreportMain['receiver'] = $scope.receiver;
    },true)

    $scope.$watch('legalStartDate',function(){
      $rootScope.dailyreportMain['legalStartDate'] = $scope.legalStartDate;
    },true)

    $scope.$watch('legalEndDate',function(){
      $rootScope.dailyreportMain['legalEndDate'] = $scope.legalEndDate;
    },true)*/

    DailyReportApi.getReportListType({}).then(function(data){
    	if(data.errcode == 0){
    		for(var i=0; i<data.result.length; i++){
    			var item = data.result[i];
    			var o = {};
    			o.id = item.drt_id;
    			o.name = item.drt_name;
    			$scope.reportTypeData.push(o);
    		}
    	}
    });

    $scope.changeDate = function(){

    }

    $scope.getReportPage = function (page) {
        $scope.reportListData = [];
        $scope.reportQueryParams.page = page;
        fetchReportList($scope.reportQueryParams);
    };

    function fetchReportList(params){
      $scope.reportQueryParams = params;
      DailyReportApi.getWorkReportList(params).then(function (data) {
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
              })();
          }
          if(data.errcode > 0){
              alert(data.errmsg);
          }
      }, function (error) {
          console.log(error)
      })
    }

    $scope.delReportItem = function(item){
      if(window.confirm('确定要删除该条报告吗？\n删除后手机端记录将一并删除，且不可恢复')){
      	var index = $scope.modoleListData.result.list.indexOf(item);
      	var drtId = $scope.modoleListData.result.list[index].dr_id;
      	DailyReportApi.delWorkReportItem({
      		dr_id : drtId
      	}).then(function(data){
      		if(data.errcode == 0){
      			//$scope.modoleListData.result.list.splice(index,1);
            //window.location.reload();
            fetchReportList($scope.reportQueryParams);
      		}
      	})
      }
    }

    $scope.reportSearch = function($event){
      if($event.keyCode==13 || $event.type=="click"){
        params = {
          page:1,//请求的页码，
          drt_type:$scope.report_type,
          submitter:$scope.submitter || "",
          start_date:isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : Date.parse(new Date($('#id_begin_time').val().replace(/-/gi,'/')+" 00:00:00"))/1000,//unix时间戳
          end_date:isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : Date.parse(new Date($('#id_end_time').val().replace(/-/gi,'/') + " 23:59:59"))/1000,
          receiver:$scope.receiver || "",
          forwarded:$scope.forwarded || ""
        }


        $rootScope.dailyreportMain['submitter'] = $scope.submitter;
        $rootScope.dailyreportMain['report_type'] = $scope.report_type;
        $rootScope.dailyreportMain['forwarded'] = $scope.forwarded;
        $rootScope.dailyreportMain['receiver'] = $scope.receiver;
        $rootScope.dailyreportMain['legalStartDate'] = $('#id_begin_time').val();
        $rootScope.dailyreportMain['legalEndDate'] = $('#id_end_time').val();
        ;(function(){
          var str = "?";
          for(var i in params){
            if(i=="forwarded"){
              str += (i + "=" + params[i]);
            }else{
              str += (i + "=" + params[i] + "&");
            }
          }
          $scope.export += str;
        })();

        fetchReportList(params);
      }
    }

    $scope.clearSearch = function(){
      /*params = {
        page:1,//请求的页码，
        drt_type:$scope.reportTypeData[0].id,
        submitter:"",
        start_date:"",
        end_date:"",
        receiver:"",
        forwarded:""
      }

      ;(function(){
        var str = "?";
        for(var i in params){
          if(i=="forwarded"){
            str += (i + "=" + params[i]);
          }else{
            str += (i + "=" + params[i] + "&");
          }
        }
        $scope.export += str;
      })();

      fetchReportList(params);

      $scope.report_type = "0";
      $scope.submitter = "";
      $scope.legalStartDate = "";
      $scope.legalEndDate = "";
      $scope.receiver = "";
      $scope.forwarded = "";
      $('#id_begin_time').val("");
      $('#id_end_time').val("");*/
      window.location.reload();
    }
    
  }])
})(angular.module('app.modules.dailyreport'));
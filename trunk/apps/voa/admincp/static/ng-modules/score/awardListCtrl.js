(function(app) {
    app.controller("awardListCtrl", ["$rootScope","$scope","ScoreApi", "Page", function($rootScope,$scope,ScoreApi,Page) {
        
        if($rootScope.scoreApplication==undefined){
          $scope.export = "/Score/Apicp/ScoreAward/exchangeLogOutputCsv";
          $rootScope.scoreAwardExchange = {};
          fetchReportList({page:1});
          $scope.status = 0;
        }else{
          $scope.export = "/Score/Apicp/ScoreAward/exchangeLogOutputCsv";
          $rootScope.scoreAwardExchange = $rootScope.scoreApplication;

          $scope.orderNo = $rootScope.scoreAwardExchange.order_num;
          $scope.status = $rootScope.scoreAwardExchange.status == undefined ? 0 : $rootScope.scoreAwardExchange.status;
          $('#id_begin_time').val($rootScope.scoreAwardExchange.legalStartDate);
          $('#id_end_time').val($rootScope.scoreAwardExchange.legalEndDate);

          var params = {
              page : 1,
              start_date:isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : Date.parse(new Date($('#id_begin_time').val().replace(/-/gi,'/')+" 00:00:00"))/1000,//unix时间戳
              end_date:isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : Date.parse(new Date($('#id_end_time').val().replace(/-/gi,'/') + " 23:59:59"))/1000,
              order_num : $scope.orderNo || "",
              status : $scope.status
          };

          ;(function(){
            var str = "?";
            for(var i in params){
              if(i=="status"){
                str += (i + "=" + params[i]);
              }else{
                str += (i + "=" + params[i] + "&");
              }
            }
            $scope.export += str;
          })();

          fetchReportList(params);
        }

        $scope.getReportPage = function (page) {
            $scope.reportListData = [];
            $scope.reportQueryParams.page = page;
            fetchReportList($scope.reportQueryParams);
        };

        function fetchReportList(params){
          $scope.reportQueryParams = params;
          ScoreApi.getAwardExchangeList(params).then(function (data) {
              if(data.errcode == 0){
                  $scope.reportList = data.result.list;
                  $scope.resultCount = data.result.count;
                  // 分页
                  curPage = params.page;
                  $scope.polerPaginationCtrl.reset({
                      total:data.result.count,
                      pages:data.result.page,
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

        $scope.search = function($event){
            if($event.keyCode==13 || $event.type=="click"){
              var startTime = isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : Date.parse(new Date($('#id_begin_time').val().replace(/-/gi,'/')+" 00:00:00"))/1000;//unix时间戳;
              var endTime = isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : Date.parse(new Date($('#id_end_time').val().replace(/-/gi,'/') + " 23:59:59"))/1000;
              var orderNo = $scope.orderNo;
              var status = $scope.status;

              var params = {
                  page : 1,
                  start_date : startTime,
                  end_date : endTime,
                  order_num : orderNo || "",
                  status : status
              };

              $rootScope.scoreAwardExchange['order_num'] = $scope.orderNo;
              $rootScope.scoreAwardExchange['status'] = $scope.status;
              $rootScope.scoreAwardExchange['legalStartDate'] = $('#id_begin_time').val();
              $rootScope.scoreAwardExchange['legalEndDate'] = $('#id_end_time').val();

              ;(function(){
                var str = "?";
                for(var i in params){
                  if(i=="status"){
                    str += (i + "=" + params[i]);
                  }else{
                    str += (i + "=" + params[i] + "&");
                  }
                }
                $scope.export += str;
              })();
              fetchReportList(params);
            }
        };
    }]);
})(angular.module("app.modules.awardExchange"));

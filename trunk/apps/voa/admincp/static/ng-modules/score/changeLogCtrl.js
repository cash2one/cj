(function(app) {
    app.controller("changeLogCtrl", ["$scope","ScoreApi", "Page", function($scope,ScoreApi,Page) {
        $scope.integralType = 0;
        $scope.export = "/Score/Apicp/Score/outputScoreLogCsv";
        var curPage = '';
        	fetchReportList({page:1});

        $scope.getReportPage = function (page) {
	        $scope.reportListData = [];
	        $scope.reportQueryParams.page = page;
	        fetchReportList($scope.reportQueryParams);
	    };

	    function fetchReportList(params){
	      $scope.reportQueryParams = params;
	      ScoreApi.getScoreLogList(params).then(function (data) {
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
	          console.log(error);
              alert('网络问题,请再试一次')
	      })
	    }

        $scope.search = function($event){
        	if($event.keyCode==13 || $event.type=="click"){
        		var params = {
	            	page : 1,
	            	username : $scope.userName || "",
	            	cd_name : $scope.cdName || "",
	            	type : $scope.integralType
	            };

	            ;(function(){
	                var str = "?";
	                for(var i in params){
	                  if(i=="type"){
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
    }]);
})(angular.module("app.modules.changeLog"));

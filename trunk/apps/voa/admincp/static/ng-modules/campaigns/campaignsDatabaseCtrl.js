(function(app,window){
    app.controller('databaseCtrl',['$scope','CampaignsApi','Page', function($scope,CampaignsApi,Page){
        $scope.types =[];

        (function(){
        	fetchReportList({page:1});
        	getSettingList();
        })();

       function getSettingList(){
           CampaignsApi.CampaignSettingList().then(function(res){
               $scope.types = res.result;
           })
       }
        

        $scope.getReportPage = function (page) {
	        $scope.reportListData = [];
	        $scope.reportQueryParams.page = page;
	        fetchReportList($scope.reportQueryParams);
	    };

	    function fetchReportList(params){
	      $scope.reportQueryParams = params;
	      CampaignsApi.dataCenter(params).then(function (data) {
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

        $scope.search = function(){
            var params = {
            	page : 1,
            	username : $scope.userName,
            	cd_name : $scope.cdName,
            	type : $scope.integralType
            }
            ScoreApi.getScoreLogList(params).then(function(data){
            	$scope.reportList = data.result.list;
            	$scope.resultCount = data.result.count;
            })
        }
    }]);
})(angular.module('app.modules.campaignsDatabase'),window);

/**
魏世超
*/

(function(app){
	app.controller("shareList",["$scope","ShareApi",'Page',function($scope,ShareApi,Page){


      $scope.seleted = 0; //selected初始值
      //select下拉框数组
      $scope.selectHig = [{"id":0,"name":"全部"},{"id":1,"name":"审核中"},{"id":2,"name":"已通过"},{"id":3,"name":"已驳回"}]
      //列表状态数组
      $scope.statusT = [" ","审核中","已通过","已驳回"];

    
        // 分页开始
        var curPage = "";
        $scope.reportList= [];

        $scope.getReportPage = function (page) {
        
            $scope.reportQueryParams.page = page;
            fetchReportList($scope.reportQueryParams);
        };


        function fetchReportList(params){
              
              $scope.reportQueryParams = params;
              ShareApi.confQuery(params).then(function (data) {
  
                   
                  if(data.errcode == 0){
                      console.log(data)
                      $scope.reportResult = data.result;
                      $scope.reportList = data.result.list;
                      // console.log($scope.reportList);
                      // 分页
                      curPage = params.page;
                      $scope.polerPaginationCtrl.reset({
                          total:data.result.count,
                          pages:data.result.pages,
                          curPage:params.page
                      });
                      
                  }else if(data.errcode > 0){
                      alert(data.errmsg);
                  }
              }, function (error) {
                  console.log(error)
              })
            }


        fetchReportList({page:1});//初始化

        // 分页结束
      
      // 搜索开始
        $scope.searchInfo = function(tit){
          
            var _params = {
                page: 1,
                title: tit,
                status:$scope.seleted
                
            };
           fetchReportList(_params);
        };

      // 搜索结束


	}]);
})(angular.module('app.modules.shareList'));
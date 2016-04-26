(function(app) {
    app.controller("matchListCtrl", ["$scope", "ScoreApi", "Page", function($scope, ScoreApi, Page) {
        (function() {
            fetchReportList({ page: 1 });
        })();

        $scope.getReportPage = function(page) {
            $scope.reportListData = [];
            $scope.reportQueryParams.page = page;
            fetchReportList($scope.reportQueryParams);
        };
        // @Chan  
        // http://cj.vchangyi.org/admincp/office/score/setup/pluginid/47/#/app/page/score/match_arrangement
        //功能：编辑
        $("#score_setup_module").on("click", ".editor", function(event) {
          sessionStorage.setItem("newPrizeHref","editor");
            sessionStorage.setItem("editorId",$(this).data("editor-id"));
        });

        $scope.toggle = function($event,item){
            var title = item.status==1 ? "禁用" : "启用";
            bootbox.dialog({
                message: "是否" + title + "该奖品",
                title: "提示：",
                buttons: {
                    Cancel: {
                        label: "暂不"　+　title,
                        className: "btn-default",
                        callback: function() {}
                    },
                    OK: {
                        label: "仍要" +　title,
                        className: "btn-primary",
                        callback: function() {
                            if(item.status==1){
                                var flag = 0;
                            }else if(item.status==0){
                                var flag = 1;
                            }
                            ScoreApi.changeAwardStatus({award_id:item.award_id,status:flag}).then(function(data){
                                if(data.errcode==0){
                                    if(flag==0){
                                        item.status = 0;
                                    }else if(flag==1){
                                        item.status = 1;
                                    }
                                }
                            })
                        }
                    }
                }
            });
        };

        function fetchReportList(params) {
            $scope.reportQueryParams = params;
            ScoreApi.getAwardList(params).then(function(data) {
                console.log(data);
                if (data.errcode == 0) {
                    $scope.reportList = data.result.list;
                    $scope.resultCount = data.result.count;
                    // 分页
                    console.log('list', $scope.reportList);
                    curPage = params.page;
                    $scope.polerPaginationCtrl.reset({
                        total: data.result.count,
                        pages: data.result.page,
                        curPage: params.page
                    });
                }
                if (data.errcode > 0) {
                    alert(data.errmsg);
                }
            }, function(error) {
                console.log(error)
            })
        }
    }]);
})(angular.module("app.modules.setUp"));

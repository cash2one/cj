(function(app){
    app.controller('detailCtrl',['$scope' ,'$window','$location','CampaignsApi',
        function($scope,$window,$location,CampaignsApi){
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-file-text-o page-header-icon\"><\/i>&nbsp;&nbsp;活动详情");
            function showDetail(){
                var id = $location.search().id
                CampaignsApi.campaignsDetail({id:id}).then(function(res){
                    if(res.result!=0){
                        $scope.subject = res.result['subject'];
                        $scope.title = res.result['title'];
                        $scope.begintime = formatTime(res.result['begintime']) ;
                        $scope.overtime = formatTime(res.result['overtime']) ;
                        $scope.share = res.result['share']?res.result['share']:0;
                        $scope.hits = res.result['hits']?res.result['hits']:0;
                        $scope.id = res.result['id'];
                        $scope.content = res.result['content'];
                        $("#content").html($scope.content);
                        $scope.tops =res.result['tops']
                    }else{
                        alert(res.errmsg);
                    }
                });
            }
            function formatTime(time){
                var date = new Date();
                date.setTime(parseInt(time)*1000);
                var year = date.getFullYear();
                var month = date.getMonth()+1;
                month = month<10 ? "0"+month:month ;
                var day = date.getDate();
                var dd = year+"-"+month+"-"+day ;
                var hh = date.getHours();
                var mm = date.getMinutes();
                mm<10 ?'0'+mm :mm;
                var tt = hh+":"+mm ;
                return dd+" "+tt ;
            }
            showDetail();
            $scope.back = function(){
                $window.location.href='#/app/page/campaigns/list';
            }
        }]);
})(angular.module('app.modules.campaignsList'));
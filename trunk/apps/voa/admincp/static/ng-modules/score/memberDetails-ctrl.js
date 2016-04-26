/**
 * Created by liangpure on 2016/4/14.
 */

(function(app){
    app.controller('memberDetailsCtrl',['$rootScope','$scope','$location', 'Page', 'IntegralApi',function($rootScope,$scope,$location,Page,IntegralApi){
        var m_uid = $location.search().m_uid;
        console.log(m_uid);
        //获取成员详细信息

        $rootScope.scoreMenberDetail = $rootScope.scoreMenberList;

        var getMemberInfo = function(){
            IntegralApi.getMemberDetails({uid: m_uid}).then(function(data){
                if(data.errcode == 0){
                    $scope.memberDetails = data.result.detail;
                }else if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function(error){
                console.log(error);
            });
        };
        getMemberInfo();

        $scope.addData = {};
        $scope.reduceData = {};
        //增减积分
        $scope.batchAdd = function(){
            if(isNaN($scope.addData.score) || $scope.addData.notes.length == 0){
                return;
            }

            var _params = {
                uids: [m_uid],
                score: Number($scope.addData.score),
                desc: $scope.addData.notes
            };
            //console.log(getSelected());

            IntegralApi.batchAdd(_params).then(function(data){
                if(data.errcode == 0){
                    console.log(data);
                    $scope.addData = {};
                    angular.element(document.getElementById('increase-jf')).modal('hide');
                    getMemberInfo();
                    //更新积分明细
                    $scope.getPage(1);
                }
            }, function(error){
                console.log(error);
                alert('网络问题，请再试一次');
            });
        };


        $scope.batchReduce = function(){
            if(isNaN($scope.reduceData.score) || $scope.reduceData.notes.length == 0){
                return;
            }

            var _params = {
                uids: [m_uid],
                score: -Number($scope.reduceData.score),
                desc: $scope.reduceData.notes
            };
            console.log(_params);
            IntegralApi.batchReduce(_params).then(function(data){
                //console.log('reduce ',data);
                if(data.errcode == 0){
                    console.log(data);
                    $scope.reduceData = {};
                    angular.element(document.getElementById('reduce-jf')).modal('hide');
                    getMemberInfo();
                    //更新积分明细
                    $scope.getPage(1);
                }
            }, function(error){
                console.log(error);
                alert('网络问题，请再试一次');
            });
        };
        //分页
        var curPage = '';
        $scope.listData = [];
        $scope.getPage = function(page){
            $scope.listData = [];
            $scope.queryParams.page = page;
            fetchList($scope.queryParams);
        };
        function fetchList(params){
            $scope.queryParams = params;
            IntegralApi.getMemberScoreList(params).then(function(data){
                if(data.errcode == 0){
                    // $scope.moduleListData = data;
                    $scope.listData = data.result.list;
                    //分页
                    console.log($scope.listData);
                    curPage = params.page;
                    $scope.polerPaginationCtrl.reset({
                        total: data.result.count,
                        pages: data.result.page,
                        curPage: params.page
                    });
                }else if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function(error){
                console.log(error);
            })
        }
        fetchList({
            uid: m_uid,
            page: 1});

        //分页
        var curPageOth = '';
        $scope.listDataOth = [];
        $scope.getPageOth = function(page1){
            $scope.listDataOth = [];
            $scope.queryParamsOth.page = page1;
            fetchListOth($scope.queryParamsOth);
        };
        function fetchListOth(params){
            $scope.queryParamsOth = params;
            IntegralApi.getMemberExchangeList(params).then(function(data){
                if(data.errcode == 0){
                    // $scope.moduleListData = data;
                    $scope.listDataOth = data.result.list;
                    //分页
                    //console.log(data);
                    console.log('faffa :', data.result.count,data.result.page);
                    curPageOth = params.page;
                    $scope.polerPaginationCtrl1.reset({
                        total: data.result.count,
                        pages: data.result.page,
                        curPage: params.page
                    });
                }else if(data.errcode > 0){
                    alert(data.errmsg);
                }
            }, function(error){
                console.log(error);
            })
        }
        fetchListOth({
            uid: m_uid,
            page: 1});


    }])
})(angular.module('app.modules.memberList'));
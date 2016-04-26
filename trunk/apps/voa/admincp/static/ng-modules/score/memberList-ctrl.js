/**
 * Created by liangpure on 2016/4/13.
 */
(function(app){
    app.controller('memberListCtrl',['$rootScope','$scope','$location', 'Page', 'IntegralApi',function($rootScope,$scope,$location,Page,IntegralApi){


        var curPage = '';
        $scope.search = {};
        $scope.selectList = {};
        var hasSelected = [];
        $scope.batchAddData = {};
        $scope.batchReduceData = {};
        var getSelected;
//分页
        $scope.getPage = function(page){
            $scope.selectAllTag = false;
            $scope.queryParams.page = page;
            fetchList($scope.queryParams);
        };

        $scope.up = function(){
            fetchList({page:$scope.queryParams.page,order:1});
        }

        $scope.down = function(){
            fetchList({page:$scope.queryParams.page,order:0});
        }

        function fetchList(params){
            $scope.queryParams = params;
            IntegralApi.memberList(params).then(function(data){
                if(data.errcode == 0){
                   // $scope.moduleListData = data;
                    $scope.listData = data.result.list;
                    $scope.resultCount = data.result.count;
                    //分页
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
                alert('请检查您的输入是否正确或再试一次');
            })
        }
        (function() {
            if($rootScope.scoreMenberDetail==undefined){
                $rootScope.scoreMenberList = {};
                fetchList({page:1});
              }else{
                $rootScope.scoreMenberList = $rootScope.scoreMenberDetail;
                $scope.search.name = $rootScope.scoreMenberList.username;
                $scope.search.department = $rootScope.scoreMenberList.cp_name;
                params = {
                  page:1,//请求的页码，
                  cp_name:$scope.search.department,
                  username:$scope.search.name
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

                fetchList(params);
            }
        })();
//搜索方法
        $scope.searchInfo = function(params){
            var _params = {
                page: 1,
                username: params.name,
                cp_name: params.department
            };

            $rootScope.scoreMenberList['username'] = params.name;
            $rootScope.scoreMenberList['cp_name'] = params.department;
            fetchList(_params);
        };
        $scope.getAllInfo = function(){
            fetchList({page:1});
            $scope.search.name = '';
            $scope.search.department = '';
        };
        //全选
        $scope.selectAllTag = false;
        $scope.selectAll = function(){
            if($scope.selectAllTag){
                $scope.listData.forEach(function(item, index){
                    $scope.selectList[item.m_uid] = true;
                })
            }else{
                //取消全选时 删除之前选择的内容
                $scope.listData.forEach(function(item, index){
                    delete $scope.selectList[item.m_uid];
                })
            }
        };

        //批量增减积分

        getSelected = function(){
            hasSelected = [];
            for(var prop in $scope.selectList){
                if($scope.selectList[prop] && $scope.selectList.hasOwnProperty(prop)){
                    hasSelected.push(prop);
                }
                console.log($scope.selectList)
            }

            return hasSelected;
        };
        $scope.batchAdd = function(){
            if(isNaN($scope.batchAddData.score) || $scope.batchAddData.notes.length == 0){
                 return;
            }

             var _params = {
                 uids: getSelected(),
                 score: Number($scope.batchAddData.score),
                 desc: $scope.batchAddData.notes
             };
            console.log(getSelected());

            IntegralApi.batchAdd(_params).then(function(data){
                if(data.errcode == 0){
                    console.log(data);
                    $scope.batchAddData = {};
                    angular.element(document.getElementById('increase-jf')).modal('hide');
                    $scope.getPage(curPage);
                    //重置勾选内容
                    $scope.selectList = {}
                }
            }, function(error){
                console.log(error);
                alert('请再试一次');
            });
        };
        
        //console.log(angular.element(document.querySelectorAll('.m-close')));
        $scope.batchReduce = function(){
            if(isNaN($scope.batchReduceData.score) || $scope.batchReduceData.notes.length == 0){
                return;
            }

            var _params = {
                uids: getSelected(),
                score: -Number($scope.batchReduceData.score),
                desc: $scope.batchReduceData.notes
            };
            //console.log(_params);
            IntegralApi.batchReduce(_params).then(function(data){
                //console.log('reduce ',data);
                if(data.errcode == 0){
                    console.log(data);
                    $scope.batchReduceData = {};
                    angular.element(document.getElementById('reduce-jf')).modal('hide');
                    $scope.getPage(curPage);
                    //重置勾选内容
                    $scope.selectList = {}
                }
            }, function(error){
                console.log(error);
                alert('网络问题，请再试一次');
            });
        };
        //检查是否有选中
        $scope.inspectedBox = function(ev){
            console.log($scope.selectList);
            if(getSelected().length == 0){
                ev.preventDefault();
                ev.stopPropagation();
                alert('请先选择需要修改积分的成员');
            }
        }
    }])
})(angular.module('app.modules.memberList'));
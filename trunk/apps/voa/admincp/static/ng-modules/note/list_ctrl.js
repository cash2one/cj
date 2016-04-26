//课堂列表

(function(app){
	app.controller('ListCtrl',['$scope','Page','NoteApi',function($scope,Page,NoteApi){


		/*NoteApi.getList({}).then(function(data){
			if(data.errcode == 0){
				$scope.list=data.result.list;
			}
		})*/

		// 分页开始
		var curPage = "";
		$scope.reportListData = [];
		$scope.search = {};
		$scope.seleted = '0';
		$scope.selectList = {};
        var hasSelected = [];
        bootbox.setDefaults("locale", "zh_CN");

		$scope.getReportPage = function (page) {
			$scope.reportListData = [];
			$scope.reportQueryParams.page = page;
			fetchReportList($scope.reportQueryParams);
		};


		function fetchReportList(params){
			$scope.reportQueryParams = params;
			NoteApi.getList(params).then(function (data) {
				if(data.errcode == 0){
					// $scope.modoleListData = data;
					$scope.selectList = {};
                    $scope.chkall = false;
					$scope.list = data.result.list;
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

		fetchReportList({page:1});

		// 分页结束


		//搜索
		$scope.searchInfo = function(params){
			var _params = {
				page: 1,
				cid: params.cid,
				title:params.title,
				m_username:params.m_username,
				course:params.course,
				start_time:params.start_time,
				end_time:params.end_time,
				cid : $scope.seleted
			};
			fetchReportList(_params);
		};
		$scope.getAllInfo = function(){
			/*fetchReportList({page:1});
			$scope.search.cid = '';
			$scope.search.title = '';
			$scope.search.m_username = '';
			$scope.search.course = '';
			$scope.search.start_time = '';
			$scope.search.end_time = '';*/
			window.location.reload();
		}

		function getSelected(){
            hasSelected = [];
            for(var prop in $scope.selectList){
                if($scope.selectList[prop] && $scope.selectList.hasOwnProperty(prop)){
                    hasSelected.push(prop);
                }
            }
            return hasSelected;
        }

        $scope.checkBoxAll = function(){
            for(var i in $scope.selectList){
                if($scope.chkall == true){
                    $scope.selectList[i] = true;
                }else{
                    $scope.selectList[i] = false;
                }
            }
        }

		//删除
		$scope.delItem = function(id){
			bootbox.dialog({
                message: '是否删除笔记？',
                title: '提示：',
                buttons: {
                    Cancel: {
                        label: '取消',
                        className: "btn-default"
                    },
                    OK: {
                        label: '删除',
                        className: "btn-primary",
                        callback: function() {
                            var ids = id==undefined ? getSelected():[id];
                            NoteApi.deleteNote({note_id:ids}).then(function(data){
                                if(data.errcode == 0){
                                    fetchReportList($scope.reportQueryParams);
                                }
                            })
                        }
                    }
                }
            });
		}

       //批量删除
		$scope.BatchRemove = function(item){
			if(window.confirm('是否确定删除此内容')){
				node_inds = [];
				var drtId = item.note_id;
				node_ids.push(drtId);
			}
			NoteApi.deleteNote({
				node_id :node_ids
			}).then(function(data){
				if(data.errcode == 0){
					fetchReportList($scope.reportQueryParams);
				}
			})
		}


		/*
		* 下拉框
		* */
		$scope.selectd = 0;
		NoteApi.getCateSearch().then(function(data){
			console.log(data)
			if(data.errcode==0){
				var res = data.result.cates;
				var selType = [{id:"0",title:"请选择",level:"0"}];
				console.log(res)
				for(var i=0; i<res.length; i++){
					var item = res[i];
					var o = {};
					o.id = item.id;
					o.title = item.title;
					o.level = "0"
					selType.push(o);
					if(item.sub && item.sub.length > 0){
						for(var j=0; j<item.sub.length; j++){
							var s = {};
							var itemChild = item.sub[j];
							s.id = itemChild.id;
							s.title = '|---' + itemChild.title;
							s.level = "1"
							selType.push(s);
						}
					}
					
				}
				console.log(selType)
				$scope.selType = selType;
				//$scope.selectD = data.result.cates;

			}
		})

	}]);
})(angular.module('app.modules.notelist'));




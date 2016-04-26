/**
魏世超
*/
(function(app){
	app.controller("shareDetail",["$scope","$location","ShareApi",function($scope,$location,ShareApi){
		bootbox.setDefaults("locale","zh_CN");//设置中文
		var reportId = $location.search().material_id; //获取详情页ID
		
		$scope.resDateil="";
		//输出详情页内容
		ShareApi.goDetail({
			"material_id" : reportId
		}).then(function(data){
			if(data.errcode==0){
				$scope.resDateil = data.result;

			}

		})

		var reg = /^\s+$/

		//驳回素材按钮
		$scope.proMpt = function(obj){
			bootbox.prompt({ 
			    title: "驳回理由",
			    message: "必须输入理由", 
			    callback: function(result){ 
			    	var desc = obj+result;
			    	if(result==""||reg.test(result)){
			    		alert("必须得输入原因");
			    		return;
			    	}else if(result){
			    		ShareApi.getBohui(desc).then(function(data){
			    			if(data.errcode==0){
				    			alert("已驳回");
				    		}else{
				    			alert("输入无效");
				    		}
				    		
				    	});	

			    	}
			    }
			})
		}
		

		//同意收录
		$scope.conFirm = function(obj){
			bootbox.confirm({ 
			    size: 'small',
			    message: "确定收录？", 
			    callback: function(result){ 
			    	if (result) {
				    	ShareApi.getBohui(obj).then(function(data){			    			
				    		if(data.errcode==0){
				    			alert("已收录");
				    		}else{
				    			alert("收录失败");
				    		}
				    		
				    	});			    		
			    	}

			    }
			})
		}




	}]);
})(angular.module('app.modules.shareList'));
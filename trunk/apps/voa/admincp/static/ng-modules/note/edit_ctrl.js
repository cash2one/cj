//编辑笔记


(function(app){
	app.controller('EditCtrl',['$scope','$location','$timeout','NoteApi',function($scope,$location,$timeout,NoteApi){

		$scope.tit="";
		$scope.resDateil="";
		 //文本编辑框
		 var opt={"toolbars":
        [["source","|","bold","italic","underline","removeformat","|",
            "forecolor","backcolor","insertorderedlist","insertunorderedlist",
            "fontfamily","fontsize","|","justifyleft","justifycenter",
            "justifyright","justifyjustify","|","link","unlink",
            "insertimage","insertvideo"

		]],
			"textarea":"content",
			"initialFrameHeight":300,
			"initialContent":"",
			"elementPathEnabled":false,
			"charset":"utf-8",
			"lang":"zh-cn",
			"autoClearinitialContent":false,
			"emotionLocalization":true,
			"pageBreakTag":"ueditor_page_break_tag"};
				UE.getEditor("myEditor",opt);


		$scope.reportId = $location.search().note_id;
		NoteApi.getNoteDetai({
			"note_id" : $scope.reportId
		}).then(function(data){
			console.log(data)
			if(data.errcode ==0 ){
				$scope.resDateil = data.result;
				$scope.addFileList = [];
				for(var i=0; i<data.result.detail.attachs.length; i++){
					var item = data.result.detail.attachs[i];
					var o = {};
					o.id = item.at_id;
	        		o.type = item.ext;
	        		o.name = item.at_filename;
	        		$scope.addFileList.push(o);
				}
				$scope.tit = data.result.detail.title;
				var ue = UE.getEditor('myEditor');
				ue.ready(function(){
					ue.setContent($scope.resDateil.detail.content, true);
				})
				$scope.$on('$destroy', function() {
	                ue.destroy();
	            });
			}
		})




// 删除附件
		$scope.attachDel = function(id){
			NoteApi.deleteAttach({
				note_id : $scope.reportId,
				at_id : id
			}).then(function(data){
				if(data.errcode==0){
					for(var i=0; i<$scope.addFileList.length; i++){
						var item = $scope.addFileList[i];
						if(id == item.id){
							$timeout(function(){
								$scope.addFileList.splice(i,1);
							})
							break;
						}
					}
				}
			})
		}



	//发送请求发布数据
		$scope.save = function(){
			NoteApi.editNote({
				note_id:$scope.reportId,
				title:$scope.tit,
				content:UE.getEditor('myEditor').getContent(),
				attachs : (function(){
					var arr = [];
					for(var i=0; i<$scope.addFileList.length; i++){
						arr.push($scope.addFileList[i].id);
					}
					return arr.join(",");
				})()
			}).then(function(data){
				location.href="#/app/page/note/list";
			})
		}

		function addFile(){
			$('#attachUpload').fileupload({
				dataType: 'json',
				url: '/admincp/api/attachment/upload/?file=file&is_attach=1',
				limitMultiFileUploads: 1,
				sequentialUploads: true,
				change: function (e, data) {
					for (var i = 0; i < data.files.length; i++) {
						var file = data.files[i];
						if(/^(image|audio)/.test(file.type)){
							if(file.size > 2000000){
								alert(file.name +　'文件超过大小限制');
				        		return false;
							}
						}else if(/^(video)/.test(file.type)){
							if(file.size > 10000000){
								alert(file.name +　'文件超过大小限制');
				        		return false;
							}
						}else if(/^(application)/.test(file.type)){
							if(file.size > 20000000){
								alert(file.name +　'文件超过大小限制');
				        		return false;
							}
						}else{
							if(data.files[i].size>30000000){
					        	alert('文件超过大小限制');
					        	return false;
					        }
						}
					}
		        	
		        },
				start: function (e, data) {
		        	$('#attach_progress .progress-bar').css('width','0%');
		        	$('#attach_progress').show();
		        },
		        fail : function(e,data){

		        	$('#attach_progress').hide();
		        },
		        done: function (e, data) {
		        	if(data.result.errcode == 0) {
		        		var d = data.result.result.list[0];
		        		var did = data.result.result.id;
		        		var fileSplit = d.name.split(".");
		        		var filetype = fileSplit[fileSplit.length - 1];
		        		var o = {};
		        		o.id = did;
		        		o.type = filetype;
		        		o.name = d.name;
		        		$timeout(function(){
		        			$scope.addFileList.push(o);
		        		})
		        	}

		        	$('#attach_progress').hide();
		        },
		        progressall: function (e, data) {
			        var progress = parseInt(data.loaded / data.total * 100, 10);
			        $('#attach_progress .progress-bar').css('width', progress + '%');
			    }
		    });
		}

		addFile();
	}]);

})(angular.module('app.modules.notelist'));

(function(app){
	app.factory('NoteApi',['ApiUtil',function(ApiUtil){
		return {
			//获取课程笔记列表
			getList:function(params){
				return ApiUtil.get('Note/Apicp/Note/get_list',params);
			},
			//删除课程笔记
			deleteNote:function(params){
				return ApiUtil.post('Note/Apicp/Note/delete_note',params);
			},
			//查看笔记详情
			getNoteDetai:function(params){
				return ApiUtil.get('Note/Apicp/Note/get_note_detail',params);
			},

			getCateList:function(params){
				return ApiUtil.get('Note/Apicp/Category/get_cate_list',params);
			},
			//获取课程分类（搜索页面）
			getCateSearch:function(params){
				return ApiUtil.get('Note/Apicp/Category/get_cate_search',params);
			},
			//提交编辑笔记
			editNote:function(params){
				return ApiUtil.post('Note/Apicp/Note/edit_note',params);
			},
			//删除附件
			deleteAttach:function(params){
				return ApiUtil.get('Note/Apicp/Note/delete_attach',params);
			},
			//添加课程笔记
			addNote:function(params){
				return ApiUtil.post('Note/Apicp/Note/add_note',params);
		}
		};
	}]);
})(angular.module('app.modules.api'));

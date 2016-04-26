(function (app) {
    app.factory('MemberApi',['ApiUtil',function(ApiUtil){
        return {
            // 人员列表
            memberList : function(params){
                return ApiUtil.get('PubApi/Apicp/Member/List',params);
            },
            // 人员详情
            memberView : function(params){
                return ApiUtil.get('PubApi/Apicp/Member/View',params);
            },
            // 删除人员(可多选)
            memberDelete : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Delete',params);
            },
            // 邀请人员
            memberInvite : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Invite',params);
            },
            // 移动人员
            memberMove : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Move',params);
            },
            // 启动或禁用人员
            memberBan : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Ban',params);
            },
            // 人员浏览权限
            memberBrowse : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Browse',params);
            },
            // 添加人员
            memberAdd : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Add',params);
            },
            // 编辑人员
            memberEdit : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Edit',params);
            },
            // 获取编辑人员信息
            getMemberEditInfo : function(params){
                return ApiUtil.get('PubApi/Apicp/Member/Getedit',params);
            },
            // 获取人员属性规则和敏感成员标签设置
            attrIndex : function(params){
                return ApiUtil.get('PubApi/Apicp/Attribute/Index',params);
            },
            // 保存自定义属性
            editField : function(params){
                return ApiUtil.post('PubApi/Apicp/Attribute/Edit',params);
            },
            // 部门列表接口
            departmentList : function(params){
                return ApiUtil.get('PubApi/Apicp/Department/List',params);
            },
            // 编辑部门初始化接口
            departmentInit : function(params){
                return ApiUtil.get('PubApi/Apicp/Department/Initial',params);
            },
            // 编辑部门/添加部门接口
            departmentAdd : function(params){
                return ApiUtil.post('PubApi/Apicp/Department/Post',params);
            },
            // 删除部门接口
            departmentDelete : function(params){
                return ApiUtil.post('PubApi/Apicp/Department/Delete',params);
            },
            // 标签列表接口
            labelList : function(params){
                return ApiUtil.get('PubApi/Apicp/Label/List',params);
            },
            // 新增标签接口
            labelAdd : function(params){
                return ApiUtil.post('PubApi/Apicp/Label/Add',params);
            },
            // 编辑标签初始化接口
            labelInit : function(params){
                return ApiUtil.get('PubApi/Apicp/Label/Initial',params);
            },
            // 编辑标签接口
            labelEdit : function(params){
                return ApiUtil.post('PubApi/Apicp/Label/Edit',params);
            },
            // 删除标签接口
            labelDelete : function(params){
                return ApiUtil.post('PubApi/Apicp/Label/Delete',params);
            },
            // 标签人员列表接口
            labelListMember : function(params){
                return ApiUtil.get('PubApi/Apicp/Label/ListMember',params);
            },
            // 添加人员到标签接口
            labelAddMember : function(params){
                return ApiUtil.post('PubApi/Apicp/Label/AddMember',params);
            },
            // 移除标签里的人接口
            labelDeleteMem : function(params){
                return ApiUtil.post('PubApi/Apicp/Label/DeleteMem',params);
            },
            // 人员导出接口
            memberDump : function(params){
                return ApiUtil.post('PubApi/Apicp/Member/Dump',params);
            }

        };
    }]);
})(angular.module('app.modules.api'));

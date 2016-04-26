(function (app) {
    app.factory('SignApi',['ApiUtil',function(ApiUtil){
        return {
            // 获取考勤设置数据
            confQuery : function(params){
                return ApiUtil.get('Sign/Apicp/SignSettingCp/config',params);
            },
            // 设置更新考勤 (签到 签退)
            updateSignIn : function(params){
                return ApiUtil.post('Sign/Apicp/SignSettingCp/update_sign',params);
            },
            // 修改外出考勤必须上传图片
            updateSwith : function(params){
                return ApiUtil.post('Sign/Apicp/SignSettingCp/update_swith',params);
            },
            // 修改微信菜单设置
            updateMenu : function(params){
                return ApiUtil.post('Sign/Apicp/SignSettingCp/update_wxcpmenu',params);
            },
            // 查询班次列表
            classList : function(params){
                return ApiUtil.get('Sign/Apicp/SignCp/List',params);
            },
            // 查询法定假日
            legalDates : function(params){
                return ApiUtil.get('Sign/Apicp/SignCp/getLegalDates',params);
            },
            // 保存排班
            addScheduling : function(params) {
                return ApiUtil.post('Sign/Apicp/SignScheduleCp/add', params);
            },
            // 保存排班
            modifyScheduling : function(params) {
                return ApiUtil.post('Sign/Apicp/SignScheduleCp/modify', params);
            },
            // 查询排班
            queryMemberClass : function(params){
                return ApiUtil.get('Sign/Apicp/SignScheduleCp/list',params);
            },
            // 获取默认全局考勤规则数据接口
            defult_rule : function(params){
                return ApiUtil.get('Sign/Apicp/SignSettingCp/defult_rule',params);
            },
            // 启用禁用状态
            controlStatus : function(params){
                return ApiUtil.post('Sign/Apicp/SignScheduleCp/enabled',params);
            },
            // 删除排班
            delMemberClass : function(params){
                return ApiUtil.post('Sign/Apicp/SignScheduleCp/delete',params);
            },
            // 排班详情
            getDetail : function(params) {
                return ApiUtil.get('Sign/Apicp/SignScheduleCp/get_schedule', params);
            },
            // 新增班次
            classAdd : function(params){
                return ApiUtil.post('Sign/Apicp/SignCp/add',params);
            },
            // 班次查看考勤规则、编辑初始化
            classEditInit : function(params){
                return ApiUtil.get('Sign/Apicp/SignCp/getBatchDetail',params);
            },
            // 修改班次
            classUpdate : function(params){
                return ApiUtil.post('Sign/Apicp/SignCp/update',params);
            },
            // 删除班次
            classDel : function(params){
                return ApiUtil.post('Sign/Apicp/SignCp/delete',params);
            }

        };
    }]);
})(angular.module('app.modules.api'));

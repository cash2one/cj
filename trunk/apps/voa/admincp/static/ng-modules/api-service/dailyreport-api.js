(function (app) {
    app.factory('DailyReportApi',['ApiUtil',function(ApiUtil){
        return {
            // 添加模板
            addReportTpl : function(params){
                return ApiUtil.post('Dailyreport/Apicp/DailyreportTpl/Add',params);
            },
            // 获取保存模板
            getReportTpl : function(params){
                return ApiUtil.get('Dailyreport/Apicp/DailyreportTpl/Gettpl',params);
            },
            // 保存模板
            saveReportTpl : function(params){
                return ApiUtil.post('Dailyreport/Apicp/DailyreportTpl/Save',params);
            },
            //获取报告模板列表
            getReportList : function(params){
                return ApiUtil.get('Dailyreport/Apicp/DailyreportTpl/GetList',params);
            },
            //删除报告
            delReportList : function(params){
            	return ApiUtil.get('Dailyreport/Apicp/DailyreportTpl/Del',params);
            },
            //禁用启用模板
            enableSwitch : function(params){
            	return ApiUtil.get('Dailyreport/Apicp/DailyreportTpl/Switch',params);
            },
            //获取工作日报列表
            getWorkReportList : function(params){
            	return ApiUtil.get('Dailyreport/Apicp/Dailyreport/GetList',params);
            },
            //删除工作日报列表
            delWorkReportItem : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/Del',params);
            },
            //获取日报类型
            getReportListType : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/Type',params);
            },
            //获取微信菜单
            getWeChatmenu : function(params){
                return ApiUtil.get('Dailyreport/Apicp/DailyreportSetting/GetWechatMenu',params);
            },
            //保存微信菜单
            weChatSave : function(parmas){
                return ApiUtil.post('Dailyreport/Apicp/DailyreportSetting/SaveWechatMenu',parmas);
            },
            //一键还原微信菜单
            weChatrestore : function(params){
                return ApiUtil.post('Dailyreport/Apicp/DailyreportSetting/ResetWechatMenu ',params);
            },
            //报告详情
            getAdminReport  : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/GetAdminReport',params);
            },
            //获取评论列表
            getAdminReportComments : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/GetAdminReportComments',params);
            },
            //删除评论
            delAdminReportComment : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/DelAdminReportComment',params);
            },
            //导出
            export : function(params){
                return ApiUtil.get('Dailyreport/Apicp/Dailyreport/Export',params);
            },
            //新增模板时获取排序
            sort :function(){
                return ApiUtil.get('Dailyreport/Apicp/DailyreportTpl/GetTplSort');
            }
        };

    }]);
})(angular.module('app.modules.api'));

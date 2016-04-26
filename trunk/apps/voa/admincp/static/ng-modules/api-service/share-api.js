/*
    魏世超
*/
(function (app) {
    app.factory('ShareApi',['ApiUtil',function(ApiUtil){
        return {
            // 获取列表数据
            confQuery : function(params){
                return ApiUtil.get('Share/Apicp/Material/list',params);
            },
            //获得详情页内容
            goDetail : function(params){
            	return ApiUtil.get('Share/Apicp/Material/detail',params);
            },
            //详情页判断是否驳回
            getBohui : function(params){
                return ApiUtil.post("Share/Apicp/Material/updateStatus",params);
            },

            //设置页面获取地址
            getUrl : function(params){
                return ApiUtil.get("Share/Apicp/Material/set",params);
            }

        };
    }]);
})(angular.module('app.modules.api'));

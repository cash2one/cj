(function (app) {
    app.factory("ScoreApi",["ApiUtil",function(ApiUtil){
        return {
            // 获取积分功能开关是否开启
            getSwitch : function(){
                return ApiUtil.get("Score/Apicp/Score/getSwitch");
            },
            // 提交积分功能开关状态
            changeSwitch : function(params){
                return ApiUtil.post("Score/Apicp/Score/postChangeSwitch",params);
            },
            // 提交新增奖品设置信息
            addAward : function(params){
                return ApiUtil.post("Score/Apicp/ScoreAward/addAward",params);
            },
	        //获取积分记录列表
            getScoreLogList : function(params){
                return ApiUtil.get("Score/Apicp/Score/getScoreLogList",params);
            },
            //获取积分奖品列表
            getAwardList : function(params){
                return ApiUtil.get("Score/Apicp/ScoreAward/getAwardList",params);
            },
            //获取奖品兑换记录列表
            getAwardExchangeList : function(params){
                return ApiUtil.get("Score/Apicp/ScoreAward/AwardExchangeList_get",params);
            },
            //获取积分奖品兑换详情
            getAwardDetaill : function(params){
                return ApiUtil.get("Score/Apicp/ScoreAward/awardDetail", params);
            },
            //提交积分兑换的拒绝理由
            processAwardOrder : function(params){
                return ApiUtil.post("Score/Apicp/ScoreAward/processAwardOrder", params);
            },
            awardEdit : function(params){
                return ApiUtil.get("Score/Apicp/ScoreAward/awardEdit", params);
            },
            editAward : function(params){
                return ApiUtil.post("Score/Apicp/ScoreAward/editAward", params);
            },
            changeAwardStatus : function(params){
                return ApiUtil.post("Score/Apicp/ScoreAward/changeAwardStatus", params);
            }
        };
    }]);
})(angular.module("app.modules.api"));

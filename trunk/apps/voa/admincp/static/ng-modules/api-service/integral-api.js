/**
 * Created by liangpure on 2016/4/13.
 */

(function (app) {
    app.factory('IntegralApi',['ApiUtil',function(ApiUtil){
        return {
             //积分成员列表
            memberList: function(params){
                return ApiUtil.get('Score/Apicp/ScoreMember/memberList', params);
            },
            //批量增加积分
            batchAdd: function(params){
                return ApiUtil.post('Score/Apicp/ScoreMember/changeMembersScore', params);
            },
            //批量减少几分
            batchReduce: function(params){
                return ApiUtil.post('Score/Apicp/ScoreMember/changeMembersScore', params);
            },
            //获取成员详细信息
            getMemberDetails: function(params){
                return ApiUtil.get('Score/Apicp/ScoreMember/memberDetail', params)
            },
            //获取成员积分明细
            getMemberScoreList:function(params){
                return ApiUtil.get('Score/Apicp/ScoreMember/memberScoreList', params);
            },
            //获取奖品兑换记录
            getMemberExchangeList:function(params){
                return ApiUtil.get('Score/Apicp/ScoreMember/memberAwardExchangeList', params);
            },
            //获取积分规则列表
            getRuleList:function(params){
                return ApiUtil.get('Score/Apicp/Score/getScoreRuleList', params);
            },
            //保存积分规则
            postUpdateRules: function(params){
                return ApiUtil.post('Score/Apicp/Score/postUpdateRules', params);
            },
            //积分规则状态更新 启用和禁用
            changeRuleStatus: function(params){
                return ApiUtil.post('Score/Apicp/Score/changeRuleStatus', params);
            }
        };
    }]);
})(angular.module('app.modules.api'));
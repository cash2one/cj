(function (app) {
    app.factory('CampaignsApi',['ApiUtil',function(ApiUtil){
        return {
            CampaignSettingSave: function(params){
                return ApiUtil.post('Campaigns/Apicp/Setting/save',params);
            },
            CampaignSettingList: function(){
                return ApiUtil.get('Campaigns/Apicp/Setting/getList');
            },
            CampaignsAdd: function(params){
                return ApiUtil.post('Campaigns/Apicp/Cam/add',params);
            },
            list: function(params){
                return ApiUtil.get('Campaigns/Apicp/Cam/list',params);
            },
            dataCenter: function(params){
                return ApiUtil.get("Campaigns/Apicp/Cam/dataCenter",params);
            },
            GetCampaignsById:function(params){
                return ApiUtil.get("Campaigns/Apicp/Cam/editDetail",params);
            },
            campaignsDetail:function(params){
                return ApiUtil.get("Campaigns/Apicp/Cam/detail",params);
            },
            CampaignsSave:function(params){
                return ApiUtil.post("Campaigns/Apicp/Cam/save",params);
            },
            dels : function(params){
                return ApiUtil.post("Campaigns/Apicp/Cam/dels",params);
            }
        };
    }]);
})(angular.module('app.modules.api'));


define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {

    }
    
    model.prototype = {
    	tv_id: 0,	//客户id
    	tv_avatar: '/static/mobile/app/talk/images/avatar.png',	//客户头像
    	sale_avatar: '/misc/images/male.png',//销售头像
    	username: null,
        //获取联系人列表
        get_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/talk/get/viewerlist', params, function (ret) {
                	if(!ret) ret = [];
                	for(k in ret)
                	{
                		var time = ret[k].lastts ? ret[k].lastts : ret[k].created;
                		ret[k].lasttsStr = self.formatDate(time);
                	}
                	callback(ret);
                });
            }
        },
        //客户注册
        register: function(params) {
        	var self = this;
        	$.ajax({
                url: '/api/talk/post/register',
                dataType: "json",
                type: "post",
                data: params,
                async: false,
                success: function (ret) {
                	self.tv_id = ret.result.tv_uid;
                	self.username = ret.result.username;
                }, 
                statusCode: {
                    404: function() {
                          alert( "no api :"+url );
                    }
                }
            });
        },
        //对话信息
        get_chat: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/talk/get/chatlist', params, function (ret) {
                	if(!ret.data) ret.data = [];
                	for(k in ret.data)
                	{
                		ret.data[k].createdStr = self.formatDate(ret.data[k].created);
                		if(ret.data[k].tw_type == 1) {
                			ret.data[k].avatar = self.tv_avatar;
                		}else{
                			ret.data[k].avatar = self.sale_avatar;
                		}
                	}
                	callback(ret);
                });
            }
        },
        //发送对话
        say: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.save('/api/talk/post/say', params, function (ret) {
                	callback(ret);
                });
            }
        },
        //客户端聊天界面初始化,获取产品名称及销售姓名
        init: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/talk/get/init', params, function (ret) {
                	if(ret.tv_avatar) self.tv_avatar = ret.tv_avatar;
                	if(ret.sale_avatar) self.sale_avatar = ret.sale_avatar;
                	$('img[tw_type=1]').attr('src', self.tv_avatar);
                	$('img[tw_type=2]').attr('src', self.sale_avatar);
                	callback(ret);
                });
            }
        },
        //格式化日期
        formatDate: function(now)   {     
			var	d = new   Date(now * 1000);
			var year = d.getFullYear();
			var month=d.getMonth() + 1;
			if(month < 10) month = '0' + month;
			var date=d.getDate();
			if(date < 10) date = '0' + date;
			var hour=d.getHours();
			if(hour < 10) hour = '0' + hour;
			var minute=d.getMinutes();
			if(minute < 10) minute = '0' + minute;
			var second=d.getSeconds();
			var current = Date.parse(new Date()) / 1000;
			if(current - now > 28800) {
				return year+"-"+month+"-"+date+" "+hour+":"+minute;
			}else{
				return hour+":"+minute;
			}
		}
    };

    return new model();
});

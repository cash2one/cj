define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {

    }
    model.prototype = {
		//获取权限
        get_power: function(callback) {
            var url = '/api/superreport/get/power';
            if (_.isFunction(callback)) {
                return api.get(url, {}, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取报表模板
        get_template: function(params, callback) {
            var url = '/api/superreport/get/template';     
            if (_.isFunction(callback)) {
            	return api.get(url, params, function (ret, all) {
                    callback(ret, all);
                });
            } else {
                return api.get(url, params);
            }
        },
		//获取日报详情
        get_daily: function(params, callback) {          
            var url = '/api/superreport/get/daily';     
            if (_.isFunction(callback)) {
            	return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取月报详情
        get_month: function(params, callback) {
			var url = '/api/superreport/get/month';
        	if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取评论
        get_comments: function(params, callback) {
			var url = '/api/superreport/get/comments';
        	if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取门店列表
        get_shops: function(params, callback) {
			var url = '/api/superreport/get/shops';
        	if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取门店列表
        get_monthshops: function(params, callback) {
            var url = '/api/superreport/get/monthshops';
            if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取编辑日报数据
        get_view: function(params, callback) {
			var url = '/api/superreport/get/view';
        	if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
		//新增报表
        add_report: function(params, callback) {
			var url = '/api/superreport/post/add';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //新增评论
        add_comment: function(params, callback) {
			var url = '/api/superreport/post/addcomment';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //编辑报表
        edit_report: function(params, callback) {
			var url = '/api/superreport/post/edit';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        }       
    };

    return new model();
});

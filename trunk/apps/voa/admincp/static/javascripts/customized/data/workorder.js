define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {

    }
    model.prototype = {
		//获取派单列表
        get_list: function(params, callback) {
            var self = this;
            var url = '/api/workorder/get/';
            if(params.res == 'received') {
            	url += 'receivedlist';
            }else{
            	url += 'sentlist';
            }
            if(!params.type) {
            	params.type = 'wait_confirm';
            }
            if (_.isFunction(callback)) {
            	return api.get(url, {page:1, limit:10, type:params.type}, function (ret) {
                    callback({data: ret.list});
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取派单详情
        get_detail: function(params, callback) {
			var url = '/api/workorder/get/view';
        	if (_.isFunction(callback)) {
                return api.get(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
		//撤回派单
        cancel: function(params, callback) {
			var url = '/api/workorder/post/cancel';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //接受派单
        confirm: function(params, callback) {
			var url = '/api/workorder/post/confirm';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //拒绝派单
        refuse: function(params, callback) {
			var url = '/api/workorder/post/refuse';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //完成派单
        complete: function(params, callback) {
			var url = '/api/workorder/post/complete';
        	if (_.isFunction(callback)) {
                return api.save(url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(url, params);
            }
        },
        //发起派单
        publish : function(params, callback) {
			var url = '/api/workorder/post/send';
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

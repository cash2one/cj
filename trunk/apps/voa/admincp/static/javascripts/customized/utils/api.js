define(["jquery", "underscore"], function ($, _){
	var api_url = {
			travel: {
				get: {
					goods: '/api/travel/get/goods',
					goodsclass: '/api/travel/get/goodsclass',
					goodstablecolopt: '/api/travel/get/goodstablecolopt',
					goodstablecol: '/api/travel/get/goodstablecol',
					goodsdetail: '/api/travel/get/goodsdetail',
				},
				post: {
					goodstpl: '/api/travel/post/goodstpl',
					goods: '/api/travel/post/goods',
					goodsclass: '/api/travel/post/goodsclass',
					goodstablecolopt: '/api/travel/post/goodstablecolopt',
					goodstablecol: '/api/travel/post/goodstablecol',
				},
				delete: {
					goods: '/api/travel/delete/goods',
					goodsclass: '/api/travel/delete/goodsclass',
					goodstablecolopt: '/api/travel/delete/goodstablecolopt',
					goodstablecol: '/api/travel/delete/goodstablecol',
				}
			},
			customer: {
				get: {
					goods: '/api/travel/get/customer',
					goodsclass: '/api/travel/get/customerclass',
					goodstablecolopt: '/api/travel/get/customertablecolopt',
					goodstablecol: '/api/travel/get/customertablecol',
					goodsdetail: '/api/travel/get/customerdetail',
				},
				post: {
					goods: '/api/travel/post/customer',
					goodsclass: '/api/travel/post/customerclass',
					goodstablecolopt: '/api/travel/post/customertablecolopt',
					goodstablecol: '/api/travel/post/customertablecol',
				},
				delete: {
					goods: '/api/travel/delete/customer',
					goodsclass: '/api/travel/delete/customerclass',
					goodstablecolopt: '/api/travel/delete/customertablecolopt',
					goodstablecol: '/api/travel/delete/customertablecol',
				}
			},
			superreport: {
				get: {
					goodstablecol: '/api/superreport/get/goodstablecol',
				},
				post: {
					goodstablecol: '/api/superreport/post/goodstablecol',
				},
				delete: {
					goodstablecol: '/api/superreport/delete/goodstablecol',
				}
			}
	};
    var app = window._appname;
    //var api_pre_url = "/api/"+app;
    window._data = {};
    var handler = {
            set: function (key, val) {
            	window._data[key] = val;
            }, 
            delete: function (api, data, callback) {
            	var url = _.isEmpty(api_url[app].delete[api]) ? api : api_url[app].delete[api];
            	data.url = api_url[app].get[data.url];
            	if (window._data[data.url]) {
            		if (_.isArray(window._data[data.url])) {
	            		window._data[data.url] = _.filter(window._data[data.url], function (item) {
	            			var ret = true;
	            			$.each(item, function (k, v){
	            				if (data.field == k) {
	            					if (v == data.value) {
	            						ret = false;
	            					}
	            				}
	            			});
	            			return ret;
	            		});
            		} else if (_.isObject(window._data[data.url])){
            			window._data[data.url] = null;
            		}
            	}
            	$.ajax({
                    url: url+"?"+"bust=" + (new Date()).getTime(),
                    dataType: "json",
                    type: "delete",
                    data: data,
                    //async: false,
                    success: function (ret) {
                    	//val = ret;
                        if ("0" != ret.errcode) {
                            alert(ret.errmsg);
                            return;
                        }
                        // 如果需要回调
                        if ("function" == typeof callback) {
                            callback(ret.result);
                        } 
                    }, 
                    statusCode: {
                        404: function() {
                              alert( "error :"+key );
                        }
                    }
                });
            },
            save: function(api, data, callback) {
            	var url = _.isEmpty(api_url[app].post[api]) ? api :  api_url[app].post[api];
            	$.ajax({
                    url: url+"?"+"bust=" + (new Date()).getTime(),
                    type: "post",
                    dataType: "json",
                    data: data,
                    //async: false,
                    success: function (ret) {
                    	if (ret.errcode == "0") {
                    		//handler.set(url, $.extend(handler.get(api), data));
                        	if ( typeof callback == "function" ) {
                        		callback(ret.result);
                        	}
                    	} else {
                    		alert(ret.errmsg);
                    	}
                    	
                    	
                    }, 
                    statusCode: {
                        404: function() {
                              alert( "error :"+url );
                        }
                    }
                });
            	//return ret.result;
            },
            get: function (api, params, force, callback) {
            	var url = _.isEmpty(api_url[app].get[api]) ? api : api_url[app].get[api];
                if ( typeof force == "function" ) {
                    callback = force;
                }
            	if (api == '/api/common/get/columntype') {
            		url = api
            	}
            	if (force) {
            		window._data[url] = null;
            	}
            	var async = false;
            	if ( typeof callback == "function") {
            		async = true;
            	}
            	if (window._data[url] != null) {
            		if ( async) {
                		callback(window._data[url]);
                	} else {
                		return window._data[url];
                	}
            		
            	} else {
            		var val = {};
            		$.ajax({
                        url: url+"?is_admin=1&"+"bust=" + (new Date()).getTime(),
                        dataType: "json",
                        type: "get",
                        data: params,
                        async: async,
                        success: function (ret) {
                        	//handler.set(url, ret.result);
                        	if ( async) {
                        		callback(ret.result);
                        	} else {
                        		val  = ret.result;
                        	}
                        }, 
                        statusCode: {
                            404: function() {
                                  alert( "no api :"+url );
                            }
                        }
                    });
            		if (async == false) {
            			return val;
            		}
            		
            		
            		//return val;
            	}
            	
            	/*
            	var item = _.find(window.data._cache, function (item, k) {
            		return  k == key;
            	});*/
            }
        };
    return handler;
	
});
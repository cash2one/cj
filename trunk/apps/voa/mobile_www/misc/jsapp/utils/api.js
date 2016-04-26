define(["jquery", "underscore"], function ($, _){
    function api () {
    }
    api.prototype = {
        set: function (key, val) {
            window._data[key] = val;
        }, 
        delete: function (api, data, callback) {
            var url = api; 
            
            $.ajax({
                url: url+"?"+"bust=" + (new Date()).getTime(),
                dataType: "json",
                type: "delete",
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
                    //val = ret;
                }, 
                statusCode: {
                    404: function() {
                          alert( "error :"+key );
                    }
                }
            });
        },
        save: function(api, data, callback) {
            var url = api;
            $.ajax({
                url: url+"?"+"bust=" + (new Date()).getTime(),
                type: "post",
                dataType: "json",
                data: data,
                //async: false,
                success: function (ret) {
                	/*
                    if (ret.errcode != "0") {
                        alert(ret.errmsg);
                    }*/
                    if ( typeof callback == "function" ) {
                        callback(ret);
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
        get: function (api, params, callback) {
            var url = api;
            var async = false;
            if ( typeof callback == "function") {
                async = true;
            }
            
            var val = {};
            $.ajax({
                url: url+"?"+"bust=" + (new Date()).getTime(),
                dataType: "json",
                type: "get",
                data: params,
                async: async,
                success: function (ret) {
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
        }
    };
    return new api();
});

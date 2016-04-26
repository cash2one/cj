define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {

    }
    model.prototype = {
        //获取目录列表
        get_cat: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/train/get/category', params, function (ret) {
                	callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取文章列表
        get_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/train/get/article', params, function (ret) {
                	callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        },
        //获取文章详情
        get_detail: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/train/get/view', params, function (ret) {
                	callback(ret);
                });
            } else {
                return api.get(url, params);
            }
        }
    };

    return new model();
});

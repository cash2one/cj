define(["utils/api", "jquery", "underscore"], function (api, $, _) {

    // 构造方法
	function Model() {
		// do nothing.
	}

	Model.prototype = {
        /**
         * 根据 url 获取数据
         * @param {string} api_url 请求的 url 地址
         * @param {*} params 请求参数
         * @param {function} callback 回调方法
         * @returns {Object}
         * @private
         */
		_get: function(api_url, params, callback) {
            // 如果有回调方法
            if (_.isFunction(callback)) {
                return api.get(api_url, params, function (ret, org) {
                	callback(ret, org);
                });
            } else {
                return api.get(api_url, params, null);
            }
        },
        /**
         * 保存数据
         * @param {string} api_url 请求的 url 地址
         * @param {*} params 请求参数
         * @param {function} callback 回调方法
         * @returns {*}
         * @private
         */
        _save: function(api_url, params, callback) {
            if (_.isFunction(callback)) {
                return api.save(api_url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save(api_url, params, null);
            }
        },
        /**
         * 删除数据
         * @param {string} api_url 请求的 url 地址
         * @param {*} params 请求参数
         * @param {function} callback 回调方法
         * @returns {*}
         * @private
         */
        _delete: function(api_url, params, callback) {
            if (_.isFunction(callback)) {
                return api.delete(api_url, params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.delete(api_url, params, null);
            }
        }
	};

	Model.prototype.parent = Model.prototype;

	return Model;
});
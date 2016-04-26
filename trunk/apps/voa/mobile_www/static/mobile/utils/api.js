define(["jquery"], function ($) {

    function Api() {
        // do nothing.
    }

    Api.prototype = {
        /**
         * 设置数据
         * @param {string} key 数据键值
         * @param {*} val 数据详情
         */
        set: function(key, val) {
            window._data[key] = val;
        },
        /**
         * 删除操作
         * @param {string} api API地址
         * @param {*} params 数据
         * @param {function} callback 回调方法
         */
        delete: function(api, params, callback) {

            $.ajax({
                url: api + "?" + "bust=" + (new Date()).getTime(),
                dataType: "json", // 数据返回格式
                type: "delete", // 请求方式
                data: params, // 请求参数
                // async: true, 异步调用时的回调处理
                success: function(ret) {
                    // 如果返回 errcode 不为 0, 则说明出错了
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
                        alert("error :" + api);
                    }
                }
            });
        },
        /**
         * 删除操作
         * @param {string} api API地址
         * @param {*} params 数据
         * @param {function} callback 回调方法
         */
        save: function(api, params, callback) {

            $.ajax({
                url: api + "?" + "bust=" + (new Date()).getTime(),
                type: "post", // 请求方式
                dataType: "json", // 数据返回格式
                data: params, // 请求参数
                // async: true, 异步请求时的回调处理
                success: function(ret) {
                    if ("function" == typeof callback) {
                        callback(ret);
                    }
                },
                statusCode: {
                    404: function() {
                        alert("error :" + api);
                    }
                }
            });
        },
        /**
         * 获取数据
         * @param {string} api API地址
         * @param {*} params 参数
         * @param {function} callback 回调方法
         * @returns {object}
         */
        get: function(api, params, callback) {

            var async = false;
            var result = {};
            // 如果有回调方法, 则强制设为异步
            if ("function" == typeof callback) {
                async = true;
            }

            $.ajax({
                url: api + "?" + "bust=" + (new Date()).getTime(),
                dataType: "json", // 数据返回格式
                type: "get", // 请求方式
                data: params, // 请求参数
                async: async, // 是否异步
                success: function(ret) {
                    // 异步请求时, 调用回调方法
                    if (async) {
                        callback(ret.result, ret);
                    } else {
                        result = ret.result;
                    }
                },
                statusCode: {
                    404: function() {
                        alert("no api :" + api);
                    }
                }
            });

            if (false == async) {
                return result;
            }
        }
    };

    return new Api();
});

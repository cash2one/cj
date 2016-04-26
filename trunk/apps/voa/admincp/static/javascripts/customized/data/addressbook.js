define(["data/base", "jquery"], function (base, $) {

    function Model() {
    	base.call(this);
    }

    Model.prototype = Object.create(base.prototype);
    Model.prototype.constructor = Model;

    Model.prototype = $.extend(Model.prototype, {
        /**
         * 获取通讯录列表
         * @param {*} params 请求参数
         * @param {function} callback 回调方法
         * @returns {Object}
         */
        get_list: function(params, callback) {
            return this._get('/api/addressbook/get/list', params, callback);
        },
        /**
         * 获取部门列表
         * @param {*} params 请求参数
         * @param {function} callback 回调方法
         * @returns {Object}
         */
        get_departments: function(params, callback) {
            return this._get('/api/addressbook/get/departments', params, callback);
        }
    });

    return new Model();
});

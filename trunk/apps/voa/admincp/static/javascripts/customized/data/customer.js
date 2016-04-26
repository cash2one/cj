define(["data/base", "utils/customized_form", "jquery", "underscore"], function(base, customized_form, $, _) {

    function Model() {
    	var self = this;
		var body = $('body');
    	// 初始化表字段
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_cols = $.data(body, 'customer_table_cols');
    	if (!this.table_cols) {
    		self.table_cols = this.get_table_col();
    		$.data(body, 'customer_table_cols', self.table_cols);
    	}

    	// 如始化表字段选项值
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_col_opts = $.data(body, 'customer_table_col_opts');
    	if (!this.table_col_opts) {
    		self.table_col_opts = this.get_table_col_opt();
    		$.data(body, 'customer_table_col_opts', self.table_col_opts);
    	}
    }

    Model.prototype = Object.create(base.prototype);
    Model.prototype.constructor = Model;
    Model.prototype = $.extend(Model.prototype, {
		// 表字段
    	table_cols: {},
    	//表字段选项值
    	table_col_opts: {},
		/**
		 * 客户关联产品
		 * @param {*} params 请求参数
		 * @param {function} callback 回调方法
		 * @returns {*}
		 */
        follow_goods: function (params, callback) {
            return this._save('/api/travel/post/attgoods', params, callback);
        },
		/**
		 * 获取客户列表
		 * @param {*} params 请求参数
		 * @param {function} callback 回调方法
		 * @returns {*}
		 */
        get_list: function(params, callback) {

        	var goods_customer = null;
        	// if goods_id 不为空， then 获取已经关注过产品的客户
        	if (params.goods_id) {
        		goods_customer = this.get_goods_customer({goods_id: params.goods_id});
        	}

            var self = this;
            if (_.isFunction(callback)) {
                return this._get('/api/travel/get/customer', params, function(ret) {
                	var data;
                	if (!_.isEmpty(goods_customer)) {
                		data = _.filter(ret.data, function (item) {
                			var ret = _.find(goods_customer, function (cid){
                				return cid == item.dataid
                			});
                    		return !ret;
                    	});

                	} else {
                		data = ret.data;
                	}
                	data = _.map(data, function (item) {
                		return self._process_detail(item);
                	});
                	callback({data: data});
                });
            } else {
            	var ret = this._get('/api/travel/get/customer', params, null);
                var data = _.map(ret.data, function (item) {
            		return self._process_detail(item);
            	});
            	return {data: data};
            }
        },

        get_detail: function (params, callback) {
        	var self = this;
            if (_.isFunction(callback)) {
                return this._get('/api/travel/get/customerdetail', params, function (ret) {
                	callback(self._process_detail(ret));
                });
            } else {
            	var ret = this._get('/api/travel/get/customerdetail', params, null);
                return self._process_detail(ret);
            }
        },

        get_follow_list: function(params, callback) {
        	return this._get('/api/travel/get/attgoods', params, callback);
        },

        get_remark: function(params, callback) {
            return this._get('/api/travel/get/remark', params, function (ret) {
            	ret.data = _.map(ret.data, function (row) {
            		var d = new Date(row.updated * 1000);
                    row.updated_date = d.getFullYear()+'-'+(d.getMonth() + 1)+'-'+d.getDate();

                	return row;
            	});
            	callback(ret);
            });
        },

        remark_save: function(params, callback) {
            return this._save('/api/travel/post/remark', params, callback);
        },

        save: function(params, callback) {
            return this._save('/api/travel/post/customer', params, callback);
        },

        get_cates_list: function(params, callback) {
        	return this._get('/api/travel/get/customerclass', params, callback);
        },

        get_table_col: function(params, callback) {
            if (!_.isEmpty(this.table_cols)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_cols);
        		} else {
        			return this.table_cols;
        		}
        	}
            var data =  this._get('/api/travel/get/customertablecol', params, callback);
			data = _.filter(data, function(item) {return item.isuse == '1'});

			return data;
        },

        get_table_col_opt: function(params, callback) {
            if (!_.isEmpty(this.table_col_opts)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_col_opts);
        		} else {
        			return this.table_col_opts;
        		}
        	}
            return this._get('/api/travel/get/customertablecolopt', params, callback);
        },

        get_goods_customer: function (params, callback) {
        	return this._get('/api/travel/get/goodscustomer', params, callback);
        },

        _process_detail: function (goods) {
        	// 如果没有取到字段数据，则返回源数据
        	if (_.isEmpty(this.table_cols) || _.isEmpty(this.table_col_opts)) {
        		return goods;
        	}

        	var cols = this.table_cols;
        	var customized = new customized_form();
    		customized.col_opts_list = this.table_col_opts;
            $.each(cols, function (k3, col2) {
            	goods = customized.get_col_value(col2, goods);
            });
            var d = new Date(goods.updated *1000);
            goods.updated = d.getUTCFullYear()+'-'+(d.getUTCMonth() + 1)+'-'+d.getUTCDate();
            // 分类
            /** 暂时隐藏
            if ('0' != goods.classid) {
        		classname = self.get_cates_list({classid: goods.classid});
        		if (!_.isEmpty(classname.data)) {
        			goods.classname = classname.data[0].classname;
        		} else {
        			goods.classname = '';
        		}
        	}*/
            return goods;
        }

    });

    return new Model();
});

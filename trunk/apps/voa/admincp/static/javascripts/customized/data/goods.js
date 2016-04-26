define(["data/base", "utils/customized_form", "jquery", "underscore"], function(base, customized_form, $, _) {

    function Model() {
    	var self = this;
		var body = $('body');
    	// 初始化表字段
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_cols = $.data(body, 'goods_table_cols');
    	if (!this.table_cols) {
    		self.table_cols = this.get_table_col();
    		$.data(body, 'goods_table_cols', self.table_cols);
    	}

    	// 如始化表字段选项值
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_col_opts = $.data(body, 'goods_table_col_opts');
    	if (!this.table_col_opts) {
    		self.table_col_opts = this.get_table_col_opt();
    		$.data(body, 'goods_table_col_opts', self.table_col_opts);
    	}
    }

    Model.prototype = Object.create(base.prototype);
    Model.prototype.constructor = Model;

    Model.prototype = $.extend(Model.prototype, {
    	// 表字段
    	table_cols: {},
    	//表字段选项值
    	table_col_opts: {},

        add_from_supply: function(dataid, callback) {
            this._save('/api/travel/post/goodspull', {dataid: dataid}, callback);
        },

        get_supply_list: function(params, callback) {
            return this.get_list($.extend(params, {src_field: 1}), function(ret_goods_list) {
            	callback(ret_goods_list);
            });
        },

        get_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return this._get('/api/travel/get/goods', params, function (ret, org) {
                	var data = _.map(ret.data, function (item) {
                		return self._process_detail(item);
                	});

                	callback({data: data, total: ret.total, timestamp: org.timestamp});

                });
            } else {
                var ret = this._get('/api/travel/get/goods', params, null);
                var data = _.map(ret.data, function (item) {
            		return self._process_detail(item);
            	});
                return {data: data, total: ret.total};
            }
        },

        get_detail: function(params, callback) {
        	var self = this;
            if (_.isFunction(callback)) {
                return this._get('/api/travel/get/goodsdetail', params, function (ret, org) {
                	var item;
                	if (org.errcode == '0') {
                		item = self._process_detail(ret);
                	} else {
                		item = org;
                	}

                    callback(item);
                });
            } else {
            	var item = this._get('/api/travel/get/goodsdetail', params, null);
                item = self._process_detail(item);
                callback(item);
            }
        },

        save: function(params, callback) {
            return this._save('/api/travel/get/goods', params, callback);
        },

        delete: function(params, callback) {
            return this._delete('/api/travel/delete/goods', params, callback);
        },

        get_table_col: function(params, callback) {
        	if (!_.isEmpty(this.table_cols)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_cols);
        		} else {
        			return this.table_cols;
        		}
        	}
        	return this._get('/api/travel/get/goodstablecol', params, callback);
        },

        get_table_col_opt: function(params, callback) {
        	if (!_.isEmpty(this.table_col_opts)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_col_opts);
        		} else {
        			return this.table_col_opts;
        		}
        	}
        	return this._get('/api/travel/get/goodstablecolopt', params, callback);
        },

		/**
		 * 获取定单地址
		 * @param {*} params 请求参数
		 * @param {function} callback 回调方法
		 * @returns {Object}
		 * 返回:
			 address		{name:'联系人',phone:'电话',adr:'完整地址'}
			 ads_params	共享微信地址参数(js对象)
		 */
		get_order_address: function (params, callback) {
			return this._get('/api/order/get/address', params, callback);
		},

		/**
		 * 创建订单并返回支付参数
		 @param {*} 参数:
			 goods_id 	必填 	订单id
			 num		必填 	数量
			 name		必填 	联系人
			 phone		必填	电话
			 adr 		必填	完整地址(字符串)
		 * @param {function} callback 回调方法
		 * @returns {Object}:
			 orderid		本地订单id
			 pay_params	支付参数,js对象
		 */
		save_order: function (params, callback) {
			return this._save('/api/order/post/pay/', params, callback);
		},

		/**
		 * 订单列表
		 @param {*} 参数:
			 page	非必选	页码,默认1
			 size	非必选	每页数,默认10
		 * @param {function} callback 回调方法
		 * @returns {Object}
		 */
		get_order_list: function (params, callback) {
			return this._get('/api/order/get/list/', params, callback);
		},

		/**
		 * 订单详情
		 @param {*} 参数:
		 	orderid		必填	订单id
		 * @param {function} callback 回调方法
		 * @returns {Object} 订单详情
		 */
		get_order_detail: function (params, callback) {
			return this._get('/api/order/get/detail', params, callback);
		},

		/**
		 * 订单继续
		 @param {*} 参数:
		 orderid		必填	订单id
		 * @param {function} callback 回调方法
		 * @returns {Object} 微信支付参数
		 */
		order_pay_continue: function (params, callback) {
			return this._get('/api/order/get/pay2', params, callback);
		},

		/**
		 * 订单支付状态查询
		 @param {*} 参数:
		 orderid		必填	订单id
		 * @param {function} callback 回调方法
		 * @returns {string} ok或no
		 */
		order_pay_status: function (params, callback) {
			return this._get('/api/order/get/query', params, callback);
		},


		/**
		 * 删除订单
		 @param {*} 参数:
		 	orderid		必填	订单id
		 * @param {function} callback 回调方法
		 * @returns {Object}
		 */
		del_order: function (params, callback) {
			return this._get('/api/order/post/delete', params, callback);
		},

		/**
		 * 获取分类列表
		 * @param {function} callback 回调方法
		 * @returns {Object}
		 */
		get_goodsclass_list: function (params, callback) {
			return this._get('/api/travel/get/goodsclass', params, callback);
		},

		/**
		 * 置顶
		 * @param {function} callback 回调方法
		 * @returns {Object}
		 */
		top: function (params, callback) {
			return this._get('/api/travel/post/goodstop', params, callback);
		},

        _process_detail: function(row) {
        	//if (_.isEmpty(this.table_cols) || _.isEmpty(this.table_col_opts)) {
			if (_.isEmpty(this.table_cols)) {
        		return row;
        	}
        	var cols = this.table_cols;
            var price_col = _.find(cols, function (col_item){return col_item.field == 'price'});
            row.price_unit = price_col.unit;

			var recommend_col = _.find(cols, function (col_item){return col_item.field == 'recommend'});
			if (typeof recommend_col.isuse == undefined) {
				recommend_col.isuse = 0;
			}
			row.recommend_col = recommend_col;

            row.customized = _.filter(cols, function (item){
            	return item.field == '';
            });
            var customized = new customized_form();
    		customized.col_opts_list = this.table_col_opts;
    		// 自定义字段
            row.customized = _.map(row.customized, function (col) {
            	row = customized.get_col_value(col, row);
            	col.value = row['_'+col.tc_id];

            	return col;
            });

            if (row.recommend) {
            	var col = _.find(cols, function (item) {
            		return item.field == 'recommend';
            	});
            	if (!_.isEmpty(col)) {
            		var newcol = customized.get_col_value(col, row);
            		row.recommend = newcol.recommend;
            	}

            }

            return row;
        }

    });

    return new Model();
});

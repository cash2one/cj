define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {
    	var self = this;
    	// 初始化表字段
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_cols = $.data($('body'), 'customer_table_cols');
    	if (!this.table_cols) {
    		self.table_cols = this.get_table_col();
    		$.data($('body'), 'customer_table_cols', self.table_cols);
    	}
    	// 如始化表字段选项值
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_col_opts = $.data($('body'), 'customer_table_col_opts');
    	if (!this.table_col_opts) {
    		self.table_col_opts = this.get_table_col_opt();
    		$.data($('body'), 'customer_table_col_opts', self.table_col_opts);
    	}
    }
    model.prototype = {
		// 表字段
    	table_cols: {},
    	//表字段选项值
    	table_col_opts: {},
        follow_goods: function (params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.save('/api/travel/post/attgoods', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save('/api/travel/post/attgoods', params);
            }
        },
        get_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/customer', params, function (ret) {
                	var data = _.map(ret.data, function (item) {
                		return self._process_detail(item);
                	});
                	callback({data: data});
                });
            } else {
            	var ret = api.get('/api/travel/get/customer', params);
                var data = _.map(ret.data, function (item) {
            		return self._process_detail(item);
            	});
            	return {data: data};
            }
        }, 

        get_detail: function (params, callback) {
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/customerdetail', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/customerdetail', params);
            }
        },

        save: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.save('/api/travel/post/customer', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.save('/api/travel/get/customer', params);
            }
        },
        get_cates_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/customerclass', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/customerclass', params);
            }
        },
        get_table_col: function(params, callback) {
            var self = this;
            if (!_.isEmpty(this.table_cols)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_cols);
        		} else {
        			return this.table_cols;
        		}
        	}
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/customertablecol', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/customertablecol', params);
            }
        },
        get_table_col_opt: function(params, callback) {
            var self = this;
            if (!_.isEmpty(this.table_col_opts)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_col_opts);
        		} else {
        			return this.table_col_opts;
        		}
        	}
            
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/customertablecolopt', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/customertablecolopt', params);
            }
        },
        _process_detail: function (row) {
        	if (_.isEmpty(this.table_cols) || _.isEmpty(this.table_col_opts)) {
        		return row;
        	}
        	var self = this;
        	var cols = this.table_cols; 
            
            $.each(cols, function (key, item) {
            	if (item.field) {
	            	// 查找本字段的选项
					var opts = _.filter(self.table_col_opts, function(opt) {
						return opt.tc_id == item.tc_id
					});
	            	if (!_.isEmpty(opts)) {
	            		var opt = _.find(opts, function (opt) {return opt.tco_id == row[item.field]});
	            		if (opt) {
	            			row[item.field] = opt.value;
	            		} else {
	            			row[item.field] = '';
	            		}                	
	            	} 
            	}
            });
            var date = new Date(row.updated *1000);
            row.updated = date.getUTCFullYear()+'-'+date.getUTCMonth()+'-'+date.getUTCDate();
            

            return row;
        },

        
    };

    return new model();
});

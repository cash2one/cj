define(["utils/api", "jquery", "underscore"], function (api, $, _){
    
    function model() {
    	var self = this;
    	// 初始化表字段
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_cols = $.data($('body'), 'goods_table_cols');
    	if (!this.table_cols) {
    		self.table_cols = this.get_table_col();
    		$.data($('body'), 'goods_table_cols', self.table_cols);
    	}
    	// 如始化表字段选项值
    	// 从body的缓存里查看如果不存在则通过接口去取
    	this.table_col_opts = $.data($('body'), 'goods_table_col_opts');
    	if (!this.table_col_opts) {
    		self.table_col_opts = this.get_table_col_opt();
    		$.data($('body'), 'goods_table_col_opts', self.table_col_opts);
    	}
    }
    model.prototype = {
    	// 表字段
    	table_cols: {},
    	//表字段选项值
    	table_col_opts: {},

        add_from_supply: function(dataid, callback) {
            api.save('/api/travel/post/goodspull', {dataid: dataid}, callback);
        },
        get_supply_list: function(params, callback) {
            return this.get_list($.extend(params, {src_field: 1}), callback);
        },
        get_list: function(params, callback) {
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/goods', params, function (ret) {
                	var data = _.map(ret.data, function (item) {
                		return self._process_detail(item);
                	});
                	callback({data: data, total: ret.total});
                    
                });
            } else {
                var ret = api.get('/api/travel/get/goods', params);
                var data = _.map(ret.data, function (item) {
            		return self._process_detail(item);
            	});
                return {data: data, total: ret.total};
            }
        }, 
        
        get_detail: function(params, callback) {
        	var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/goodsdetail', params, function (ret) {
                	var item = self._process_detail(ret);
                    callback(item);
                });
            } else {
            	var item = api.get('/api/travel/get/goodsdetail', params);
                item = self._process_detail(item);
                callback(item);
            }
        },

        save: function(params, callback) {
            return api.post('/api/travel/post/goods', params, callback);
        },

        delete: function(params, callback) {
            return api.delete('/api/travel/delete/goods', params, callback);
        },
        
        get_table_col: function(params, callback) {
        	if (!_.isEmpty(this.table_cols)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_cols);
        		} else {
        			return this.table_cols;
        		}
        	}
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/goodstablecol', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/goodstablecol', params);
            }
        },
        get_table_col_opt: function(params, callback) {
        	if (!_.isEmpty(this.table_col_opts)) {
        		if (_.isFunction(callback)) {
        			return callback(this.table_col_opts);
        		} else {
        			return this.table_col_opts;
        		}
        	}
            var self = this;
            if (_.isFunction(callback)) {
                return api.get('/api/travel/get/goodstablecolopt', params, function (ret) {
                    callback(ret);
                });
            } else {
                return api.get('/api/travel/get/goodstablecolopt', params);
            }
        },
        
        _process_detail: function (row) {
        	if (_.isEmpty(this.table_cols) || _.isEmpty(this.table_col_opts)) {
        		return row;
        	}
        	var self = this;
        	var cols = this.table_cols; 
            var price_col = _.find(cols, function (col_item){return col_item.field == 'price'});
            row.price_unit = price_col.unit; 
            
            row.customized = _.filter(cols, function (item){
            	return item.field == '';
            });
            row.customized = _.map(row.customized, function (item) {
            	
            	// 查找本字段的选项
				var opts = _.filter(self.table_col_opts, function(opt) {
					return opt.tc_id == item.tc_id
				});
            	if (!_.isEmpty(opts)) {
            		var opt = _.find(opts, function (opt) {return opt.tco_id == row['_'+item.tc_id]});
            		if (opt) {
            			item.value = opt.value;
            		} else {
            			item.value = '';
            		}                	
            	} else {
            		item.value = row['_'+item.tc_id];
            	}
            	return item;
            });

            return row;
        },
        
    };

    return new model();
});

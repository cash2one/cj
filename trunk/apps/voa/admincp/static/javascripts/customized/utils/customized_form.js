define(["underscore", 'jquery'], function(_, $){
	function customized () {
		
	}
	customized.prototype= {
		cols_list: null,
		col_opts_list: null,
		_multi_value: function (g, col, options) {
			if (g["_" + col.tc_id].length) {
				// 字段值
				var value = g["_" + col.tc_id];
				// 如果值不是array 则转为array
				if (!_.isArray(value)) {
					value = g["_" + col.tc_id].split(',');
				}
				// 如果值为array;则处理, 否则不处理；
				if (_.isArray(value)) {
					// 找到该字段选项值, 因为是多选所以可能是多个值，所以用数组对像 
					var value_filter = [];
					$.each(options, function(k5, v6) {
						if (_.find(value, function(v2) {
							return v2 == v6.tco_id;
						})) {
							// 把找到的值放到数组对像里
							if (col.ftype != '2') {
								value_filter.push(v6.value);
							} else {
								value_filter.push(v6);
							}
							
						}
					});
					//value_filter = _(value_filter).toArray();
					// 如果值不为空刚把他组成字符串以逗号分隔
					if (value_filter.length && col.ftype != '2') {// 如果不是图片选项则直接返回值 
						g["_" + col.tc_id] = value_filter.join(', ');
					} else {// 如果是图片选项则返回选项值 
						g["_" + col.tc_id] = value_filter;
					}
				}
			} else {
				// 如果字段值赋空字符串值 
				g["_" + col.tc_id] = '';
			}
			return g;
		},
		_single_selection: function (g, col, options) {
			// 如果是select radio 单选项则
			// 查找选项值，返回选项数据
			var option = _.find(options, function (v4){
				//值判断
				return v4.tco_id == g["_"+col.tc_id];
				
			});
			// 如果存在选项，则设置自定义id的选项值
			if (option) {
				if (col.ftype != '2') { // 如果不是图片选项则直接返回值 
					g["_" + col.tc_id] = option.value;
				} else {// 如果是图片选项则返回选项值 
					g["_" + col.tc_id] = option;
				}
			} else {
				g["_" + col.tc_id] = '';
			}
			
			return g;
		},
		
		get_col_value: function (col, g) {
			// 如果有默认的字段名，则把该字段的值赋到自定义字段
			if (col.field) {
				g["_" + col.tc_id] = g[col.field];
			}
			// 查找本字段的选项
			var options = _.filter(this.col_opts_list, function(opt) {
				return opt.tc_id == col.tc_id
			});
			
			// 如果选为空则不处理返回值,如果不为空刚说明此字段是checkbo, radio, select 等多选字段
			if (!_.isEmpty(options)) {
				// 如果是checkbox
				if (col.ct_type == 'checkbox') {
					g = this._multi_value(g, col, options);
				} else {// 如果是select radio 单选项则
					g = this._single_selection(g, col, options);
				}
			} 
			if (_.isEmpty(g["_" + col.tc_id])) {
				// 如果是空的数据或字符串，则全部转为空字符串
				g["_" + col.tc_id] = '';
			} 
				
			// 如果有设置字段名则赋结果值 
			if (col.field) {
				g[col.field] = g["_" + col.tc_id];
			}
			
			return g;
		},
		
	
	};
	
	return customized;
});
define(["underscore", 'jquery'], function(_, $) {

	function Customized() {
		// do nothing.
	}

	Customized.prototype = {
		cols_list: null,
		col_opts_list: null,
		_multi_value: function (g, col, options) {
			// 字段值
			var value = g["_" + col.tc_id];
			// 如果字段值赋空字符串值
			if (!value.length) {
				g["_" + col.tc_id] = '';
				return g;
			}

			// 如果值不是 Array 则转为 Array
			if (!_.isArray(value)) {
				value = g["_" + col.tc_id].split(',');
			}

			// 找到该字段选项值, 因为是多选所以可能是多个值，所以用数组对像
			var value_filter = [];
			$.each(options, function(_k, _opt) {
				var result = _.find(value, function(_v) {
					return _v == _opt.tco_id;
				});

				// 如果不是当前选项
				if (!result) {
					return;
				}

				// 把找到的值放到数组对像里
				if ('2' != col.ftype) {
					value_filter.push(_opt.value);
				} else {
					value_filter.push(_opt);
				}
			});

			// 如果值不为空刚把他组成字符串以逗号分隔
			if (value_filter.length && '2' != col.ftype) { // 如果不是图片选项则直接返回值
				g["_" + col.tc_id] = value_filter.join(', ');
			} else { // 如果是图片选项则返回选项值
				g["_" + col.tc_id] = value_filter;
			}

			return g;
		},
		_single_selection: function (g, col, options) {
			// 如果是select radio 单选项则
			// 查找选项值，返回选项数据
			var option = _.find(options, function(_opt) {
				//值判断
				return _opt.tco_id == g["_" + col.tc_id];
			});
			// 如果存在选项，则设置自定义id的选项值
			if (option) {
				if ('2' != col.ftype) { // 如果不是图片选项则直接返回值
					g["_" + col.tc_id] = option.value;
				} else { // 如果是图片选项则返回选项值
					g["_" + col.tc_id] = option;
				}
			} else {
				g["_" + col.tc_id] = '';
			}

			return g;
		},
		/**
		 * 获取列属性值
		 * @param {object} col
		 * @param {object} g
		 * @returns {*}
		 */
		get_col_value: function(col, g) {
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
				if ('checkbox' == col.ct_type) {
					g = this._multi_value(g, col, options);
				} else { // 如果是select radio 单选项则
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
		}
	};

	return Customized;
});
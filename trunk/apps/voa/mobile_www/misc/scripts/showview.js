define(['zepto', 'underscore', 'frozen'], function($, _) {

	function ShowView() {

		this._aj_success = null;
		this._aj_error = null;
		this.tpl = null;
		this.element = null;
	}

	ShowView.prototype = {

		ajax_view: function(ajax, view, tpl) {

			if (_.isString(ajax)) {
				ajax = {"url": ajax};
			}

			// 如果 url 不存在
			if (!_.has(ajax, 'url') || !_.isString(ajax.url)) {
				return false;
			}

			// 判断 ajax 是否有获取数据成功的回调方法
			if (_.has(ajax, 'success') && _.isFunction(ajax.success)) {
				this._aj_success = ajax.success;
			}
			
			//获取参数
			this.element = $('#'+view);

			if(0 >= this.element.size()) {
				this.show_error('模板出错', null, null);
			}

			this.tpl = $('#'+tpl).html();

			if(_.isEmpty(tpl)) {
				this.show_error('展示的元素不存在', null, null);
			}
			
			var self = this;
			/**
			 * ajax 数据读取成功时的回调
			 * @param {*} data 返回数据
			 * @param {int} status 状态
			 * @param {XMLHttpRequest} xhr
			 */
			ajax.success = function(data, status, xhr) {

				// ajax 调用成功
				self._ajax_success(data, status, xhr);
			};

			// 错误处理方法
			if (_.has(ajax, 'error')) {
				this._aj_error = ajax.error;
			}

			/**
			 * 错误处理
			 * @param {XMLHttpRequest} xhr
			 * @param {int} errorType
			 * @param {string} error
			 * @returns {*}
			 */
			ajax.error = function(xhr, errorType, error) {

				// 调用错误处理方法
				if (null != self._aj_error) {
					return self._aj_error(xhr, errorType, error);
				}

				// 显示错误
				self.show_error('数据读取错误.', null, null);
				return true;
			};
			//执行ajax
			$.ajax(ajax);
			
			return true;
		},
		/**
		 * ajax 请求成功之后的回调
		 * @param {*} data
		 * @param {int} status
		 * @param {XMLHttpRequest} xhr
		 * @returns {*}
		 */
		_ajax_success: function(data, status, xhr) {

			// 预先处理数据
			if (null != this._aj_success) {
				data = this._aj_success(data, status, xhr);
			}
			if(_.isEmpty(data.result)) {
				this.show_error('数据读取错误.', null, null);
			} else {
				// 渲染页面
				this._render(data.result);
			}
			return data;
		},
		
		/**
		 * 渲染模板
		 * @param {*} data 数据
		 */
		_render: function(data) {
			
			// 解析模板, 返回解析后的内容
			var html = _.template(this.tpl, data);

			// 将解析后的内容填充到渲染元素
			this.element.html(html);
		},

		/**
		 * 错误提示
		 * @param {string} tips 错误提示文字
		 * @param {string} title
		 * @param {*} btns
		 */
		show_error: function(tips, title, btns) {

			// 错误提示标题
			if (_.isUndefined(title) || _.isNull(title)) {
				title = '错误提示';
			}

			// 错误窗口按钮
			if (_.isUndefined(btns) || _.isNull(btns)) {
				btns = ["确认"];
			} else if (_.isString(btns)) {
				btns = [btns];
			}

			// dailog
			var dialog = $.dialog({"title": title, "content": tips, "button": btns});
			dialog.on("dialog:hide", function(e) {
				// To do sth when dialog hide
				history.go(-1);
			});
			return true;
		}
	};

	return ShowView;
});

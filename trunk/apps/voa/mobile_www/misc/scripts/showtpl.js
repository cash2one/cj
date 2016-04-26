define(['zepto', 'underscore', 'frozen'], function($, _, fz) {

	function showtpl() {

		// 数据回调方法
		this.cb_success = null;
		// 数据读取错误回调方法
		this.cb_error = null;
		// 模板 dom 对象
		this.dom_tpl = null;
		// 容器 dom 对象
		this.dom_container = null;
		// 模板和容器关联后缀
		this.suffix = '_tpl';
		// 总数
		this.total = -1;
		// 当前页
		this.page = 1;
		// 分页数
		this.limit = 10;
	}

	// 成员方法
	showtpl.prototype = {
		/**
		 * 数据展示方法
		 * @param object ajax 纯粹的 ajax 参数
		 * @param dom tpl 模板对象
		 * @param dom container 展示区对象
		 */
		showajax: function(ajax, container, tpl) {

			var self = this;
			// 如果 ajax 是字串
			if (_.isString(ajax)) {
				ajax = {"url": ajax};
			}

			// 如果 ajax 不是对象
			if (!_.isObject(ajax)) {
				return false;
			}

			// 如果 url 不存在
			if (!_.has(ajax, 'url') || !_.isString(ajax.url)) {
				return false;
			}

			// 判断 ajax 是否有获取数据成功的回调方法
			if (_.has(ajax, 'success') && _.isFunction(ajax.success)) {
				this.cb_success = ajax.success;
			}

			/**
			 * ajax 数据读取成功时的回调
			 * @param mixed data 返回数据
			 * @param int status 状态
			 * @param object xhr
			 */
			ajax.success = function(data, status, xhr) {
				self.ajax_success(data, status, xhr);
			};

			// 错误处理方法
			if (_.has(ajax, 'error')) {
				this.cb_error = ajax.error;
			}

			/**
			 * 错误处理
			 * @param XMLHttpRequest xhr
			 * @param int errorType
			 * @param string error
			 * @returns {*}
			 */
			ajax.error = function(xhr, errorType, error) {

				// 调用错误处理方法
				if (null != self.cb_error) {
					return self.cb_error(xhr, errorType, error);
				}

				// 显示错误
				self.showerror('数据读取错误.');
				return true;
			};

			// 初始化容器和模板
			if (!this.init(container, tpl)) {
				this.showerror('容器或者模板不存在');
				return false;
			}

			// ajax 请求
			this.dom_container.data('ajax_req', ajax);
			// ajax 请求 url
			this.dom_container.data('ajax_urls', parseURL(ajax.url));
			// 读取数据
			this.request(ajax);
			return true;
		},
		ajax_success: function(data, status, xhr) {
			// 预先处理数据
			if (null != this.cb_success) {
				data = this.cb_success(data, status, xhr);
			}

			// 解析数据
			if (!this.parse_data(data)) {
				return false;
			}

			// 渲染页面
			self.render(data);
			return data;
		},
		parse_data: function(data) {

			if (!_.has(data, "errcode") || !_.has(data, "errmsg") || !_.has(data, "result")) {
				this.showerror("数据返回错误");
				return false;
			}

			if (0 != data.errcode) {
				this.showerror(data.errmsg);
				return false;
			}

			if (_.has(data, "page")) {
				this.page = parseInt(data.page);
			}

			if (_.has(data, "limit")) {
				this.limit = parseInt(data.limit);
			}

			if (_.has(data, "total")) {
				this.total = parseInt(data.total);
			}

			// 信息写入缓存
			this.dom_container.data("data-" + this.page, data);

			return true;
		},
		create_url: function(page, limit) {

			var url = '';
			var urls = this.dom_container.data('ajax_urls');
			// http 协议/域名
			url = urls.protocol + "://" + urls.host;
			// 端口
			if (urls.port) {
				url += ":" + urls.port;
			}

			// 请求的地址
			url += urls.path;
			// 参数
			var urlparams = [];
			var params = urls.params;

			// 如果指定了 page
			if (!_.isUndefined(page)) {
				params["page"] = page;
			}

			// 如果指定了分页数
			if (!_.isUndefined(limit)) {
				params["limit"] = limit;
			}

			for (var k in params) {
				array_push(urlparams, k + "=" + params[k]);
			}

			// 拼接参数
			if (0 < urlparams.length) {
				url += '?' + urlparams.join('&');
			}

			return url;
		},
		/**
		 * ajax 请求
		 * @param object ajax ajax请求
		 */
		request: function(ajax, page, limit) {

			ajax.url = this.create_url(page, limit);
			$.ajax(ajax);
			return true;
		},
		/**
		 * 初始化
		 * @param dom tpl 模板对象
		 * @param dom container 容器对象
		 * @returns {boolean}
		 */
		init: function(container, tpl) {

			// 如果容器 dom 存在
			container = $(container);
			if (0 == container.size()) {
				return false;
			}

			// 如果模板 dom 存在
			this.dom_container = container;
			tpl = $(tpl);
			if (0 == tpl.size()) {
				tpl = $('#' + this.dom_container.attr('id') + this.suffix);
			}

			// 如果容器不存在, 根据默认规则取模板
			if (0 == tpl.size()) {
				return false;
			}

			this.dom_tpl = tpl;

			return true;
		},
		/**
		 * 显示指定页面
		 * @param object data 数据对象
		 * @param dom tpl 模板对象
		 * @param dom container 容器对象
		 * @returns {boolean}
		 */
		showdata: function(data, container, tpl) {

			// 初始化容器和模板
			this.init(tpl, container);
			// 渲染页面
			this.render(data);
			return true;
		},
		/**
		 * 渲染页面
		 * @param object data 数据对象
		 * @returns {boolean}
		 */
		render: function(data) {

			// 如果容器对象为空
			if (null == this.dom_container) {
				return true;
			}

			// 解析模板
			this.dom_container.html(_.template(this.dom_tpl.html(), data));
			return true;
		},
		/**
		 * 错误提示
		 * @param string tips 错误提示文字
 		 */
		showerror: function(tips, title, btns) {

			// 错误提示标题
			if (_.isUndefined(title)) {
				title = '错误提示';
			}

			// 错误窗口按钮
			if (_.isUndefined(btns)) {
				btns = ["确认", "取消"];
			} else if (_.isString(btns)) {
				btns = [btns];
			}

			// dailog
			var dialog = $.dialog({"title": title, "content": tips, "button": btns});
			dialog.on("dialog:hide", function(e) {
				// To do sth when dialog hide
			});
			return true;
		}
	};

	return showtpl;
});

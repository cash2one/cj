/**
 * 该类适用于列表信息展示
 * Created by zhuxun37 on 15/2/11.
 *
 * 加入有如下模板:
 * <ul id="test"></ul>
 * <script id="test_tpl" type="text/template">
 * <ul class="ui-list ui-list-text ui-list-link ui-border-b">
 * <%_.each(data, function(item) {%>
 *     <li class="ui-border-t">
 *         <h4 class="ui-nowrap"><%=item.subject%></h4>
 *     </li>
 * <%});%>
 * </ul>
 * </script>
 *
 * 调用示例:
 * var st = new ShowList();
 * st.show_ajax({'url': '/api/project/get/list'}, {
 *     "dist": $('#test'), // 容器 dom 对象
 *     "tpl": $("#test_tpl"), // 显示模板 dom 对象(可选)
 *     "datakey": "list", // 数据列表键值(可选)
 *     // 回调方法, dom 为容器对象(可选)
 *     "cb": function(dom) {
 *         alert(dom.html());
 *     }
 * });
 */

define(['zepto', 'underscore', 'frozen'], function($, _) {

	function ShowList() {
		// ajax 数据回调方法
		this._cb_success = null;
		// ajax 数据读取错误回调方法
		this._cb_error = null;
		// 模板 dom 对象
		this._dom_tpl = null;
		// 容器 dom 对象
		this._dom_dist = null;
		// 模板和容器关联后缀
		this._suffix = "_tpl";
		// 总数
		this._total = -1;
		// 当前页
		this._page = 1;
		// 分页数
		this._limit = 10;
		// 总页数
		this._pages = -1;
		// tips
		this._tips = null;
		// 执行完后的回调方法
		this._cb = null;
		// 当前数据标签
		this._data_curtag = "data";
		this._data_tags = {};
		// 第一次展示标识
		this._render_first = true;
		// scroll top
		this._scroll_top = 0;
		// 返回数据列表的键值
		this._data_key = "list";
		// 数据读取状态
		this._data_loading = false;
		// 上/下一页的状态
		this._is_next = true;
		// 已经显示的页码
		this._page_showed = [];
	}

	ShowList.prototype = {
		/**
		 * 数据展示方法
		 * @param {object} ajax 纯粹的 ajax 参数
		 * @param {object} view 展示对象
		 * + {dom} dist 容器
		 * + {dom} tpl 模板
		 * + {function} cb 回调函数
		 */
		init: function(ajax, view) {

			// 初始化容器和模板
			if (!this.init_view(view)) {
				this.show_error('容器或者模板不存在', null, null);
				return false;
			}

			// 初始化 ajax
			if (!this.init_ajax(ajax, false)) {
				this.show_error('Ajax 信息初始化失败', null, null);
				return false;
			}

			// 监听 scroll
			var self = this;
			$(window).on("scroll", function(e) {

				return self._scroll_event(e);
			});

			return true || this.show_data;
		},
		/**
		 * 重新初始化
		 * @param {*} ajax
		 * @returns {boolean}
		 */
		reinit: function(ajax) {

			// 重新初始化 ajax
			this.init_ajax(ajax, true);
			this._page = 1;
			this._pages = -1;
			this._total = -1;
			this._page_showed = [];
			this._render_first = true;

			// 清空旧数据
			this._dom_dist.empty();
			this._request(ajax, this._get_page(this._page), this._get_limit(this._limit));
			return true;
		},
		/**
		 * 显示 ajax 请求的数据
		 * @param {*} ajax ajax 对象
		 * @param {*} view 显示对象
		 * @returns {boolean}
		 */
		show_ajax: function(ajax, view) {

			// 初始化
			if (!this.init(ajax, view)) {
				return false;
			}

			// 数据请求
			this._request(ajax, this._get_page(this._page), this._get_limit(this._limit));
			return true;
		},
		/**
		 * 显示指定页面
		 * @param {object} data 数据对象
		 * @param {object} view 展示对象
		 * + {dom} dist 容器
		 * + {dom} tpl 模板
		 * + {function} cb 回调函数
		 * @returns {boolean}
		 */
		show_data: function(data, view) {

			// 初始化容器和模板
			this.init_view(view);
			// 渲染页面
			this._render(data);

			return true;
		},
		/**
		 * scroll event 处理方法
		 * @param e 事件对象
		 * @returns {*}
		 * @private
		 */
		_scroll_event: function(e) {

			// 如果未滑动到底部, 则
			var scroll_top = this._scroll_top;
			this._scroll_top = $(window).scrollTop();
			if (this._scroll_top + $(window).height() > $(document).height() - 40) {
				// 如果是往上拖动, 则取下一页
				if (scroll_top < this._scroll_top) {
					this.next_page();
				}

				return true;
			}

			// 如果已经拖到最顶部
			if (this._scroll_top < 5) {
				if (scroll_top > this._scroll_top) {
					this.prev_page();
				}

				return true;
			}

			return false && e;
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
			if (null != this._cb_success) {
				data = this._cb_success(data, status, xhr);
			}

			// 解析数据
			if (!this._parse_data(data)) {
				return false;
			}

			// 渲染页面
			this._render(null);
			return data;
		},
		/**
		 * 数据解析
		 * @param {*} data
		 * @returns {boolean}
		 */
		_parse_data: function(data) {

			// 如果数据请求错误
			if (!_.has(data, "errcode") || !_.has(data, "errmsg") || !_.has(data, "result")) {
				this.show_error("数据返回错误", null, null);
				return false;
			}

			// 如果返回的了错误码
			if (0 != data["errcode"]) {
				this.show_error(data["errmsg"] + "(code:" + data["errcode"] + ")", null, null);
				return false;
			}

			var result = data.result;
			// 页码
			if (_.has(result, "page")) {
				this._page = parseInt(result.page);
				if (isNaN(this._page) || 0 == this._page) {
					this._page = 1;
				}
			}

			// 每页记录数
			if (_.has(result, "limit")) {
				this._limit = parseInt(result.limit);
				if (isNaN(this._limit) || 0 == this._limit) {
					this._limit = 10;
				}
			}

			// 总记录数
			if (_.has(result, "total")) {
				this._total = parseInt(result.total);
				if (isNaN(this._total) || 0 == this._total) {
					this._total = 1;
				}
			}

			// 计算总页码
			if (0 >= this._limit) {
				this._limit = 10;
			}

			var datalen = 0;
			if (_.has(result, this._data_key)) {
				datalen = result[this._data_key].length;
			}

			// 如果总数为 -1, 则认为有下一页
			if (-1 == this._total && _.has(result, this._data_key) && !_.isEmpty(result[this._data_key])) {
				if (datalen >= this._limit) {
					this._pages = this._page + 1;
				} else {
					this._pages = this._page;
				}
			} else {
				this._pages = Math.ceil(this._total / this._limit);
			}

			if (0 >= datalen) {
				return true;
			}

			// 信息写入缓存
			this._dom_dist.data(this._data_curtag + "-" + this._page, result);

			return true;
		},
		/**
		 * 创建新请求的 url
		 * @param {int} page
		 * @param {int} limit
		 * @param {*} params
		 * @returns {string}
		 */
		_create_url: function(page, limit, params) {

			var urls = this._dom_dist.data('ajax_urls');
			// http 协议/域名
			var url = urls.protocol + "://" + urls.host;
			// 端口
			if (urls.port) {
				url += ":" + urls.port;
			}

			// 请求的地址
			url += urls.path;
			// 参数
			var urlparams = [];
			if (_.isUndefined(params) || !_.isObject(params)) {
				params = {};
			} else {
				params = _.extend(params, urls.params);
			}

			// 如果指定了 page
			if (!_.isUndefined(page) && 0 < page) {
				params["page"] = page;
			}

			// 如果指定了分页数
			if (!_.isUndefined(limit) && 0 < limit) {
				params["limit"] = limit;
			}

			// 取键值
			var keys = _.keys(params);
			keys.sort();
			_.each(keys, function(key) {
				urlparams.push(key + "=" + params[key]);
			});

			// 拼接参数
			if (0 < urlparams.length) {
				url += '?' + urlparams.join('&');
			}

			return url;
		},
		/**
		 * ajax 请求
		 * @param {object} ajax ajax请求
		 * @param {int} page
		 * @param {int} limit
		 */
		_request: function(ajax, page, limit) {

			// ajax 请求
			if (!_.isObject(ajax) || _.isEmpty(ajax)) {
				ajax = this._dom_dist.data('ajax_req_' + this._data_curtag);
			}

			// 如果 page 未定义
			if (_.isUndefined(page) || _.isNull(page)) {
				page = this._page;
			}

			// 如果 limit 未定义
			if (_.isUndefined(limit) || _.isNull(limit)) {
				limit = this._limit;
			}

			// 如果有缓存
			var data = this._dom_dist.data(this._data_curtag + '-' + page);
			if (_.isObject(data)) {
				this._page = page;
				if (-1 === this._page_showed.indexOf(page)) {
					this._render(data);
				}

				return true;
			}

			// tips
			this._tips = $.loading({content:'加载中...'});
			// 读取数据
			ajax.url = this._create_url(page, limit, {});
			// 如果数据读取中
			if (true == this._data_loading) {
				return true;
			}

			this._is_next = !(this._page >= page);
			this._page = page;
			this._data_loading = true;
			$.ajax(ajax);
			return true;
		},
		// 下一页
		next_page: function() {

			var page = this._page + 1;
			if (this._pages < page) {
				page = this._pages;
			}

			if (this._pages <= 0 || page == this._page || null != this._tips) {
				return true;
			}

			return this._request(this._dom_dist.data('ajax_req_' + this._data_curtag), page, null);
		},
		// 上一页
		prev_page: function() {

			var page = this._page - 1;
			if (0 >= page) {
				page = 1;
			}

			if (page == this._page || null != this._tips) {
				return true;
			}

			return this._request(this._dom_dist.data('ajax_req_' + this._data_curtag), page, null);
		},
		/**
		 * 初始化
		 * @param {object} view 展示对象
		 * + {dom} dist 容器
		 * + {dom} tpl 模板
		 * + {function} cb 回调函数
		 * @returns {boolean}
		 */
		init_view: function(view) {

			if (_.isUndefined(view) || !_.isObject(view) || !_.has(view, "dist")) {
				return false;
			}

			// 如果容器 dom 不存在
			if (!this._init_dist(view.dist)) {
				return false;
			}

			// 如果容器不存在, 根据默认规则取模板
			if (!this._init_tpl(view.tpl)) {
				return false;
			}

			// 回调方法
			if (_.has(view, "cb") && _.isFunction(view.cb)) {
				this._cb = view.cb;
			}

			// 数据列表键值
			if (_.has(view, "datakey") && _.isString(view["datakey"])) {
				this._data_key = view["datakey"];
			}

			return true;
		},
		_init_tpl: function(tpl) {

			// 模板不存在
			if (_.isUndefined(tpl)) {
				tpl = '#' + this._dom_dist.attr('id') + this._suffix;
			}

			var dom = $(tpl);
			if (0 == dom.size()) {
				return false;
			}

			this._dom_tpl = dom;
			return true;
		},
		_init_dist: function(tpl) {

			if (_.isUndefined(tpl)) {
				return false;
			}

			var dom = $(tpl);
			// 如果容器 dom 不存在
			if (0 == dom.size()) {
				return false;
			}

			this._dom_dist = dom;
			return true;
		},
		/**
		 * 渲染页面
		 * @param {object} data 数据对象
		 * @returns {boolean}
		 */
		_render: function(data) {

			// 如果容器对象为空
			if (null == this._dom_dist) {
				return true;
			}

			if (_.isUndefined(data) || _.isNull(data)) {
				data = this._dom_dist.data(this._data_curtag + "-" + this._page);
				// 如果为空, 则赋一个空数组
				if (_.isUndefined(data)) {
					data = {};
					data[this._data_key] = [];
				}
			}

			// 解析模板
			if ((_.has(data, this._data_key) && !_.isEmpty(data[this._data_key])) || true == this._render_first) {
				if (true == this._is_next) {
					this._dom_dist.append(_.template(this._dom_tpl.html(), data));
				} else {
					this._dom_dist.prepend(_.template(this._dom_tpl.html(), data));
				}
			}

			this._page_showed.push(this._page);
			this._render_first = false;
			// 回调
			if (_.isFunction(this._cb)) {
				this._cb(this._dom_dist);
			}

			return true;
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
			});
			return true;
		},
		/**
		 * 获取页码
		 * @param {int} page 默认页码
		 * @returns {number}
		 * @private
		 */
		_get_page: function(page) {

			var curpage = 0;
			var urls = this._dom_dist.data('ajax_urls');
			if (_.has(urls.params, "page")) {
				curpage = parseInt(urls.params.page);
			}

			if (isNaN(curpage) || 0 >= curpage) {
				curpage = parseInt(page);
			}

			return isNaN(curpage) || 0 >= curpage ? this._page : curpage;
		},
		get_page: function() {

			return this._page;
		},
		set_page: function(page) {

			page = parseInt(page);
			if (isNaN(page) || 0 >= page) {
				return false;
			}

			this._page = page;
			return true;
		},
		/**
		 * 获取每页数据条数
		 * @param {int} limit 数据条数
		 * @returns {number}
		 * @private
		 */
		_get_limit: function(limit) {

			var curlimit = 0;
			var urls = this._dom_dist.data('ajax_urls');
			if (_.has(urls.params, "limit")) {
				curlimit = parseInt(urls.params.limit);
			}

			if (isNaN(curlimit) || 0 >= curlimit) {
				curlimit = parseInt(limit);
			}

			return isNaN(curlimit) || 0 >= curlimit ? this._limit : curlimit;
		},
		/**
		 * 初始化 ajax
		 * @param {*} ajax ajax 请求
		 * @param {bool} isrewrite 是否新请求
		 * @returns {boolean}
		 */
		init_ajax: function(ajax, isrewrite) {

			var self = this;
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
				this._cb_success = ajax.success;
			}

			/**
			 * ajax 数据读取成功时的回调
			 * @param {*} data 返回数据
			 * @param {int} status 状态
			 * @param {XMLHttpRequest} xhr
			 */
			ajax.success = function(data, status, xhr) {

				// 如果提示对象不为空
				if (!_.isNull(self._tips)) {
					self._tips.loading("hide");
					self._tips = null;
				}

				// ajax 调用成功
				self._data_loading = false;
				self._ajax_success(data, status, xhr);
			};

			// 错误处理方法
			if (_.has(ajax, 'error')) {
				this._cb_error = ajax.error;
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
				if (null != self._cb_error) {
					return self._cb_error(xhr, errorType, error);
				}

				// 显示错误
				self._data_loading = false;
				self.show_error('数据读取错误.', null, null);
				return true;
			};

			// ajax 请求 url
			var urls = parseURL(ajax.url);
			var ajax_data = {};
			// 合并请求参数
			if (_.has(ajax, "data")) {
				ajax_data = _.extend({}, ajax.data);
			}

			// 缓存 urls 对象
			this._dom_dist.data('ajax_urls', urls);
			// 生成请求 url
			var url = this._create_url(1, 1, ajax_data);
			// 如果 url 对应的 tag 存在
			if (_.has(this._data_tags, url)) {
				this._data_curtag = this._data_tags[url];
			} else {
				// 如果是重新初始化
				if (true == isrewrite) {
					this._data_curtag = _.uniqueId('__dct_');
				}

				// 缓存 url => tag
				this._data_tags[url] = this._data_curtag;
			}

			// 缓存 ajax 对象
			this._dom_dist.data('ajax_req_' + this._data_curtag, ajax);
			return true;
		}
	};

	return ShowList;
});

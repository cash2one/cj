/**
 * 该类适用于多 tab 的数据展示, 配合 frozenUI 的 tab 使用
 * Created by zhuxun37 on 15/2/15.
 *
 * 加入有如下模板:
 * <ul class="ui-tab-nav ui-border-b ui-tab">
 *     <li>用户</li>
 *     <li>部门</li>
 * </ul>
 * <ul id="userdp" class="ui-tab-content" style="width:300%">
 *     <li data-dist="user_ul"><ul class="ui-list ui-list-text ui-list-link ui-border-b content" id="user_ul"></ul></li>
 *     <li data-dist="dp_ul"><ul class="ui-list ui-list-text ui-list-link ui-border-b content" id="dp_ul"></ul></li>
 * </ul>
 * <script id="user_ul_tpl" type="text/template">
 * <%_.each(list, function(user) {%>
 * <li class="ui-border-t">
 *     <h4 class="ui-nowrap"><%=user.realname%></h4>
 * </li>
 * <%});%>
 * </script>
 * <script id="user_dp_tpl" type="text/template">
 * <%_.each(lists, function(dp) {%>
 * <li class="ui-border-t">
 *     <h4 class="ui-nowrap"><%=dp.name%></h4>
 * </li>
 * <%});%>
 * </script>
 *
 * 调用示例:
 * var st = new ShowTabs();
 * st.show({
 *     "dist": $('#userdp'), // 主容器
 *     "tabs": [
 *         {
 *             "dist": "user_ul", // tab 容器
 *             "datakey": "list", // 数据列表键值(可选)
 *             "ajax": {"url": "/api/addressbook/get/list"}, // ajax 请求
 *             // 读取数据时的回调(可选)
 *             "cb": function(dom) {
 *                 alert(dom.html());
 *             }
 *         },
 *         {
 *             "dist": "dp_ul", // tab 容器
 *             "ajax": {"url": "/api/addressbook/get/departments"} // ajax 请求
 *         }
 *     ]
 * });
 */

define(['zepto', 'underscore', 'frozen', 'showlist'], function($, _, fz, sl) {

	function ShowTabs() {

		// 父类初始化
		sl.call(this);
		// tabs
		this._tabs = null;
		// 切换动作
		this._toggle = false;
	}

	// 调用继承方法
	extend(ShowTabs, sl);

	$.extend(ShowTabs.prototype, {
		/**
		 * 数据展示方法
		 * @param {object} view 展示对象
		 * + {function} cb 回调函数
		 * + {*} dist 目标显示区对象
		 * + {string} tabs tab 相关信息
		 * + + {string} dist tab 显示区 dom 对象
		 * + + {object} ajax ajax 请求对象
		 */
		show: function(view) {

			var self = this;
			// 构造 tab 相关
			if (!_.has(view, "tabs") || _.isEmpty(view.tabs)) {
				return false;
			}

			this._tabs = [];
			this._data_curtag = null;
			// 遍历 tabs, 检查数据的合法性
			_.each(view.tabs, function(tab) {
				// 判断 id
				if (!_.has(tab, "dist")) {
					return true;
				}

				var dist = tab.dist;
				// 默认 tab
				if (null == self._data_curtag) {
					self._data_curtag = dist;
				}

				// 模板 id 初始化
				if (!_.has(tab, "tpl")) {
					tab["tpl"] = $("#" + dist + "_tpl");
				}

				// 总页数初始化
				if (!_.has(tab, "pages")) {
					tab["pages"] = -1;
				}

				// 总数初始化
				if (!_.has(tab, "total")) {
					tab["total"] = -1;
				}

				// 如果指定了列表键值
				if (!_.has(tab, "datakey") || !_.isString(tab["datakey"])) {
					tab["datakey"] = "list";
				}

				tab["init"] = false;
				tab["page_showed"] = [];
				self._tabs[dist] = tab;
			});

			// 显示 ajax 数据
			var ajax = {};
			var curtab = this._tabs[this._data_curtag];
			_.extend(ajax, curtab.ajax);
			// 如果指定了列表键值
			this._data_key = curtab["datakey"];
			// 显示 ajax 信息
			this.show_ajax(ajax, view);

			return true;
		},
		init_view: function(view) {

			// 如果 this._dom_dist 存在, 则说明已经初始化了
			if (null != this._dom_dist) {
				return true;
			}

			// 如果容器 dom 不存在
			if (!this._init_dist(view.dist)) {
				return false;
			}

			// tab
			var tab = new window.fz.Scroll('.ui-tab', {
				role: 'tab',
				autoplay: false,
				interval: 3000
			});

			var self = this;
			tab.on('beforeScrollStart', function(from, to) {

			});

			// tab 切换完成
			tab.on('scrollEnd', function(curpage) {

				// 保存已显示的页码
				self._tabs[self._data_curtag]["page_showed"] = self._page_showed;
				// 更换 tab 标签
				self._data_curtag = self._dom_dist.find(".current").data("dist");
				var curtab = self._tabs[self._data_curtag];
				var ajax = {};
				_.extend(ajax, curtab.ajax);

				// 如果当前 tab 未初始化, 则
				if (false == curtab.init) {
					self._render_first = true;
				} else {
					self._toggle = true;
				}

				// 如果指定了列表键值
				self._data_key = curtab["datakey"];
				self._pages = curtab.pages;
				self._total = curtab.total;
				self._page_showed = curtab.page_showed;
				self.show_ajax(ajax, {"dist": self._dom_dist});
				return true || curpage;
			});

			return true;
		},
		/**
		 * ajax 请求
		 * @param {object} ajax ajax请求
		 * @param {int} page
		 * @param {int} limit
		 */
		_request: function(ajax, page, limit) {

			// 如果已经有缓存数据, 则不重新读取
			if (true == this._tabs[this._data_curtag].init && true == this._toggle) {
				this._toggle = false;
				this._page --;
				return true;
			}

			return this.super(ajax, page, limit);
		},
		_render: function(data) {

			// 标识已初始化过该 tab
			var tab = this._tabs[this._data_curtag];
			tab.init = true;
			tab["ajax"]["url"] = this._create_url(this._page, this._limit, {});
			tab["pages"] = this._pages;
			tab["total"] = this._total;
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
			var cur_id = this._dom_dist.find(".current").data("dist");
			if (_.isUndefined(cur_id)) {
				return false;
			}

			// 解析模板
			if ((_.has(data, this._data_key) && !_.isEmpty(data[this._data_key])) || true == this._render_first) {
				$("#" + cur_id).append(_.template($(tab["tpl"]).html(), data));
			}

			this._page_showed.push(this._page);
			this._render_first = false;
			// 回调
			if (_.has(tab, "cb") && _.isFunction(tab.cb)) {
				tab.cb(this._dom_dist);
			}

			return true;
		}
	});

	return ShowTabs;
});

/**
 * 适用于用户选择
 * Created by zhuxun37 on 15/2/11.
 *
 * <div id="addrbook" class="ui-tab"></div>
 *
 * 调用示例:
 * var ab = new addrbook();
 * ab.show({
 *     "dist": $("#addrbook"),
 *     "src": $("#btn1"), // 触发对象
 *     "tabs": {
 *         "user": {
 *             "name": "选择用户",
 *             "input": $("#uids"),
 *             // uid: 当前操作的用户, checked: 选中(true)还是剔除(false)
 *             "cb": function(uid, checked) {
 *                 alert(uid + "," + checked);
 *             }
 *         },
 *         "dp": {
 *             "name": "选择部门",
 *             // dpid: 当前操作的部门id, checked: 选中(true)还是剔除(false)
 *             "cb": function(dpid, checked) {
 *                 alert(dpid + "," + checked);
 *             }
 *         }
 *     },
 *     "cb": function(ab) {
 *         alert("AAAAAA");
 *     }
 * });
 */

define(['zepto', 'underscore', 'frozen', 'showlist'], function($, _, fz, sl) {

	function AddrBook() {

		// 父类初始化
		sl.call(this);
		this.TAG_USER = "user";
		this.TAG_DP = "dp";
		// 通讯录
		this._section_ab = null;
		// 当前页面
		this._section_cur = null;
		// 已经选择的 uid/dpid
		this._uids = [];
		this._dpids = [];
		// 默认通讯录接口地址
		this._def_user_url = "/api/addressbook/get/list";
		// 部门信息接口
		this._def_dp_url = "/api/addressbook/get/departments";
		// tabs
		this._tabs = {};
		// 默认容器
		this._tabs_dist_id = "addrbook";
		// 内容容器
		this._content_dist = null;
		// 默认模板
		this._def_tpl = '<ul id="<%=tab_head%>" class="ui-tab-nav ui-border-b"><%_.each(tabs, function(tab) {%><li><%=tab.name%></li><%});%></ul><ul id="<%=dist%>" class="ui-tab-content" style="width:300%"><%$.each(tabs, function(index, tab) {%><li data-dist="<%=tab.dist%>" data-index="<%=index%>"><ul id="<%=tab.dist%>" class="addr_book ui-list ui-list-text ui-border-b"></ul></li><%});%></ul><div id="<%=tab_btn%>" class="ui-tab-nav ui-tab-nav-footer ui-border-t"><div class="ui-btn-group-tiled ui-btn-wrap"><button class="ui-btn clearfix">取消</button><button class="ui-btn ui-btn-primary">确认</button></div></div>';
		this._def_avatar_tpl = '<%_.each(list, function(user) {%><div class="ui-badge-wrap" data-uid="<%=user.uid%>"><div class="ui-badge-cornernum"></div><div class="ui-avatar-s"><span style="background-image:url(<%=user.face%>)"></span></div><div class="name"><%=user.realname%></div></div><%});%>';
		this._def_dpname_tpl = '<%_.each(lists, function(dp) {%><div class="ui-badge-wrap ui-border ui-contact-part" data-dpid="<%=dp.departmentid%>"><div class="ui-badge-cornernum"></div><span><%=dp.name%></div><%});%></span>';
		this._def_li_tpl = {};
		this._def_li_tpl[this.TAG_USER] = '<%_.each(list, function(user) {%><%if (_.has(user, "_groupkey")) {%><li class="ui-txt-muted"><%=user._groupkey%></li><%}%><li class="ui-border-t ui-checkbox"><input type="checkbox" id="ab_uid_<%=user["uid"]%>" value="<%=user.uid%>"<%if (-1 < _.indexOf(uids, user["uid"].toString())) {%> checked<%}%> /><div class="ui-nowrap"><div class="ui-avatar-s"><img src="<%=user.face%>" height="45" width="45"/></div><div class="ui-list-info"><h4><%=user.realname%></h4><p><%=user.department%></p></div></div></li><%});%>';
		this._def_li_tpl[this.TAG_DP] = '<%_.each(lists, function(dp) {%><li class="ui-border-t ui-checkbox"><input type="checkbox" id="ab_dpid_<%=dp["departmentid"]%>" value="<%=dp.departmentid%>"<%if (-1 < _.indexOf(dpids, dp["departmentid"].toString())) {%> checked<%}%> /><h4 class="ui-nowrap"><%=dp.name%></h4></li><%});%>';
		// 切换动作
		this._toggle = false;
		// 分页信息
		this._slice_num = 20;
		this._slices = 6;
		this._slice_start = 0;
		this._slice_end = this._slice_num * this._slices;
		// 数据列表
		this._list = [];
		// 源对象
		this._src = null;
		this._avatar_list = null;
		this._dpname_list = null;
		// 显示重画
		this._redraw = false;
		// 缓存信息
		this._users = {};
		this._departments = {};
		// 所有部门的标识
		this._dp_all = -1;
	}

	// 调用继承方法
	extend(AddrBook, sl);

	$.extend(AddrBook.prototype, {
		/**
		 * 数据展示方法
		 * @param {object} view 展示对象
		 * + {dom} dist 容器
		 * + {object} tabs tab 对象
		 * + + {string} name tab 名称
		 * + + {function} cb 选择时的回调函数
		 * + + {int} max 最多可选数
		 * + + {object} ajax 纯粹的 ajax 参数
		 */
		show: function(view) {

			var self = this;

			// 如果不存在源对象
			if (!_.has(view, "src")) {
				return false;
			}

			// 构造 tab 相关
			if (!_.has(view, "tabs") || _.isEmpty(view.tabs)) {
				this._tabs = {
					"user": {
						"name": "用户",
						"max": 1,
						"dist": _.uniqueId("addr_book_"),
						"ajax": {"url": this._def_user_url}
					}
				};
			} else {
				this._tabs = _.extend(this._tabs, view.tabs);
			}

			// 遍历 tabs, 检查数据的合法性
			this._data_curtag = null;
			_.each(this._tabs, function(tab, k) {

				// 判断 max
				if (!_.has(tab, "max")) {
					tab["max"] = 1;
				} else {
					tab["max"] = parseInt(tab["max"]);
				}

				// 如果 max 非数字, 则忽略
				if (_.isNaN(tab["max"])) {
					return true;
				}

				// 如果数据键值为空, 则指定当前的
				if (_.isNull(self._data_curtag)) {
					self._data_curtag = k;
				}

				// 判断 id
				if (!_.has(tab, "dist")) {
					tab["dist"] = _.uniqueId("addr_book_");
				}

				// 判断 ajax 是否存在, 如果未指定, 则使用默认地址
				if (!_.has(tab, "ajax")) {
					tab["ajax"] = {"url": "user" == k ? self._def_user_url : self._def_dp_url};
				}

				// 最大页数
				if (!_.has(tab, "pages")) {
					tab["pages"] = -1;
				}

				// 总记录数
				if (!_.has(tab, "total")) {
					tab["total"] = -1;
				}

				// 隐藏输入框(记录所选值)
				if (!_.has(tab, "input")) {
					var dom = $(tab["input"]);
					if (0 < dom.size()) {
						tab["input"] = dom;
					} else {
						delete tab["input"];
					}
				}

				// 如果指定了列表键值
				if (!_.has(tab, "datakey") || !_.isString(tab["datakey"])) {
					tab["datakey"] = "list";
				}

				tab["init"] = false;
				self._tabs[k] = tab;
			});

			// 显示 ajax 数据
			var ajax = {};
			var curtab = this._tabs[this._data_curtag];
			_.extend(ajax, curtab.ajax);

			// 完成选择时回调
			if (_.has(view, "cb") && _.isFunction(view.cb)) {
				this._cb = view.cb;
			}

			if (!this.init(ajax, view)) {
				return false;
			}

			// 初始化 section 动画
			this._init_section(view);

			return true;
		},
		_init_dp_show: function() {

			this._dpname_list = this._src.find("._dpname_list");
			if (0 >= this._dpname_list.size()) {
				return true;
			}

			// 如果有接收
			var ids = "";
			if (_.has(this._tabs, this.TAG_DP) && _.has(this._tabs[this.TAG_DP], "input")) {
				ids = this._tabs[this.TAG_DP]["input"].val();
				this._dpids = 0 < ids.length ? ids.split(",") : [];
			}

			// 部门删除操作
			var self = this;
			this._dpname_list.on("tap", ".ui-badge-cornernum", function(e) {

				var dpid = $(this).parent().data("dpid");
				self._dpids = _.without(self._dpids, dpid.toString());
				self._tabs[self.TAG_DP]["input"].val(self._uids.join(","));
				// 清除选中状态
				var dp = $("#ab_dpid_" + dpid);
				if (0 < dp.size()) {
					dp.prop("checked", false);
				}

				// 更新部门信息
				self._update_department();
				return true || e;
			});

			return true;
		},
		_init_user_show: function() {

			this._avatar_list = this._src.find("._addrbook_list");
			if (0 >= this._avatar_list.size()) {
				return true;
			}

			// 如果有接收
			var ids = "";
			if (_.has(this._tabs, this.TAG_USER) && _.has(this._tabs[this.TAG_USER], "input")) {
				ids = this._tabs[this.TAG_USER]["input"].val();
				this._uids = 0 < ids.length ? ids.split(",") : [];
			}

			// 头像删除操作
			var self = this;
			this._avatar_list.on("tap", ".ui-badge-cornernum", function(e) {

				var uid = $(this).parent().data("uid");
				self._uids = _.without(self._uids, uid.toString());
				self._tabs[self.TAG_USER]["input"].val(self._uids.join(","));
				// 清除选中状态
				var user = $("#ab_uid_" + uid);
				if (0 < user.size()) {
					user.prop("checked", false);
				}

				// 更新头像
				self._update_avatar();
				return true || e;
			});

			return true;
		},
		/**
		 * 初始化 section 动画
		 * @param {object} view
		 * @private
		 */
		_init_section: function(view) {

			// 通讯录对象
			this._section_ab = $(this._dom_dist.parent());
			if (_.has(view, "section")) {
				this._section_ab = $(view["section"]);
			}

			// 当前显示对象
			this._section_cur = this._section_ab.prev("section");
			this._src = $(view["src"]);
			// 初始化用户显示
			this._init_user_show();
			this._init_dp_show();

			// 页面切换到通讯录
			var self = this;
			var src = this._src.find("a.ui-icon-add");
			src.on("tap", function(e) {
				self._dom_dist.css("display", "block");
				self._section_ab.css({'display': 'block'});
				self._section_ab.addClass('ani_start slider_right_in');

				setTimeout(function() { // 动画结束时重置class
					self._section_cur.css({'display': 'none'});
					self._section_ab.removeClass('ani_start slider_right_in');
				}, 350);

				// 如果有接收
				self._init_uids();
				self._init_dpids();

				// 分页参数
				var urls = self._dom_dist.data('ajax_urls');
				var page = _.has(urls.params, "page") ? parseInt(urls.params.page) : 1;
				var limit = _.has(urls.params, "limit") ? parseInt(urls.params.limit) : 10;
				if (isNaN(page) || 0 == page) {
					page = 1;
				}

				if (isNaN(limit) || 0 == limit) {
					limit = 10;
				}

				self._request({}, page, limit);
				return true || e;
			});
			this._dom_dist.css("display", "none");

			return true;
		},
		// 关闭
		_close_section: function() {

			// 页面切回到操作页面
			var self = this;
			this._redraw = true;
			this._dom_dist.css("display", "none");
			this._section_cur.css({'display': 'block'});
			this._section_cur.addClass('ani_start slider_right_in');

			setTimeout(function() { // 动画结束时重置class
				self._section_ab.css({'display': 'none'});
				self._section_cur.removeClass('ani_start slider_right_in');
			}, 350);
			return true;
		},
		_init_uids: function() {

			if (!_.has(this._tabs, this.TAG_USER)) {
				return true;
			}

			// 如果有接收
			var curtab = this._tabs[this.TAG_USER];
			if (_.has(curtab, "input")) {
				var ids = curtab["input"].val();
				this._uids = 0 < ids.length ? ids.split(",") : [];
			}

			return true;
		},
		_init_dpids: function() {

			if (!_.has(this._tabs, this.TAG_DP)) {
				return true;
			}

			// 如果有接收
			var curtab = this._tabs[this.TAG_DP];
			if (_.has(curtab, "input")) {
				var ids = curtab["input"].val();
				this._dpids = 0 < ids.length ? ids.split(",") : [];
			}

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
			// 如果显示区域的顶部已经过来 2/3
			if (this._scroll_top + $(window).height() > $(document).height() * 2 / 3) {
				// 如果是往上拖动, 则取下一页
				if (scroll_top < this._scroll_top) {
					this.next_page();
				}

				return false;
			}

			// 如果显示区域少于 1/3
			/**if (this._scroll_top < $(document).height() / 3) {
				if (scroll_top > this._scroll_top) {
					this.prev_page();
				}

				return false;
			}*/

			return false && e;
		},
		// 下一页
		next_page: function() {

			// 如果已经到了末尾
			if (this._slice_end + 1 >= this._list.length) {
				return true;
			}

			// 交互数据量
			var slice_num = this._slice_num * 6;
			// 如果剩余数不足, 则需要重新计算
			if (this._list.length < this._slice_end + slice_num) {
				slice_num = this._list.length - this._slice_end;
			}

			// 数据显示区域 id
			var distid = this._content_dist.find(".current").data("dist");
			// 显示容器对象
			var content = $("#" + distid);
			var scroll_top = $(window).scrollTop();
			//var h = content.height();
			var data = {};
			// 读取待新增的数据列表
			data[this._data_key] = this._list.slice(this._slice_end, this._slice_end + slice_num);
			// 重新计算展示的开始结束序号
			this._slice_start += slice_num;
			this._slice_end += slice_num;

			// 剔除前半部分 li
			/**content.find("li").each(function(index, item) {
				// 如果需要剔除
				if (index < slice_num) {
					$(item).remove();
					// 如果是分组 li
					if ($(item).hasClass("ui-txt-muted")) {
						slice_num ++;
					}
				}

				return true;
			});*/

			// 恢复 scroll top
			//$(window).scrollTop(scroll_top + content.height() - h);
			// 在尾部追加相同数量的数据
			if (this.TAG_USER == this._data_curtag) {
				data["uids"] = this._uids;
			} else {
				data["dpids"] = this._dpids;
			}

			content.append(_.template($("#" + distid + "_tpl").html(), data));
			return true;
		},
		// 上一页
		prev_page: function() {

			// 如果已经是最开始位置
			if (0 == this._slice_start) {
				return true;
			}

			// 数据显示区域 id
			var distid = this._content_dist.find(".current").data("dist");
			// 显示容器对象
			var content = $("#" + distid);
			// 交互数据量
			var slice_num = this._slice_num;
			// 当前 scroll top
			var scroll_top = $(window).scrollTop();
			// 如果剩余数不足, 则需要重新计算
			if (this._slice_start < slice_num) {
				slice_num = this._slice_start;
			}

			// 取出待新增的数据
			var data = {};
			data[this._data_key] = this._list.slice(this._slice_start - slice_num, this._slice_start);
			// 重新计算展示的开始结束序号
			this._slice_start -= slice_num;
			this._slice_end -= slice_num;
			// 剔除后半部分 li
			var lis = content.find("li");
			for (var i = lis.length - 1; i >= 0; -- i) {
				// 如果需要剔除
				if (0 >= slice_num) {
					break;
				}

				$(lis[i]).remove();
				// 如果是分组 li
				if (!$(lis[i]).hasClass("ui-txt-muted")) {
					slice_num --;
				}
			}

			// 在头部追加相同数量的数据
			var h = content.height();
			if (this.TAG_USER == this._data_curtag) {
				data["uids"] = this._uids;
			} else {
				data["dpids"] = this._dpids;
			}

			content.prepend(_.template($("#" + distid + "_tpl").html(), data));
			// 恢复 scroll top
			$(window).scrollTop(scroll_top - h + content.height());
			return true;
		},
		// 初始化目标区域
		_init_dist: function(tpl) {

			if (_.isUndefined(tpl)) {
				return false;
			}

			var dom = $(tpl);
			// 如果容器 dom 不存在
			if (0 == dom.size()) {
				return false;
			}

			dom.css("display", "none");
			var dist = dom.clone();
			dist.attr("id", _.uniqueId("ab_"));
			dist.css("display", "block");
			dom.parent().prepend(dist);
			this._dom_dist = dist;
			return true;
		},
		// 初始化显示区域
		init_view: function(view) {

			// 如果 this._dom_dist 存在, 则说明已经初始化了
			if (null != this._dom_dist) {
				return true;
			}

			// 未定义展示容器
			if (_.isUndefined(view) || !_.isObject(view) || !_.has(view, "dist")) {
				view = {"dist": $("#" + this._tabs_dist_id)};
			}

			// 如果容器 dom 不存在
			if (!this._init_dist(view.dist)) {
				return false;
			}

			// 显示
			this._dom_dist.parent().css("display", "block");

			var self = this;
			var wap = $("#" + window._append_parent);
			// 如果模板未定义
			_.each(this._tabs, function(tab, k) {
				wap.append('<script id="' + tab.dist + '_tpl" type="text/template">' + self._def_li_tpl[k] + '</script>');
			});

			// 头像模板
			wap.append('<script id="' + this._dom_dist.attr("id") + '_avatar_tpl" type="text/template">' + this._def_avatar_tpl + '</script>');
			// 部门名字模板
			wap.append('<script id="' + this._dom_dist.attr("id") + '_dpname_tpl" type="text/template">' + this._def_dpname_tpl + '</script>');

			// 把模板放入容器
			var content_id = _.uniqueId(this._dom_dist.attr("id"));
			var tab_head = _.uniqueId(this._dom_dist.attr("id"));
			var tab_btn = _.uniqueId(this._dom_dist.attr("id"));
			this._dom_dist.html(_.template(this._def_tpl, {
				"tabs": this._tabs,
				"dist": content_id,
				"tab_head": tab_head,
				"tab_btn": tab_btn
			}));
			// 计算显示区高度
			this._content_dist = $("#" + content_id);
			this._content_dist.height($(window).height() - $("#" + tab_head).height() - $("#" + tab_btn));

			// 监听取消/确定按钮事件
			this._dom_dist.find("button.ui-btn-primary").on("tap", function(e) {

				return self.submit(e);
			});

			this._dom_dist.find("button.clearfix").on("tap", function(e) {

				return self.close(e);
			});

			// 监听 .ui-checkbox 的 tap 事件(将其代理到 this._content_dist 上)
			this._content_dist.on("tap", ".ui-checkbox", function(e) {

				var ipt = $(this).find("input");
				// 如果标识不可用, 则
				if (self._data_curtag == self.TAG_DP && self._dp_all != ipt.val() && 0 < self._dpids.length && self._dp_all == self._dpids[0]) {
					return true;
				}

				var checked = ipt.prop("checked") ? false : true;
				ipt.prop("checked", checked);

				// 选择/剔除操作
				self._toggle_select(ipt.val(), checked);
				// tab 选择时回调方法
				var curtab = self._tabs[self._data_curtag];
				if (_.has(curtab, "cb") && _.isFunction(curtab.cb)) {
					curtab.cb(ipt.val(), checked);
				}

				return true || e;
			});

			// tab
			var tab = new window.fz.Scroll('#' + this._dom_dist.attr("id"), {
				role: 'tab',
				autoplay: false,
				interval: 3000
			});

			tab.on('beforeScrollStart', function(from, to) {

				self._data_curtag = self._content_dist.find(".current").data("index");
				var curtab = self._tabs[self._data_curtag];
				curtab["slice_start"] = self._slice_start;
				curtab["slice_end"] = self._slice_end;
				return true || from || to;
			});

			tab.on('scrollEnd', function(curpage) {

				self._redraw = false;
				self._data_curtag = self._content_dist.find(".current").data("index");
				var curtab = self._tabs[self._data_curtag];
				var ajax = {};
				_.extend(ajax, curtab.ajax);

				if (false == curtab.init) {
					self._render_first = true;
					self._slice_start = 0;
					self._slice_end = self._slice_num * self._slices;
				} else {
					self._slice_start = curtab["slice_start"];
					self._slice_end = curtab["slice_end"];
				}

				self._data_key = curtab["datakey"];
				self._toggle = true;
				self._pages = curtab.pages;
				self._total = curtab.total;
				self._list = [];
				self.show_ajax(ajax, {"dist": self._content_dist});
				return true || curpage;
			});

			// 隐藏
			this._dom_dist.parent().css("display", "none");

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
			if ((true == this._tabs[this._data_curtag].init && true == this._toggle) || true == this._redraw) {

				this._toggle = false;
				// 如果列表为空
				var data = this._dom_dist.data(this._data_curtag + "-" + this._page);
				if (_.isEmpty(this._list)) {
					this._list = this._resort(data[this._data_key]);
				}

				return true;
			}

			return this.super(ajax, page, limit);
		},
		/**
		 * 数据解析
		 * @param {*} data
		 * @returns {boolean}
		 */
		_parse_data: function(data) {

			// 如果父类解析错误, 则
			if (!this.super(data)) {
				return false;
			}

			// 重新整理数据并保存
			this._list = this._resort(data["result"][this._data_key]);
			return true
		},
		/**
		 * 对数据进行排序
		 * @param {object} data 待排序的数据
		 * @returns {*}
		 * @private
		 */
		_resort: function(data) {

			// 如果是部门列表, 则不需要整理
			if (this._data_curtag == this.TAG_DP) {
				return this._resort_dpnames(data);
			}

			return this._resort_users(data);
		},
		_resort_dpnames: function(data) {

			var self = this;
			_.each(data, function(dp) {

				// 一级部门即: 公司名称
				if (0 >= dp["parentid"]) {
					dp["departmentid"] = "-1";
				}

				self._departments[dp["departmentid"]] = dp;
			});
			return data;
		},
		_resort_users: function(data) {

			// 取数据列表
			var list = {};
			var keys = [];
			var self = this;
			// 按首字母分组
			_.each(data, function(usr) {
				var gk = 0 < usr["department"].length ? usr["department"] : '#';
				// 如果 list 中不存在当前用户的前缀
				if (!_.has(list, gk)) {
					list[gk] = [];
					usr["_groupkey"] = gk;
					keys.push(gk);
				}

				list[gk].push(usr);
				self._users[usr["uid"]] = usr;
			});

			// 合并数组
			var rets = [];
			keys.sort();
			_.each(keys, function(k) {
				rets = rets.concat(list[k]);
			});

			return rets;
		},
		/**
		 * 显示
		 * @param {object} data 待显示数据
		 * @returns {boolean}
		 * @private
		 */
		_render: function(data) {

			// 标识已初始化过该 tab
			var tab = this._tabs[this._data_curtag];
			tab.init = true;
			tab["ajax"]["url"] = this._create_url(this._page, this._limit);
			tab["pages"] = this._pages;
			tab["total"] = this._total;
			// 如果容器对象为空
			if (null == this._dom_dist) {
				return true;
			}

			// 如果数据为空
			if (_.isUndefined(data) || _.isNull(data)) {
				data = this._dom_dist.data(this._data_curtag + "-" + this._page);
			}

			// 如果列表为空
			if (_.isEmpty(this._list)) {
				this._list = this._resort(data[this._data_key]);
			}

			// 解析模板
			var distid = this._content_dist.find(".current").data("dist");
			if (_.isUndefined(distid)) {
				return false;
			}

			// 解析模板
			var content = $("#" + distid);
			if ((_.has(data, this._data_key) && !_.isEmpty(data[this._data_key])) || true == this._render_first) {
				var list = {};
				if (this.TAG_USER == this._data_curtag) {
					list["uids"] = this._uids;
				} else {
					list["dpids"] = this._dpids;
				}

				list[this._data_key] = this._list.slice(this._slice_start, this._slice_end);
				content.append(_.template($("#" + distid + "_tpl").html(), list));
			}

			this._render_first = false;

			return true;
		},
		// 取消/关闭
		close: function(e) {

			this._close_section();
			// 恢复被修改的数据
			this._init_dpids();
			this._init_uids();
			// 修改选中状态
			var self = this;
			var distid = this._content_dist.find(".current").data("dist");
			$("#" + distid).find("input").each(function(index, ipt) {

				var uid = $(ipt).val();
				if (-1 === _.indexOf(self._uids, uid)) {
					$(ipt).prop("checked", false);
					return true || index;
				}

				$(ipt).prop("checked", true);
				return true || index;
			});

			// 如果有回调方法
			if (_.isFunction(this._cb)) {
				this._cb(this._dom_dist);
			}

			return true || e;
		},
		// 确定
		submit: function(e) {

			this._close_section();
			// 如果存在接收数据的 input
			if (_.has(this._tabs, this.TAG_USER) && _.has(this._tabs[this.TAG_USER], "input")) {
				this._tabs[this.TAG_USER]["input"].val(this._uids.join(","));
				this._update_avatar();
			}

			// 如果存在接收数据的 input
			if (_.has(this._tabs, this.TAG_DP) && _.has(this._tabs[this.TAG_DP], "input")) {
				this._tabs[this.TAG_DP]["input"].val(this._dpids.join(","));
				this._update_department();
			}

			// 如果有回调方法
			if (_.isFunction(this._cb)) {
				this._cb(this._dom_dist);
			}

			return true || e;
		},
		_update_department: function() {

			// 如果部门县市区为空
			if (0 >= this._dpname_list.size()) {
				return true;
			}

			var list = [];
			var dpids = this._dpids;
			this._dpname_list.find(".ui-badge-wrap").each(function(index, dpname) {
				var atr = $(dpname);
				var dpid = atr.data("dpid").toString();
				if (-1 < _.indexOf(dpids, dpid)) {
					dpids = _.without(dpids, dpid);
					return true;
				}

				atr.remove();
			});

			var self = this;
			_.each(dpids, function(dpid) {
				// 如果 dpid 不正确
				dpid = parseInt(dpid);
				if (_.isNaN(dpid) || 0 == dpid || !_.has(self._departments, dpid)) {
					return true;
				}

				list.push(self._departments[dpid]);
			});

			// 如果新增用户为空
			if (0 == dpids.length) {
				return true;
			}

			this._dpname_list.append(_.template($("#" + this._dom_dist.attr("id") + "_dpname_tpl").html(), {"lists": list}));
		},
		// 更新头像
		_update_avatar: function() {

			// 如果头像显示区为空
			if (0 >= this._avatar_list.size()) {
				return true;
			}

			var list = [];
			var uids = this._uids;

			// 遍历已存在的头像
			this._avatar_list.find(".ui-badge-wrap").each(function(index, avatar) {
				var atr = $(avatar);
				var uid = atr.data("uid").toString();
				if (-1 < _.indexOf(uids, uid)) {
					uids = _.without(uids, uid);
					return true;
				}

				atr.remove();
			});

			var self = this;
			_.each(uids, function(uid) {
				// 如果 uid 不正确
				uid = parseInt(uid);
				if (_.isNaN(uid) || 0 >= uid || !_.has(self._users, uid)) {
					return true;
				}

				list.push(self._users[uid]);
			});

			// 如果新增用户为空
			if (0 == uids.length) {
				return true;
			}

			this._avatar_list.append(_.template($("#" + this._dom_dist.attr("id") + "_avatar_tpl").html(), {"list": list}));
		},
		// 选择用户/部门
		_toggle_select: function(id, checked) {

			// 选择用户
			if (this.TAG_USER == this._data_curtag) {
				if (true == checked) { // 选中
					this._select_uid(id);
				} else { // 剔除
					this._uids = _.without(this._uids, id);
				}
			} else if (this.TAG_DP == this._data_curtag) { // 选择部门
				if (true == checked) { // 选中
					this._select_dpid(id);
				} else { // 剔除
					this._dpids = _.without(this._dpids, id);
				}
			}

			return true;
		},
		_select_dpid: function(dpid) {

			var curtab = this._tabs[this.TAG_DP];
			var department;

			// 如果当前操作的是公司
			if (-1 == dpid) {
				department = $("#ab_dpid_" + dpid);
				// 如果公司被选中
				if (0 < department.size() && department.prop("checked")) {
					this._content_dist.find(".ui-checkbox").each(function(index, li) {
						var ipt = $(li).find("input");
						if (dpid == ipt.val()) {
							return true;
						}

						ipt.prop("checked", false);
					});

					this._dpids = [dpid];
				}

				return true;
			}

			// 如果只能选择一个
			if (1 == curtab["max"]) {
				// 清除部门选中状态
				department = $("#ab_dpid_" + this._dpids.pop());
				if (0 < department.size()) {
					department.prop("checked", false);
				}

				this._dpids = [dpid];
				return true;
			}

			// 如果 max 小于 1, 则说明不限
			if (1 > curtab["max"]) {
				this._dpids.push(dpid);
				return true;
			}

			// 判断是否达到最大值
			if (curtab["max"] <= this._dpids.length) {
				// 清除选中状态
				department = $("#ab_dpid_" + dpid);
				if (0 < department.size()) {
					department.prop("checked", false);
				}

				$.tips({
					"content": "最多只能选择 " + curtab["max"] + " 个部门",
					"type": "success",
					"stayTime": 1000
				});
				return true;
			}

			this._dpids.push(dpid);
			return true;
		},
		_select_uid: function(uid) {

			var curtab = this._tabs[this.TAG_USER];
			var user;
			// 如果只能选择一个
			if (1 == curtab["max"]) {
				// 清除选中状态
				user = $("#ab_uid_" + this._uids.pop());
				if (0 < user.size()) {
					user.prop("checked", false);
				}

				this._uids = [uid];
				return true;
			}

			// 如果 max 小于 1, 则说明不限
			if (1 > curtab["max"]) {
				this._uids.push(uid);
				return true;
			}

			// 判断是否达到最大值
			if (curtab["max"] <= this._uids.length) {
				// 清除选中状态
				user = $("#ab_uid_" + uid);
				if (0 < user.size()) {
					user.prop("checked", false);
				}

				$.tips({
					"content": "最多只能选择 " + curtab["max"] + " 人",
					"type": "success",
					"stayTime": 1000
				});
				return true;
			}

			this._uids.push(uid);
			return true;
		},
		// 获取/设置所有uid
		uids: function(uids) {

			if (_.isUndefined(uids)) {
				return this._uids;
			}

			this._uids = uids;
		},
		// 获取/设置部门id
		dpids: function(dpids) {

			if (_.isUndefined(dpids)) {
				return this._dpids;
			}

			this._dpids = dpids;
		}
	});

	return AddrBook;
});

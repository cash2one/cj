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
 *     "selectall": false,
 *     "ac": "byuser", // 通过当前用户, 读取有权限的用户列表
 *     "tabs": {
 *         "user": {
 *             "input": $("#uids"),
 *             // uid: 当前操作的用户, checked: 选中(true)还是剔除(false)
 *             "cb": function(uid, checked) {
 *                 alert(uid + "," + checked);
 *             }
 *         },
 *         "dp": {
 *             // cdid: 当前操作的部门id, checked: 选中(true)还是剔除(false)
 *             "cb": function(cdid, checked) {
 *                 alert(cdid + "," + checked);
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
		// 已经选择的 uid/cdid
		this._uids = [];
		this._cdids = [];
		// 默认通讯录接口地址
		this._def_user_url = "/api/addressbook/get/list";
		// 部门信息接口
		this._def_dp_url = "/api/addressbook/get/departments";
		// tabs
		this._tabs = {};
		// 默认容器
		this._dist_id = "addrbook";
		// 搜索框
		this._kw_id = "abkw";
		this._last_val = "";
		// 选择显示区
		this._show_select_id = "show_select_id";
		// 内容容器
		this._content_dist = null;
		// 是否允许选择所有人/部门
		this._selectall = false;
		// 顶级部门cd_id
		this._top_cd_id = 0;
		// 唯一值
		this._unique_salt = _.uniqueId('ab_');
		// 默认模板
		this._tpl = '<div class="ui-searchbar-wrap focus ui-addr-search ui-tab-nav ui-border-b">' +
						'<div class="mod_photo_uploader search-icon"><div class="photo-scrollable" id="<%=show_select_id%>"></div></div>' +
						'<div class="ui-searchbar"><input value="" type="text" placeholder="搜索" id="<%=kw_id%>" autocapitalize="off"></div>' +
					'</div>' +
					'<ul id="<%=dist%>" class="addrbook ui-addr-book ui-border"></ul>' +
					'<div id="<%=tab_btn%>" class="ui-tab-nav ui-tab-nav-footer ui-border-t">' +
						'<div class="ui-btn-group-tiled ui-btn-wrap">' +
							'<button class="ui-btn clearfix">取消</button>' +
							'<button class="ui-btn ui-btn-primary">确认</button>' +
						'</div>' +
					'</div>';
		this._only_dp_tpl = '<li<%if (false == selectall) {%> style="display: none;"<%}%>><ul data-cdid="-1" id="' + this.create_id('ad_group') + '-1" class="_addrbook_group addrbook_group ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section">' +
								'<li class="_addrbook_li addrbook_li ui-border-b ui-checkbox addrbook_dp">' +
									'<input type="checkbox" class="addrbook_cdid" id="' + this.create_id('ab_cdid') + '-1" value="-1" />' +
									'<h4 class="ui-nowrap">全部人员/部门</h4>' +
								'</li>' +
							'</ul></li>' +
							'<%_.each(list, function(dp) {%>' +
							'<li>' +
								'<ul data-cdid="<%=dp.cd_id%>" id="' + this.create_id('ad_group') + '<%=dp.cd_id%>" class="_addrbook_group addrbook_group ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section">' +
									'<li class="_addrbook_li addrbook_li ui-border-b ui-checkbox addrbook_dp">' +
										'<input type="checkbox" class="addrbook_cdid" id="' + this.create_id('ab_cdid') + '<%=dp.cd_id%>" value="<%=dp.cd_id%>"<%if (-1 < _.indexOf(cdids, dp.cd_id.toString())) {%> checked<%}%> />' +
										'<h4 class="ui-nowrap"><%=dp.cd_name%></h4>' +
									'</li>' +
								'</ul>' +
							'</li>' +
							'<%});%>';
		this._group_tpl = '<li<%if (false == selectall) {%> style="display: none;"<%}%>><ul data-cdid="-1" id="' + this.create_id('ad_group') + '-1" class="_addrbook_group addrbook_group ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section">' +
								'<li class="_addrbook_li addrbook_li ui-border-b ui-checkbox addrbook_dp">' +
									'<input type="checkbox" class="addrbook_cdid" id="' + this.create_id('ab_cdid') + '-1" value="-1" />' +
									'<h4 class="ui-nowrap">全部人员/部门</h4>' +
								'</li>' +
							'</ul></li>' +
							'<%_.each(list, function(dp) {%>' +
							'<li>' +
							'<ul data-cdid="<%=dp.cd_id%>" id="' + this.create_id('ad_group') + '<%=dp.cd_id%>" class="_addrbook_group addrbook_group ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section">' +
								'<li class="ui-border-b ui-checkbox addrbook_dp ui-form-item-link ui-background-default">' +
									'<h4 class="ui-nowrap<%if (0 >= dp.cd_id) {%> ui-text-color-info<%}%>"><%=dp.cd_name%></h4>' +
								'</li>' +
							'</ul>' +
							'<ul id="' + this.create_id('ad_group_list') + '<%=dp.cd_id%>" data-init="0" class="ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section"></ul>' +
							'</li>' +
							'<%});%>';
		this._user_tpl = '<%_.each(list, function(user) {%>' +
							'<li class="_addrbook_li addrbook_li ui-border-t ui-checkbox">' +
								'<input type="checkbox" class="addrbook_uid" data-relation="ab_uid_<%=user.uid%>_so" id="' + this.create_id('ab_uid') + '<%=user.uid%>" value="<%=user.uid%>"<%if (-1 < _.indexOf(uids, user.uid.toString())) {%> checked<%}%> />' +
								'<div class="ui-nowrap">' +
									'<div class="ui-avatar-s"><img src="<%=user.face%>" height="45" width="45" /></div>' +
									'<div class="ui-list-info"><h4><%=user.realname%></h4><p><%=user.jobtitle%></p></div>' +
								'</div>' +
							'</li>' +
							'<%});%>';
		this._dp_tpl = '<%_.each(list, function(dp) {%>' +
							'<li class="_addrbook_li addrbook_li ui-border-t ui-checkbox addrbook_dp">' +
								'<input type="checkbox" class="addrbook_cdid" id="' + this.create_id('ab_cdid') + '<%=dp.cd_id%>" value="<%=dp.cd_id%>"<%if (-1 < _.indexOf(cdids, dp.cd_id.toString())) {%> checked<%}%> /><h4 class="ui-nowrap"><%=dp.cd_name%></h4>' +
							'</li>' +
							'<%});%>';
		this._so_group_tpl = '<li><ul id="<%=id%>" class="_addrbook_group addrbook_group ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section">' +
								'<li class="ui-border-tb ui-checkbox addrbook_dp ui-background-default">' +
									'<h4 class="ui-nowrap"><%=name%></h4>' +
								'</li>' +
								'<ul id="<%=list_id%>" class="ui-list ui-list-text ui-padding-bottom-0 ui-margin-bottom-0 ui-section"></ul>' +
							'</ul></li>';
		this._so_user_tpl = '<%_.each(list, function(user) {%>' +
							'<li class="_addrbook_li addrbook_li ui-border-t ui-checkbox">' +
								'<input type="checkbox" class="addrbook_uid" data-relation="ab_uid_<%=user.uid%>" id="' + this.create_id('ab_uid') + 'so_<%=user.uid%>" value="<%=user.uid%>" <%if (-1 < _.indexOf(uids, user.uid.toString())) {%> checked<%}%> />' +
								'<div class="ui-nowrap">' +
									'<div class="ui-avatar-s"><img src="<%=user.face%>" height="45" width="45" /></div>' +
									'<div class="ui-list-info"><h4><%=user.realname%></h4><p><%=user.jobtitle%></p></div>' +
								'</div>' +
							'</li>' +
							'<%});%>';
		this._select_tpl = '<a id="' + this.create_id('ab_select') + '<%=type%>_<%=id%>" data-type="<%=type%>" data-id="<%=id%>" class="addrbook_select"><span class="ui-border ui-avatar-u<%if ("dp" == type) {%> ui-text-color-info<%}%>"><%=name%></span></a>';
		this._avatar_tpl = '<%_.each(list, function(user) {%>' +
							'<div class="ui-badge-wrap" id="' + this.create_id('ab_uid_show') + '<%=user.uid%>" data-uid="<%=user.uid%>">' +
								'<div class="ui-badge-cornernum"></div>' +
								'<div class="ui-avatar-s"><span style="background-image:url(<%=user.face%>)"></span></div>' +
								'<div class="name"><%=user.realname%></div>' +
							'</div>' +
							'<%});%>';
		this._dpname_tpl = '<%_.each(list, function(dp) {%>' +
							'<div class="ui-badge-wrap ui-border ui-contact-part" id="' + this.create_id('ab_cdid_show') + '<%=dp.cd_id%>" data-cdid="<%=dp.cd_id%>">' +
								'<div class="ui-badge-cornernum"></div><span><%=dp.cd_name%></span>' +
							'</div>' +
							'<%});%>';
		// 源对象
		this._src = null;
		this._avatar_list = null;
		this._dpname_list = null;
		// 显示重画
		this._redraw = false;
		// 缓存信息
		this._users = {};
		// 部门信息列表
		this._departments = {};
		// 职位信息
		this._jobs = {};
		// cdid2uid
		this._cdid2uid = {};
		// 所有部门的标识
		this._sel_all = "-1";
	}

	// 调用继承方法
	extend(AddrBook, sl);

	$.extend(AddrBook.prototype, {
		create_id: function(id) {

			return id + "_" + this._unique_salt + "_";
		},
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

			// 如果 ajax 不存在
			if (!_.has(view, "ajax")) {
				view.ajax = {"url": this._def_user_url};
			}

			// 判断读取参数
			if (_.has(view, "ac")) {
				view.ajax.url = view.ajax.url + (-1 === view.ajax.url.indexOf("?") ? "?" : "&") + "ac=" + view.ac;
			}

			// 是否允许选择所有
			if (!_.has(view, "selectall") || false == view.selectall) {
				this._selectall = false;
			} else {
				this._selectall = true;
			}

			// 遍历 tabs, 检查数据的合法性
			this._tabs = _.extend(this._tabs, view.tabs);
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

				self._tabs[k] = tab;
			});

			// 完成选择时回调
			if (_.has(view, "cb") && _.isFunction(view.cb)) {
				this._cb = view.cb;
			}

			return this.init(view.ajax, view);
		},
		_scroll_event: function() {

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
		_init_cdids: function() {

			if (!_.has(this._tabs, this.TAG_DP)) {
				return true;
			}

			// 如果有接收
			var curtab = this._tabs[this.TAG_DP];
			if (_.has(curtab, "input")) {
				var ids = curtab["input"].val();
				this._cdids = 0 < ids.length ? ids.split(",") : [];
			}

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
				view = {"dist": $("#" + this._dist_id)};
			}

			// 如果容器 dom 不存在
			if (!this._init_dist(view.dist)) {
				return false;
			}

			var self = this;

			// 把模板放入容器
			var content_id = _.uniqueId(this._dom_dist.attr("id"));
			//var tab_head = _.uniqueId(content_id);
			var tab_btn = _.uniqueId(content_id);
			this._show_select_id = _.uniqueId(content_id);
			this._kw_id = _.uniqueId(content_id);
			this._dom_dist.html(_.template(this._tpl, {
				"dist": content_id,
				//"tab_head": tab_head,
				"tab_btn": tab_btn,
				"show_select_id": this._show_select_id,
				"kw_id": this._kw_id
			}));

			// 计算显示区高度
			this._content_dist = $("#" + content_id);

			// 监听取消/确定按钮事件
			this._dom_dist.find("button.ui-btn-primary").on("tap", function(e) {

				return self.submit(e);
			});

			this._dom_dist.find("button.clearfix").on("tap", function(e) {

				return self.close(e);
			});

			// 通讯录对象
			this._section_ab = $(this._dom_dist.parent());
			if (_.has(view, "section")) {
				this._section_ab = $(view["section"]);
			}

			// 当前显示对象
			this._section_cur = this._section_ab.prev("section");
			this._src = $(view["src"]);

			// 页面切换到通讯录
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
				self._init_cdids();

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

				if (true == this._redraw) {
					return true;
				}

				self._request({}, page, limit);
				return true || e;
			});

			// 初始化用户/部门操作
			this._init_user_show();
			this._init_dp_show();

			// 监听 ._addrbook_group 的 tap 事件
			this._content_dist.on("tap", "._addrbook_group", function(e) {

				var cdid = $(this).data("cdid");
				var ele_list = $(self.create_id("#ad_group_list") + cdid);
				// 如果已经是展开状态, 则隐藏
				var li = $(this).find("li");
				if (li.hasClass("ui-rotate-90")) {
					li.removeClass("ui-rotate-90");
					ele_list.hide();
					return true;
				}

				li.addClass("ui-rotate-90");
				// 如果不存在
				if ((0 <= cdid && !_.has(self._cdid2uid, cdid)) || 0 < ele_list.data("init")) {
					ele_list.show();
					return true;
				}

				// 列部门
				ele_list.data("init", 1);
				if (0 <= cdid) {
					// 获取数据并显示
					var users = [];
					_.each(self._cdid2uid[cdid], function(uid) {
						users.push(self._users[uid]);
					});

					ele_list.html(_.template(self._user_tpl, {"list": users, "uids": self._uids}));
				} else {
					//var list = {};
					//list = _.extend(list, self._departments);
					//list[0]["cd_name"] = "所有部门";
					ele_list.html(_.template(self._dp_tpl, {"list": self._departments, "cdids": self._dpids}));
				}

				return true || e;
			});

			// 监听 ._addrbook_li 的 tap 事件(将其代理到 this._content_dist 上)
			this._content_dist.on("tap", "._addrbook_li", function(e) {

				var ipt = $(this).find("input");
				// 如果标识不可用, 则
				/**if (self._sel_all != ipt.val() && 0 < self._dpids.length && self._sel_all == self._dpids[0]) {
					return true;
				}*/

				self._do_select(ipt);
				return true || e;
			});

			// 监听显示区
			$("#" + this._show_select_id).on("tap", ".addrbook_select", function(e) {

				var id = $(this).data("id");
				if (self.TAG_USER == $(this).data("type")) {
					$(self.create_id("#ab_uid") + id).prop("checked", false);
					self._uids = _.without(self._uids, id.toString());
				} else {
					$(self.create_id("#ab_cdid") + id).prop("checked", false);
					self._cdids = _.without(self._cdids, id.toString());
				}

				$(this).remove();
				return true || e;
			});

			// 监听 input 事件
			var ele_kw = $("#" + this._kw_id);
			ele_kw.on("input", function(e) {

				self._so($(this));
				return true || e;
			});

			ele_kw.on("propertychange", function(e) {

				self._so($(this));
				return true || e;
			});

			this._dom_dist.css("display", "none");
			return true;
		},
		// 选择全公司的操作
		_do_select_all: function(ipt, checked) {

			var ele_ss = $("#" + this._show_select_id);
			var data = {"id": this._sel_all, "name": ipt.parent().find("h4").text()};
			var self = this;
			ele_ss.html('');
			if (false == checked) { // 取消全选
				if (_.has(this._tabs, this.TAG_USER)) { // 如果有选择人
					// 恢复所有选择
					this._uids = _.without(this._uids, this._sel_all);
				}

				if (_.has(this._tabs, this.TAG_DP)) {
					this._cdids = _.without(this._cdids, this._sel_all);
				}
			} else { // 选择了全公司
				var init = false;
				data["type"] = this.TAG_USER;
				if (_.has(this._tabs, this.TAG_USER)) {
					this._uids.unshift(this._sel_all);
					ele_ss.append(_.template(this._select_tpl, data));
					init = true;
				}

				if (_.has(this._tabs, this.TAG_DP)) {
					this._cdids.unshift(this._sel_all);
					if (!init) {
						ele_ss.append(_.template(this._select_tpl, data));
					}
				}
			}

			_.each(this._uids, function(uid) {
				if (self._sel_all == uid.toString()) {
					return true;
				}

				var chk = $(self.create_id("#ab_uid") + uid);
				var chk_so = $(self.create_id("#ab_uid_so") + uid);
				if (0 < chk.size()) {
					chk.prop("checked", !checked);
				}

				if (0 < chk_so.size()) {
					chk_so.prop("checked", !checked);
				}

				// 如果未选中
				if (!checked) {
					var user = self._users[uid];
					ele_ss.append(_.template(self._select_tpl, {"id": user.uid, "type": self.TAG_USER, "name": user.realname}));
				}
			});

			_.each(this._cdids, function(cdid) {
				if (self._sel_all == cdid.toString()) {
					return true;
				}

				var chk = $(self.create_id("#ab_cdid") + cdid);
				var chk_so = $(self.create_id("#ab_cdid_so") + cdid);
				if (0 < chk.size()) {
					chk.prop("checked", !checked);
				}

				if (0 < chk_so.size()) {
					chk_so.prop("checked", !checked);
				}

				// 如果未选中
				if (!checked) {
					var dp = self._departments[cdid];
					ele_ss.append(_.template(self._select_tpl, {"type": self.TAG_DP, "id": dp.cd_id, "name": dp.cd_name}));
				}
			});

			ipt.prop("checked", checked);
			return true;
		},
		// 选择操作
		_do_select: function(ipt) {

			var id = ipt.val();
			var checked = ipt.prop("checked") ? false : true;
			// 如果选择的是全部
			if (0 >= id) {
				return this._do_select_all(ipt, checked);
			}

			// 如果 uids 数组里包含了 -1
			if (-1 < _.indexOf(this._uids, this._sel_all)) {
				return true;
			}

			// 如果 cdids 数组里包含了 -1
			if (-1 < _.indexOf(this._cdids, this._sel_all)) {
				return true;
			}

			ipt.prop("checked", checked);
			// 相关显示
			var relation = $("#" + ipt.data("relation"));
			if (0 < relation.size()) {
				relation.prop("checked", checked);
			}

			var curtab = null;
			// 选择/剔除操作
			if (ipt.hasClass("addrbook_uid")) {
				curtab = this._tabs[this.TAG_USER];
				$(this.create_id("#ab_uid") + id).prop("checked", checked);
				if (true == checked) { // 选中
					this._select_uid(id);
				} else { // 剔除
					$(this.create_id("#ab_select") + "user_" + id).remove();
					this._uids = _.without(this._uids, id);
				}
			} else {
				curtab = this._tabs[this.TAG_DP];
				$(this.create_id("#ab_cdid") + id).prop("checked", checked);
				if (true == checked) { // 选中
					this._select_cdid(id);
				} else { // 剔除
					$(this.create_id("#ab_select") + "dp_" + id).remove();
					this._cdids = _.without(this._cdids, id);
				}
			}

			// tab 选择时回调方法
			if (_.has(curtab, "cb") && _.isFunction(curtab.cb)) {
				curtab.cb(ipt.val(), checked);
			}

			return true;
		},
		// 搜索
		_so: function(ele) {

			var ele_so = $(this.create_id("#ad_group_so"));
			// 整理搜索词
			var val = ele.val();
			var re = /[^\W\S]/g;
			val = val.replace(re, '');
			val = $.trim(val);
			// 如果搜索词未改, 则忽略
			if (val == this._last_val) {
				return true;
			}

			this._last_val = val;
			// 如果搜索条件为空, 则去除结果列表
			if ("" == val) {
				ele_so.remove();
				return true;
			}

			// 搜索
			var result = [];
			var count = 0;
			re = new RegExp("~" + val, "i");
			for (var i in this._users) {
				var usr = this._users[i];
				if (re.test(usr["_re"])) {
					result.push(usr);

					if (10 <= count ++) {
						break;
					}
				}
			}

			// 获取搜索结果列表对象
			if (0 >= ele_so.size()) {
				this._content_dist.prepend(_.template(this._so_group_tpl, {"id": this.create_id('ad_group_so'), "list_id": this.create_id("ad_group_so_list"), "name": '搜索结果'}));
			}

			// 显示用户详情
			$(this.create_id("#ad_group_so_list")).html(_.template(this._so_user_tpl, {"list": result, "uids": this._uids}));
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

			// 如果部门为空
			var self = this;
			if (_.isEmpty(this._departments) && _.has(data.result, "departments")) {
				self._departments = {};
				// 遍历部门, 剔除顶级部门
				_.each(data["result"]["departments"], function(dp) {
					if (0 == dp["cd_qywxparentid"]) {
						self._top_cd_id = dp.cd_id;
						return true;
					}

					self._departments[dp.cd_id] = dp;
					// 初始化选择显示
					if (-1 < _.indexOf(self._dpids, dp.cd_id)) {
						$("#" + self._show_select_id).append(_.template(self._select_tpl, {"type": self.TAG_DP, "id": dp.cd_id, "name": dp.cd_name}));
					}
				});
			}

			// 如果职位为空
			if (_.isEmpty(this._jobs) && _.has(data.result, "jobs")) {
				this._jobs = data["result"]["jobs"];
			}

			// 重新整理数据并保存
			this._list = this._resort(data["result"][this._data_key]);
			// 信息写入缓存
			this._dom_dist.data(this._data_curtag + "-" + this._page, data["result"]);
			return true
		},
		/**
		 * 对数据进行排序
		 * @param {object} data 待排序的数据
		 * @returns {*}
		 * @private
		 */
		_resort: function(data) {

			return this._resort_users(data);
		},
		_resort_users: function(data) {

			// 取数据列表
			var self = this;
			// 按首字母分组
			_.each(data, function(usr) {
				// 如果 list 中不存在当前用户的前缀
				if (!_.has(self._cdid2uid, usr["departmentid"])) {
					self._cdid2uid[usr["departmentid"]] = [];
				}

				self._cdid2uid[usr["departmentid"]].push(usr["uid"]);
				usr["_re"] = "~" + usr["alphaindex"] + "~" + usr["mobilephone"] + "~" + usr["realname"];
				self._users[usr["uid"]] = usr;

				// 初始化选择显示
				if (-1 < _.indexOf(self._uids, usr.uid)) {
					$("#" + self._show_select_id).append(_.template(self._select_tpl, {"id": usr.uid, "type": self.TAG_USER, "name": usr.realname}));
				}
			});

			return data;
		},
		/**
		 * ajax 请求
		 * @param {object} ajax ajax请求
		 * @param {int} page
		 * @param {int} limit
		 */
		_request: function(ajax, page, limit) {

			if (true == this._redraw) {
				return true;
			}

			return this.super(ajax, page, limit);
		},
		/**
		 * 显示
		 * @param {object} data 待显示数据
		 * @returns {boolean}
		 * @private
		 */
		_render: function(data) {

			// 如果容器对象为空
			if (null == this._content_dist) {
				return true;
			}

			// 解析模板
			if (_.has(this._tabs, this.TAG_DP) && !_.has(this._tabs, this.TAG_USER)) { // 只需要部门
				this._content_dist.append(_.template(this._only_dp_tpl, {"list": this._departments, "cdids": this._cdids, "selectall": this._selectall}));
			} else {
				var departments = [];
				// 如果还需要选择部门
				if (_.has(this._tabs, this.TAG_DP) && _.has(this._tabs, this.TAG_USER)) {
					departments.push({"cd_id": -1, "cd_name": "部门列表"});
				}

				// 顶级部门
				if (_.has(this._cdid2uid, this._top_cd_id)) {
					departments.push({"cd_id": this._top_cd_id, "cd_name": "#"});
				}

				for (var i in this._departments) {
					if (_.has(this._cdid2uid, this._departments[i]["cd_id"])) {
						departments.push(this._departments[i]);
					}
				}

				this._content_dist.append(_.template(this._group_tpl, {"list": departments, "selectall": this._selectall}));
			}

			return true || data;
		},
		// 取消/关闭
		close: function(e) {

			this._close_section();
			// 恢复被修改的数据
			this._init_cdids();
			this._init_uids();
			// 修改选中状态
			var self = this;
			this._content_dist.find("input").each(function(index, ipt) {

				var ele_ipt = $(ipt);
				var id = ele_ipt.val();
				// 如果当前 uid 在队列中
				if (ele_ipt.hasClass("addrbook_uid")) {
					self._chk_sel_ipt(self._uids, id, ele_ipt);
				} else {
					self._chk_sel_ipt(self._cdids, id, ele_ipt);
				}

				return true || index;
			});

			// 如果有回调方法
			if (_.isFunction(this._cb)) {
				this._cb(this._dom_dist);
			}

			return true || e;
		},
		/**
		 *
		 * @param {object} ids ID数组
		 * @param {string} id 当前ID值
		 * @param {zepto} ipt zepto 对象
		 * @returns {boolean}
		 * @private
		 */
		_chk_sel_ipt: function(ids, id, ipt) {

			if(-1 < _.indexOf(ids, id)) {
				// 如果当前对象已选中
				if (true == ipt.prop("checked")) {
					return true;
				}

				ipt.prop("checked", false);
				this._do_select(ipt);
			} else {
				// 如果当前对象未选中
				if (false == ipt.prop("checked")) {
					return true;
				}

				ipt.prop("checked", true);
				this._do_select(ipt);
			}

			return true;
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
				this._tabs[this.TAG_DP]["input"].val(this._cdids.join(","));
				this._update_department();
			}

			// 如果有回调方法
			if (_.isFunction(this._cb)) {
				this._cb(this._dom_dist);
			}

			return true || e;
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
		_update_department: function() {

			// 如果部门县市区为空
			if (0 >= this._dpname_list.size()) {
				return true;
			}

			var self = this;
			var list = [];
			var cdids = this._cdids;
			// 是否选择了全部的标识
			var sel_all = this._sel_all == this._cdids[0];
			this._dpname_list.find(".ui-badge-wrap").each(function(index, dpname) {
				var atr = $(dpname);
				var cdid = atr.data("cdid").toString();
				if (!sel_all && -1 < _.indexOf(cdids, cdid)) {
					cdids = _.without(cdids, cdid);
					return true;
				}

				atr.remove();
			});

			if (sel_all) { // 如果是选择了全部
				list.push({"cd_id": this._sel_all, "cd_name": "全部人员/部门"});
			} else {
				_.each(cdids, function (cdid) {
					// 如果 cdid 不正确
					cdid = parseInt(cdid);
					if (_.isNaN(cdid) || 0 == cdid || !_.has(self._departments, cdid)) {
						return true;
					}

					list.push(self._departments[cdid]);
				});
			}

			// 读取全部用户元素
			var ele_alluser = $(this.create_id("#ab_uid_show") + "-1");
			// 如果新增用户为空
			if (0 == cdids.length || 0 < ele_alluser.size()) {
				return true;
			}

			this._dpname_list.append(_.template(this._dpname_tpl, {"list": list}));
		},
		// 更新头像
		_update_avatar: function() {

			// 如果头像显示区为空
			if (0 >= this._avatar_list.size()) {
				return true;
			}

			var self = this;
			var list = [];
			var uids = this._uids;
			// 是否选择了全部的标识
			var sel_all = this._sel_all == this._uids[0];
			// 遍历已存在的头像
			this._avatar_list.find(".ui-badge-wrap").each(function(index, avatar) {
				var atr = $(avatar);
				var uid = atr.data("uid").toString();
				if (!sel_all && -1 < _.indexOf(uids, uid)) {
					uids = _.without(uids, uid);
					return true;
				}

				atr.remove();
			});

			if (sel_all) { // 如果是选择了全部
				list.push({"uid": this._sel_all, "face": "-1.png", "realname": "全部人员/部门"});
			} else {
				_.each(uids, function(uid) {
					// 如果 uid 不正确
					uid = parseInt(uid);
					if (_.isNaN(uid) || 0 >= uid || !_.has(self._users, uid)) {
						return true;
					}

					list.push(self._users[uid]);
				});
			}

			// 读取全部部门元素
			var ele_alldp = $(this.create_id("#ab_cdid_show") + "-1");
			// 如果新增用户为空
			if (0 == uids.length || 0 < ele_alldp.size()) {
				return true;
			}

			this._avatar_list.append(_.template(this._avatar_tpl, {"list": list}));
		},
		_select_cdid: function(cdid) {

			var curtab = this._tabs[this.TAG_DP];
			var ele_dp;
			var department = this._departments[cdid];
			var ele_select = $("#" + this._show_select_id);
			// 如果当前操作的是公司
			if (this._sel_all == cdid.toString()) {
				ele_dp = $(this.create_id("#ab_cdid") + cdid);
				// 如果公司被选中
				if (0 < ele_dp.size() && ele_dp.prop("checked")) {
					this._content_dist.find(".ui-checkbox").each(function(index, li) {
						var ipt = $(li).find("input");
						if (cdid == ipt.val()) {
							return true;
						}

						ipt.prop("checked", false);
					});

					ele_select.append(_.template(this._select_tpl, {"type": this.TAG_DP, "id": department.cd_id, "name": department.cd_name}));
					this._cdids = [cdid];
				}

				return true;
			}

			// 如果只能选择一个
			if (1 == curtab["max"]) {
				// 清除部门选中状态
				var prev_cdid = this._cdids.pop();
				ele_dp = $(this.create_id("#ab_cdid") + prev_cdid);
				$(this.create_id("#ab_select") + "dp_" + prev_cdid).remove();
				if (0 < ele_dp.size()) {
					ele_dp.prop("checked", false);
				}

				ele_select.append(_.template(this._select_tpl, {"type": this.TAG_DP, "id": department.cd_id, "name": department.cd_name}));
				this._cdids = [cdid];
				return true;
			}

			// 如果 max 小于 1, 则说明不限
			if (1 > curtab["max"]) {
				this._cdids.push(cdid);
				ele_select.append(_.template(this._select_tpl, {"type": this.TAG_DP, "id": department.cd_id, "name": department.cd_name}));
				return true;
			}

			// 判断是否达到最大值
			if (curtab["max"] <= this._cdids.length) {
				// 清除选中状态
				ele_dp = $(this.create_id("#ab_cdid") + cdid);
				if (0 < ele_dp.size()) {
					ele_dp.prop("checked", false);
				}

				$.tips({
					"content": "最多只能选择 " + curtab["max"] + " 个部门",
					"type": "success",
					"stayTime": 1000
				});
				return true;
			}

			ele_select.append(_.template(this._select_tpl, {"type": this.TAG_DP, "id": department.cd_id, "name": department.cd_name}));
			this._cdids.push(cdid);
			return true;
		},
		_select_uid: function(uid) {

			var curtab = this._tabs[this.TAG_USER];
			var ele_user;
			var user = this._users[uid];
			var ele_select = $("#" + this._show_select_id);
			// 如果只能选择一个
			if (1 == curtab["max"]) {
				// 清除选中状态
				var prev_uid = this._uids.pop();
				ele_user = $(this.create_id("#ab_uid") + prev_uid);
				if (0 < ele_user.size()) {
					ele_user.prop("checked", false);
				}

				$(this.create_id("#ab_select") + "user_" + prev_uid).remove();
				ele_select.append(_.template(this._select_tpl, {"id": user.uid, "type": this.TAG_USER, "name": user.realname}));
				this._uids = [uid];
				return true;
			}

			// 如果 max 小于 1, 则说明不限
			if (1 > curtab["max"]) {
				this._uids.push(uid);
				ele_select.append(_.template(this._select_tpl, {"id": user.uid, "type": this.TAG_USER, "name": user.realname}));
				return true;
			}

			// 判断是否达到最大值
			if (curtab["max"] <= this._uids.length) {
				// 清除选中状态
				ele_user = $(this.create_id("#ab_uid") + uid);
				if (0 < ele_user.size()) {
					ele_user.prop("checked", false);
				}

				$.tips({
					"content": "最多只能选择 " + curtab["max"] + " 人",
					"type": "success",
					"stayTime": 1000
				});
				return true;
			}

			ele_select.append(_.template(this._select_tpl, {"id": user.uid, "type": this.TAG_USER, "name": user.realname}));
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
		cdids: function(cdids) {

			if (_.isUndefined(cdids)) {
				return this._cdids;
			}

			this._cdids = cdids;
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
				this._cdids = 0 < ids.length ? ids.split(",") : [];
			}

			// 部门删除操作
			var self = this;
			this._dpname_list.on("tap", ".ui-badge-cornernum", function(e) {

				var cdid = $(this).parent().data("cdid");
				self._del_department(cdid, true);
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
				self._del_user(uid, true);
				return true || e;
			});

			return true;
		},
		_del_department: function(cdid, chk) {

			this._cdids = _.without(this._cdids, cdid.toString());
			this._tabs[this.TAG_DP]["input"].val(this._cdids.join(","));
			// 清除选中状态
			var dp = $(this.create_id("#ab_cdid") + cdid);
			if (0 < dp.size()) {
				if (this._sel_all == cdid) {
					this._do_select_all(dp, false);
				} else {
					dp.prop("checked", false);
				}
			}

			// 删除显示
			$(this.create_id("#ab_select") + "dp_" + cdid).remove();
			// 更新部门信息
			this._update_department();

			// 如果删除的是全公司, 则
			if (-1 == cdid && chk) {
				this._del_user(cdid, false);
			}
		},
		_del_user: function(uid, chk) {

			this._uids = _.without(this._uids, uid.toString());
			this._tabs[this.TAG_USER]["input"].val(this._uids.join(","));
			// 清除选中状态
			var user = $(this.create_id("#ab_uid") + uid);
			if (0 < user.size()) {
				user.prop("checked", false);
			}

			$(this.create_id("#ab_select") + "user_" + uid).remove();
			// 更新头像
			this._update_avatar();

			// 如果删除的是全公司, 则
			if (-1 == uid && chk) {
				this._del_department(uid, false);
			}
		}
	});

	return AddrBook;
});

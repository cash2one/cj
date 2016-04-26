; (function() {
	var MenuList = [];
	var _s = null;
	var Menu = {
		data: [{
			type: "view",
			url: "",
			name: "",
			sub_button: []
		},
		{
			type: "view",
			url: "",
			name: "",
			sub_button: []
		},
		{
			type: "view",
			url: "",
			name: "",
			sub_button: []
		}],
		validate: function() {
			var lists = this.data,
			url_reg = /http(s?):\/\/([\w|\.]+)/,
			tips = "";
			$.each(lists, function(index, item) {
				var temp = item["sub_button"],
				arr = ["一", "二", "三", "四"],
				str = arr[index] || index + 1;
				if (!item["name"]) {
					tips = "菜单不能为空，请确认";
					return;
				} else if (item["sub_button"].length == 1) {
					tips = "第" + str + "个菜单项子菜单数目为1，至少需要2个";
					return;
				} else if (item["type"] == "view" && !item["url"] && item["name"] != "维权" && item["sub_button"].length <= 0) {
					tips = "第" + str + "个菜单的URL不能为空";
					return
				} else if (item["url"] && !url_reg.test(item["url"])) {
					tips = "第" + str + "个菜单的URL不正确";
					return
				}
				$.each(temp, function(i, t) {
					if (!t["name"]) {
						tips = "子菜单名称不能为空，请确认";
						return;
					}
					if (t["type"] == "view" && !t['url'] && t["name"] != "维权" && t["sub_button"].length <= 0) {
						tips = "子菜单【" + t["name"] + "】没有选择页面，请确认"
						return;
					}
					if (t["url"] && !url_reg.test(t["url"])) {
						//tips = "第" + str + "个菜单子菜单URL错误";
						//return;
					}
				});
			});
			if ( !! tips) {
				WG.popup({
					content: tips
				});
				return false;
			} else {
				return true;
			}
		},
		addSubMenu: function(p_index) {
			var arr = this.data,
			temp = arr[p_index]["sub_button"],
			setting = {
				type: "view",
				name: "",
				url: "",
				sub_button: []
			};
			temp[temp.length] = setting;
		},
		setKey: function(index, key, value) {
			var arr = this.data,
			temp = arr[index];
			temp[key] = value;
		},
		getKey: function(index, key) {
			var arr = this.data,
			temp = arr[index];
			return temp[key] || "";
		},
		setSubKey: function(p_index, s_index, key, value) {
			var arr = this.data,
			p_temp = arr[p_index],
			temp = p_temp["sub_button"][s_index];
			temp[key] = value;
		},
		delSubKey: function(p_index, s_index) {
			var arr = this.data,
			p_temp = arr[p_index],
			temp = p_temp["sub_button"];
			temp.splice(s_index, 1);
		},
		getSubKey: function(p_index, s_index, key) {
			var arr = this.data,
			p_temp = arr[p_index],
			temp = p_temp["sub_button"][s_index];
			return temp[key] || "";
		},
		initData: function(cbk) {
			that	=	this;
			var	url	=	typeof(historyUrl) != 'undefined' && historyUrl ? historyUrl : smActionUrlBase+'get';
			$.getJSON(url,function(o){
				if (o.ret == 0) {
					if (o.data && o.data.menu && o.data.menu.button) {
						var temp = o.data.menu.button;
						$.each(that.data, function(index, item) {
							if (temp[index]) {
								$.extend(item, temp[index]);
							}
						});
					} else {}
					if (typeof cbk == 'function') {
						cbk();
					}
				} else {
					alert(o);
				}
			});
		},
		renderMenuName: function() {
			/** 写入手机标题栏 */
			//$(".appname").html(o.data.appname);
			return ;
		},
		renderOprMenu: function(index, item) {
			var name = item["name"] || "",
			sub_button = item["sub_button"],
			url = item["url"],
			opr_str = "";
			opr_str = txTpl("tem_menu", {
				index: index + 1,
				name: name,
				subBtns: sub_button
			});
			$(".opr_menu").append(opr_str);
		},
		renderShowMenu: function(index, item) {
			var name = item["name"] || "",
			sub_button = item["sub_button"],
			show_str = "";
			show_str = txTpl("menu_show", {
				name: name,
				subBtns: sub_button
			});
			$(".menu_show").append(show_str);
		},
		modShowMenu: function(index) {
			var item = this.data[index],
			name = item["name"] || "",
			sub_button = item["sub_button"],
			show_str = "";
			show_str = txTpl("menu_show", {
				name: name,
				subBtns: sub_button
			});
			$(".menu_show .menu_item:eq(" + index + ")").replaceWith(show_str);
		},
		renderAllData: function() {
			var arr = this.data;
			$.each(arr,
			function(index, item) {
				Menu.renderShowMenu(index, item);
				Menu.renderOprMenu(index, item);
			});
		},
		submitData: function() {
			if (!this.validate()) {
				return;
			}
			var url	=	smActionUrlBase+'post';
			//提交保存
			$.post(url,{"button":this.data},function(o){
				if ( typeof(o.errcode) == 'underined') {
					alert('未知错误');
				} else {
					if ( o.errcode == 0 ) {
						alert('自定义菜单发布成功');
						window.location.href=pageUrl;
					} else {
						alert(o.errmsg);
					}
				}
			},'json');
			return ;
		}
	};
	var Logic = {
		start: function() {
			Menu.renderMenuName();
			Menu.initData(function() {
				Menu.renderAllData();
				Logic.bindEvents();
			});
		},
		errCode: function(o) {
			if (o.ret == '-4749') {
				WG.openLogin();
				return;
			} else if (o.ret == '2002') {
				WG.popup({
					content: "您没有操作权限"
				});
				return;
			} else if (o.sub == '-422') {
				WG.popup({
					content: "有菜单未配置URL，请检查"
				});
				return;
			} else {
				WG.popup({
					content: "操作菜单失败"
				});
				return;
			}
		},
		bindEvents: function() {
			$(".opr_menu").bind("click",
			function(e) {
				var target = $(e.target);
				if (target.hasClass("edit_menuname")) {
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					menu_name = Menu.getKey(pIndex - 1, "name"),
					url = Menu.getKey(pIndex - 1, "url"),
					menu_key = Menu.getKey(pIndex - 1, "key"),
					url_img = "",
					tpl = "";
					if (!url) {
						url_img = "";
					} else {
						if (url.indexOf("?") > 0) {
							url = url.substring(0, url.indexOf("?"));
						}
						$.each(MenuList,
						function(index, item) {
							if (item["url"].indexOf(url) >= 0) {
								url = item["url"];
								url_img = item["img"];
							}
						});
					}
					tpl = txTpl("edit_menu", {
						pIndex: pIndex,
						menu_name: menu_name,
						url: url,
						url_img: url_img,
						menu_key: menu_key
					});
					elem.replaceWith(tpl);
				}
				if (target.hasClass("commit_menuname")) {
					var elem = target.parent().parent(),
					nextChild = $(elem).next(),
					pIndex = target.attr("data-pIndex"),
					val = WG.filterXSS($.trim($(".menuname", elem).val())),
					url = "",
					key = "",
					current = $(".mod-tab__item_current", nextChild),
					tpl = '<div class="mod-custom-menu__op">\
         <a class="button button_primary edit_menuname" data-pIndex="' + pIndex + '" href="javascript:;">编辑</a>\
        </div>\
        <span class="mod-custom-menu__number menunum">' + pIndex + '</span>\
        <span class="mod-custom-menu__text menuname">' + val + '</span>';
					if (current.hasClass("tab_selectpage")) {
						url = $(".selectPage", nextChild).attr("data-url") || "";
					} else if (current.hasClass("tab_configlink")) {
						url = WG.filterXSS($.trim($(".configLink", nextChild).val()));
					} else if (current.hasClass("tab_clickmsg")) {
						key = $(".show_clickmsg", nextChild).attr("data-sTemplateID");
					}
					var subBtns = Menu.data[pIndex - 1]["sub_button"];
					if ((url || key) && subBtns.length > 0) {
						WG.popup({
							content: "已有子菜单，无法设置主菜单URL或Click"
						});
					} else {
						if (url) {
							//url = url + (url.indexOf('?') == -1 ? '?': '&') + "appid=bbb";
							Menu.setKey(pIndex - 1, "type", "view");
							Menu.setKey(pIndex - 1, "key", "");
							Menu.setKey(pIndex - 1, "url", url);
						} else if (key) {
							Menu.setKey(pIndex - 1, "type", "click");
							Menu.setKey(pIndex - 1, "url", "");
							Menu.setKey(pIndex - 1, "key", key);
						}
						Menu.setKey(pIndex - 1, "name", val);
						elem.html(tpl);
						nextChild.remove();
						Menu.modShowMenu(pIndex - 1);
					}
				}
				if (target.hasClass("cancel_menuname")) {
					var elem = target.parent().parent(),
					nextChild = $(elem).next(),
					pIndex = target.attr("data-pIndex"),
					menu_name = Menu.getKey(pIndex - 1, "name"),
					tpl = '<div class="mod-custom-menu__op">\
         <a class="button button_primary edit_menuname" data-pIndex="' + pIndex + '" href="javascript:;">编辑</a>\
        </div>\
        <span class="mod-custom-menu__number menunum">' + pIndex + '</span>\
        <span class="mod-custom-menu__text menuname">' + menu_name + '</span>';
					elem.html(tpl);
					nextChild.remove();
				}
				if (target.hasClass("edit_submenu")) {
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					sIndex = target.attr("data-sIndex"),
					submenu_name = Menu.getSubKey(pIndex - 1, sIndex - 1, "name"),
					submenu_url = Menu.getSubKey(pIndex - 1, sIndex - 1, "url"),
					submenu_key = Menu.getSubKey(pIndex - 1, sIndex - 1, "key"),
					submenu_img = "",
					str = "";
					if (!submenu_url) {
						submenu_img = "";
					} else {
						//if (submenu_url.indexOf("?") > 0) {
							//submenu_url = submenu_url.substring(0, submenu_url.indexOf("?"));
						//}
						$.each(MenuList,
						function(index, item) {
							if (item["url"].indexOf(submenu_url) >= 0) {
								submenu_url = item["url"];
								submenu_img = item["img"];
							}
						});
					}
					str = txTpl("edit_submenu", {
						pIndex: pIndex,
						sIndex: sIndex,
						submenu_num: pIndex + "." + sIndex,
						submenu_name: submenu_name,
						submenu_url: submenu_url,
						submenu_img: submenu_img,
						submenu_key: submenu_key
					});
					elem.html(str);
				}
				if (target.hasClass("del_submenu")) {
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					sIndex = target.attr("data-sIndex"),
					menuname = Menu.getKey(pIndex - 1, "name"),
					sub_button = [],
					str = "";
					Menu.delSubKey(pIndex - 1, sIndex - 1),
					Menu.modShowMenu(pIndex - 1);
					sub_button = Menu.data[pIndex - 1]["sub_button"];
					str = txTpl("tem_menu", {
						index: pIndex,
						name: menuname,
						subBtns: sub_button
					});
					elem.parent().replaceWith(str);
				}
				if (target.hasClass("cancel_submenu")) {
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					sIndex = target.attr("data-sIndex"),
					submenu_name = Menu.getSubKey(pIndex - 1, sIndex - 1, "name"),
					str = '<div class="mod-custom-menu__submenu-op">\
         <a href="javascript:;" class="edit_submenu" data-pIndex="' + pIndex + '" data-sIndex="' + sIndex + '">编辑</a>\
         <a href="javascript:;" class="del_submenu" data-pIndex="' + pIndex + '" data-sIndex="' + sIndex + '">删除</a>\
        </div>\
        <span class="mod-custom-menu__submenu-number submenu_num">' + pIndex + "." + sIndex + '</span>\
        <span class="mod-custom-menu__submenu-text submenu_name">' + submenu_name + '</span>';
					elem.html(str);
				}
				if (target.hasClass("commit_submenu")) {
					//alert(WG.mapToStr(target.parent().parent()[0]));
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					sIndex = target.attr("data-sIndex"),
					submenu_name = WG.filterXSS($.trim($(".submenu_name", elem).val())),
					current = $(".mod-tab__item_current", elem),
					submenu_url = "",
					key = "",
					str = '<div class="mod-custom-menu__submenu-op">\
         <a href="javascript:;" class="edit_submenu" data-pIndex="' + pIndex + '" data-sIndex="' + sIndex + '">编辑</a>\
         <a href="javascript:;" class="del_submenu" data-pIndex="' + pIndex + '" data-sIndex="' + sIndex + '">删除</a>\
        </div>\
        <span class="mod-custom-menu__submenu-number submenu_num">' + pIndex + "." + sIndex + '</span>\
        <span class="mod-custom-menu__submenu-text submenu_name">' + submenu_name + '</span>';
					if (current.hasClass("tab_selectpage")) {
						submenu_url = $(".selectPage", elem).attr("data-url") || "";
					} else if (current.hasClass("tab_configlink")) {
						submenu_url = WG.filterXSS($.trim($(".configLink", elem).val()));
					} else if (current.hasClass("tab_clickmsg")) {
						key = $(".show_clickmsg", elem).attr("data-sTemplateID");
					}
					if (!submenu_name) {
						WG.popup({
							content: "子菜单名称不能为空"
						});
						return;
					}
					if (submenu_url) {
						//submenu_url = submenu_url + (submenu_url.indexOf('?') == -1 ? '?': '&') + "appid=aaaa"
						Menu.setSubKey(pIndex - 1, sIndex - 1, "type", "view");
						Menu.setSubKey(pIndex - 1, sIndex - 1, "key", "");
						Menu.setSubKey(pIndex - 1, sIndex - 1, "url", submenu_url);
					} else if (key) {
						Menu.setSubKey(pIndex - 1, sIndex - 1, "type", "click");
						Menu.setSubKey(pIndex - 1, sIndex - 1, "url", "");
						Menu.setSubKey(pIndex - 1, sIndex - 1, "key", key);
					}
					Menu.setSubKey(pIndex - 1, sIndex - 1, "name", submenu_name);
					elem.html(str);
					Menu.modShowMenu(pIndex - 1);
				}
				if (target.hasClass("add_submenu")) {
					var elem = target.parent().parent(),
					pIndex = target.attr("data-pIndex"),
					subBtns = Menu.data[pIndex - 1]["sub_button"],
					sIndex = subBtns.length + 1,
					str = "";
					function temp() {
						str = txTpl("edit_submenu", {
							pIndex: pIndex,
							sIndex: sIndex,
							submenu_num: pIndex + "." + sIndex,
							submenu_name: "",
							submenu_url: "",
							submenu_img: "",
							submenu_key: ""
						});
						str = '<div class="mod-custom-menu__submenu">' + str + '</div>';
						Menu.addSubMenu(pIndex - 1);
						$($.parseHTML(str)).insertBefore(target.parent());
						Menu.modShowMenu(pIndex - 1);
						if (sIndex >= 5) {
							$(target.parent()).hide();
						}
					}
					if (Menu.getKey(pIndex - 1, "url") || Menu.getKey(pIndex - 1, "key")) {
						WG.popup({
							content: "主菜单已配置，点击继续添加会删除该主菜单对应配置，然后添加子菜单",
							confirm_txt: "继续添加",
							cancel_txt: "取消",
							confirm_cbk: function() {
								Menu.setKey(pIndex - 1, "url", "");
								Menu.setKey(pIndex - 1, "key", "");
								temp();
							}
						});
						return;
					}
					temp();
				}
				if (target.hasClass("tab_clickmsg")) {
					var elem = target.parent().parent().parent().parent(),
					pIndex = target.attr("data-pIndex");
					$("a", target.parent()).removeClass("mod-tab__item_current");
					$(target).addClass("mod-tab__item_current");
					$(".show_configlink, .show_selectpage", elem).hide();
					$(".show_clickmsg", elem).show();
				}
				if (target.hasClass("tab_selectpage")) {
					var elem = target.parent().parent().parent().parent(),
					pIndex = target.attr("data-pIndex");
					$("a", target.parent()).removeClass("mod-tab__item_current");
					$(target).addClass("mod-tab__item_current");
					$(".show_configlink, .show_clickmsg", elem).hide();
					$(".show_selectpage", elem).show();
				}
				if (target.hasClass("tab_configlink")) {
					var elem = target.parent().parent().parent().parent(),
					pIndex = target.attr("data-pIndex");
					$("a", target.parent()).removeClass("mod-tab__item_current");
					$(target).addClass("mod-tab__item_current");
					$(".show_selectpage, .show_clickmsg", elem).hide();
					$(".show_configlink", elem).show();
				}
				if (target.hasClass("open_selectMsg")) {
					var elem = target.parent().parent().parent(),
					old = $(".show_clickmsg", elem).attr("data-sTemplateID") || "";
					_s = !_s ? new Wkd.selector() : _s;
					_s.open();
					_s.returnSelect = function(retVal) {
						var sTemplateID = retVal.sTemplateID;
						if (sTemplateID) {
							$(".show_clickmsg", elem).attr("data-sTemplateID", sTemplateID);
							$(".show_clickmsg div", elem).html('<div>您已配置消息素材</div><br/><a class="button button_primary open_selectMsg" href="javascript:;">修改消息素材</a>');
						} else {
							WG.popup({
								content: "获取下发消息ID失败，请重新选择"
							});
						}
					}
				}
				if (target.hasClass("open_selectpage")) {
					var str = "";
					str = txTpl("tem_page", {
						lists: MenuList
					});
					$(top.window.document.body).append(str);
					function fitPopup() {
						var wWidth = $(top.window).width(),
						wHeight = $(top.window).height(),
						elemWidth = $(".tem_page_main", top.document.body).width(),
						elemHeight = $(".tem_page_main", top.document.body).height();
						var _left = (parseInt(wWidth, 10) - parseInt(elemWidth, 10)) / 2;
						var _top = (parseInt(wHeight, 10) - parseInt(elemHeight, 10)) / 2;
						$(".tem_page_main", top.document.body).css('top', _top + 'px');
						$(".tem_page_main", top.document.body).css('left', _left + 'px');
					}
					fitPopup();
					function choice(e) {
						var target = e.target,
						url = $(target).attr("data-url") || "";
						$(".tem_page_main .img_list", top.document.body).removeClass("current");
						$(".tem_page_main .mod-checkitem", top.document.body).css("display", "none");
						$(target.parentNode).addClass("current");
						$(".mod-checkitem", target.parentNode).css("display", "block");
					}
					$(".tem_page_main .img_list", top.document.body).unbind("click", choice);
					$(".tem_page_main .img_list", top.document.body).bind("click", choice);
					$(".tem_page_confirm", top.document.body).click(function() {
						var current = $(".tem_page .img_list.current", top.document.body),
						img = $("img", current).attr("src") || "",
						url = current.attr("data-url") || "";
						$(".selectPage", document.body).attr("data-url", url);
						$(".selectPage", document.body).attr("src", img);
						$(".tem_page", top.document.body).remove();
					});
					$(".tem_page_cancel", top.document.body).click(function() {
						$(".tem_page", top.document.body).remove();
					});
				}
			});
			$(".commit_all").bind("click",
			function(e) {
				Menu.submitData();
			})
		}
	};
	Logic.start();
})()
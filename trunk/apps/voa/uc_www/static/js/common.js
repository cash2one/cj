function serialize(objs) {
	var parmString = $(objs).serialize();
	var parmArray = parmString.split("&");
	var parmStringNew="";
	$.each(parmArray,function(index,data){
		var li_pos = data.indexOf("=");
		if(li_pos >0){
			var name = data.substring(0,li_pos);
			var value = escape(decodeURIComponent(data.substr(li_pos+1)));
			var parm = name+"="+value;
			parmStringNew = parmStringNew=="" ? parm : parmStringNew + '&' + parm;
		}
	});
	return parmStringNew;
}

function delayURL(url, time) {
	setTimeout("window.location.href='" + url + "'", time*1000);
}

/**
 * 通过某个复选框触发全选/取消某组复选框
 * @param t 触发动作按钮的复选框对象this
 * @param itemName 组复选框的name名
 * @example checkAll(this,'delete')
 */
function checkAll(t,itemName){
	jQuery(t).prop("checked",function(i,v){
		jQuery("input[name^='"+itemName+"']").prop("checked",v);
	});
}

/**
 * 封装了的WG方法，提供多种工具
 */
(function(win) {
	var WG = WG || {};
	WG.RunPrefixMethod	=	function(obj, method) {
		var	pfx	=	["webkit", "moz", "ms", "o", ""];
		var p = 0, m, t;
		while (p < pfx.length && !obj[m]) {
			m = method;
			if (pfx[p] == "") {
				m = m.substr(0,1).toLowerCase() + m.substr(1);
			}
			m = pfx[p] + m;
			t = typeof obj[m];
			if (t != "undefined") {
				pfx = [pfx[p]];
				return (t == "function" ? obj[m]() : obj[m]);
			}
			p++;
		}
	}
	WG.format = function(tpl, obj) {
		return tpl.replace(/\{([\d\w]+)\}/g,
		function(m, n) {
			return obj[n] !== undefined ? obj[n].toString() : m;
		});
	};
	WG.strToMap = function(str, spliter1, spliter2) {
		spliter1 = spliter1 || '&';
		spliter2 = spliter2 || '=';
		var type = str.split(spliter1);
		var typeMap = {};
		for (var p in type) {
			var _i = type[p].split(spliter2);
			if (2 == _i.length) {
				typeMap[_i[0]] = _i[1];
			}
		}
		return typeMap;
	};
	WG.mapToStr = function(map, spliter1, spliter2) {
		try {
			spliter1 = spliter1 || '&';
			spliter2 = spliter2 || '=';
			if (WG.is(map, 'object')) {
				var _arr = [];
				for (var p in map) {
					_arr.push(p + spliter2 + map[p]);
				}
				return _arr.join(spliter1);
			} else {
				throw 'map is invalid';
			}
		} catch(e) {
			alert(e.message);
		}
	};
	WG.strlen = function(str) {
		var len = 0;
		for (var i = 0; i < str.length; i++) {
			len += (str.charCodeAt(i) > 255 ? 2 : 1);
		}
		return len;
	};
	WG.is = function(obj, type) {
		return typeof obj === type;
	};
	WG.isFunction = (function(obj) {
		var _f;
		return "object" === typeof document.getElementById ? _f = function(fn) {
			try {
				return /^\s*\bfunction\b/.test("" + fn);
			} catch(x) {
				return false;
			}
		}: _f = function(fn) {
			return "[object Function]" === Object.prototype.toString.call(fn);
		};
	})();
	WG.isArray = Array.isArray || function(obj) {
		return WG.type(obj) === "array";
	};
	WG.isPlainObject = function(obj) {
		if (!obj || WG.type(obj) !== "object" || obj.nodeType || WG.isWindow(obj)) {
			return false;
		}
		if (obj.constructor && !hasOwn.call(obj, "constructor") && !hasOwn.call(obj.constructor.prototype, "isPrototypeOf")) {
			return false;
		}
		var key;
		for (key in obj) {}
		return key === undefined || hasOwn.call(obj, key);
	};
	WG.type = function(obj) {
		if (obj == null) {
			return String(obj);
		}
		return typeof obj === "object" || typeof obj === "function" ? class2type[core_toString.call(obj)] || "object": typeof obj;
	};
	WG.isWindow = function(obj) {
		return obj && typeof obj === "object" && "setInterval" in obj;
	};
	WG.contains = function(str, substr) {
		return !! ~ ('' + str).indexOf(substr);
	};
	WG.filterXSS = function(str) {
		if (!WG.is(str, 'string')) return str;
		return str.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;").replace(/\'/g, "&apos;");
	};
	WG.htmlDecode = function(text) {
		var temp = document.createElement("div");
		temp.innerHTML = text;
		var output = temp.innerText || temp.textContent;
		temp = null;
		return output;
	};
	WG.htmlEncode = function(html) {
		var temp = document.createElement("div"); (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
		var output = temp.innerHTML;
		temp = null;
		return output;
	};
	WG.jsonEncode = function(str) {
		var s = "";
		if (str.length == 0) return "";
		s = str.replace(/\\/g, "\\\\");
		s = s.replace(/"/g, '\\"');
		s = s.replace(/\r/g, "\\r");
		s = s.replace(/\n/g, "\\n");
		return s;
	};
	WG.jsonDecode = function(str) {
		var s = "";
		if (str.length == 0) return "";
		s = str.replace(/\\"/g, '"');
		s = s.replace(/\\r/g, "\r");
		s = s.replace(/\\n/g, "\n");
		s = s.replace(/\\\\/g, "\\");
		return s;
	};
	WG.ImgUploader = function(uploadStatusCb) {
		var _popupHtml = ['<div class="mod-pop">', ' <div class="mod-pop__mask"></div>', ' <div class="mod-pop__main">', '<div class="mod-pop__hd">', '<div class="mod-pop__title">温馨的提示</div>', '<a class="mod-pop__close" href="javascript:;"  op="close"></a>', '</div>', '<div class="mod-pop__bd">', '<div class="mod-pop__content">', '<div class="file_box">', '            <a href="javascript:;" class="btnb_normal" style="display:;">上传文件</a>', '              <form id="form" action="http://cgi.trade.qq.com/cgi-bin/tools/img_upload/img_uploadv2.fcg" method="post" enctype="multipart/form-data" target="bridgeframe">', '  <input class="btn_file" type="file" name="file" id="file" hidefocus=""/>', '    </form>', '   </div>', '            <div class="audio_edit">', '                <div class="mod_audio">', '     <p class="uploading" style="display:none;">正在上传中......</p>', '     <IFRAME id="bridgeframe" name="bridgeframe" src="" ', '      frameborder="0" style="display:none"></IFRAME>', '    </div>', '            </div>', '    </div>', '</div>', '<div class="mod-pop__ft">', '<a class="button button_primary" href="javascript:;" op="submit">确定</a><a class="button" href="javascript:;"  op="close">取消</a>', '</div>', '</div>', '</div>'];
		this.div = null;
		this.mask = null;
		this.open = function() {
			var that = this;
			var div = document.getElementById('__fileuploader__') || document.createElement('div');
			div.id = "__fileuploader__";
			div.innerHTML = _popupHtml.join('');
			document.body.appendChild(div);
			that.div = div;
			that.fitPopUpPos($('#__fileuploader__'));
			that.registCallback();
			that.showMask();
			$('#__fileuploader__').on('click',
			function(evt) {
				var tar = evt.target;
				var op = tar.getAttribute('op');
				var frm = null,
				_file = null;
				switch (op) {
				case 'submit':
					$('.uploading').show();
					frm = div.getElementsByTagName('form')[0];
					_file = div.getElementsByTagName('input')[0];
					if (_file.value.length <= 0) {
						alert('还没选择文件！');
						return;
					}
					frm.submit();
					break;
				case 'close':
					that.clear();
					break;
				}
			});
			$('#file').on('change',
			function() {
				$('.uploading').show();
				frm = div.getElementsByTagName('form')[0];
				frm.submit();
			});
		};
		this.showMask = function() {
			var _mask = document.createElement('div');
			_mask.className = 'mod_pop_mask';
			this.mask = _mask;
			document.body.appendChild(_mask);
		};
		this.hideMask = function() {
			if (this.mask) {
				document.body.removeChild(this.mask);
			}
		};
		this.fitPopUpPos = function(wnd) {
			var mask = wnd.find('.mod-pop__mask');
			wnd = wnd.find('.mod-pop__main');
			var body = $(window);
			var _wndWidth = wnd.width(),
			_wndHeight = wnd.height();
			var _docWidth = body.width(),
			_docHeight = body.height();
			var _left = (parseInt(_docWidth, 10) - parseInt(_wndWidth, 10)) / 2;
			var _top = (parseInt(_docHeight, 10) - parseInt(_wndHeight, 10)) / 2;
			wnd.css({
				top: _top,
				left: _left
			});
			mask.css({
				height: _docWidth
			});
		};
		this.registCallback = function() {
			var that = this;
			document.domain = 'qq.com';
			window['dealReturn'] = function(dt) {
				if (dt.ret == 0) {
					if ('function' == typeof uploadStatusCb) {
						uploadStatusCb.call(that, dt);
						that.clear();
					}
				} else {
					alert(dt.umsg);
				}
			};
		};
		this.clear = function() {
			window['dealReturn'] = null;
			$('#__fileuploader__').unbind('click');
			this.div.parentNode.removeChild(this.div);
			this.hideMask();
		};
	};
	WG.popup = function(options) {
		var tpl = '<div class="mod-pop tpl_popup">\
         <div class="mod-pop__mask" style="position:fixed;"></div>\
         <div class="mod-pop__main" style="z-index:1000;">\
             <div class="mod-pop__hd">\
                 <div class="mod-pop__title">{[title]}</div>\
                 <a class="mod-pop__close tpl_close" href="javascript:;"></a>\
             </div>\
             <div class="mod-pop__bd">\
                 <div class="mod-pop__content">{[content]}</div>\
             </div>\
             <div class="mod-pop__ft">\
                 <a class="button button_primary tpl_confirm" style="display:{[confirm_show]};" href="javascript:;">{[confirm_txt]}</a>\
                 <a class="button tpl_cancel" style="display:{[cancel_show]};" href="javascript:;">{[cancel_txt]}</a>\
             </div>\
         </div>\
     </div>',
		setting = {
			title: "温馨提示",
			content: "文本",
			confirm_show: "inline-block",
			confirm_txt: "确定",
			cancel_show: "inline-block",
			cancel_txt: "关闭",
			confirm_auto: true,
			cancel_auto: true,
			close_auto: true,
			confirm_cbk: function() {
				$(".tpl_popup").remove();
			},
			cancel_cbk: function() {
				$(".tpl_popup").remove();
			},
			close_cbk: function() {
				$(".tpl_popup").remove();
			}
		};
		if (location.href != top.location.href) {
			top.WG.popup(options);
			return;
		}
		function fitPopup() {
			var wWidth = $(window).width(),
			wHeight = $(window).height(),
			elemWidth = $(".mod-pop__main").width(),
			elemHeight = $(".mod-pop__main").height();
			var _left = (parseInt(wWidth, 10) - parseInt(elemWidth, 10)) / 2;
			var _top = (parseInt(wHeight, 10) - parseInt(elemHeight, 10)) / 2;
			$(".mod-pop__main").css('top', _top + 'px');
			$(".mod-pop__main").css('left', _left + 'px');
		}
		if (options && options.tpl) {
			tpl = options.tpl;
		}
		$.extend(setting, options);
		var tips = tpl.replace(/\{\[([\w\d]+)\]\}/g,
		function(m, n, p) {
			return typeof setting[n] !== undefined ? setting[n] : m;
		});
		$(".tpl_popup").remove();
		$(document.body).append(tips);
		fitPopup();
		function confirm_cbk() {
			setting.confirm_cbk();
			if (setting.confirm_auto) {
				$(".tpl_popup").remove();
			}
		}
		function cancel_cbk() {
			setting.cancel_cbk();
			if (setting.cancel_auto) {
				$(".tpl_popup").remove();
			}
		}
		function close_cbk() {
			setting.close_cbk();
			if (setting.close_auto) {
				$(".tpl_popup").remove();
			}
		}
		$(".tpl_confirm").unbind("click", confirm_cbk);
		$(".tpl_confirm").bind("click", confirm_cbk);
		$(".tpl_cancel").unbind("click", cancel_cbk);
		$(".tpl_cancel").bind("click", cancel_cbk);
		$(".tpl_close").unbind("click", close_cbk);
		$(".tpl_close").bind("click", close_cbk);
	};
	WG.fullScreen	=	function() {
		var elem	=	document.documentElement;
		if (elem.requestFullscreen) {
			elem.requestFullscreen();
		} else if (elem.webkitRequestFullScreen) {
			// 对 Chrome 特殊处理，
			// 参数 Element.ALLOW_KEYBOARD_INPUT 使全屏状态中可以键盘输入。
			if ( window.navigator.userAgent.toUpperCase().indexOf( 'CHROME' ) >= 0 ) {
				elem.webkitRequestFullScreen( Element.ALLOW_KEYBOARD_INPUT );
			} else {
				// Safari 浏览器中，如果方法内有参数，则 Fullscreen 功能不可用。
				elem.webkitRequestFullScreen();
			}
		} else if (elem.mozRequestFullScreen) {
			elem.mozRequestFullScreen();
		}
	};
	WG.cancelFullScreen	=	function(){
		/** 取消全屏 */
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.webkitCancelFullScreen) {
			document.webkitCancelFullScreen();
		} else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		}
	};
	WG.ftime = function(tm, fmt, dts) {
		var dt = new Date(tm * 1000),
		dts = dts || '/',
		y = dt.getFullYear(),
		m = dt.getMonth() + 1,
		d = dt.getDate(),
		h = dt.getHours(),
		i = dt.getMinutes(),
		s = dt.getSeconds(),
		format = {
			md: (m < 10 ? '0' + m: m) + dts + (d < 10 ? '0' + d: d),
			hi: (h < 10 ? '0' + h: h) + ':' + (i < 10 ? '0' + i: i)
		};
		format.his = format.hi + ':' + (s < 10 ? '0' + s: s);
		format.ymd = y + dts + format.md;
		format.mdhi = format.md + ' ' + format.hi;
		format.mdhis = format.md + ' ' + format.his;
		format.ymdhi = format.ymd + ' ' + format.hi;
		format.ymdhis = format.ymd + ' ' + format.his;
		if (fmt in format) {
			return format[fmt];
		} else {
			return format.ymdhis;
		}
	};
	WG.template = function(str, data) {
		var fn = null;
		if (!/\W/.test(str)) {
			var val = $("#" + str, top.document.body).val() || $("#" + str, top.document.body).html();
			fn = WG.template(val, data);
		} else {
			var fun_str = "var p=[],print=function(){p.push.apply(p,arguments);};" + "p.push('" + str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');return p.join('');";
			fn = new Function("obj", fun_str);
		}
		return data && typeof fn == 'function' ? fn(data) : fn;
	}
	if (typeof win['WG'] == 'undefined') {
		win.WG = WG;
	}
})(window);


/**
 * js模板方法
 */
var txTpl = (function() {
	var cache = {};
	return function(str, data, startSelector, endSelector, isCache) {
		var fn, d = data,
		valueArr = [],
		isCache = isCache != undefined ? isCache: true;
		if (isCache && cache[str]) {
			for (var i = 0,
			list = cache[str].propList, len = list.length; i < len; i++) {
				valueArr.push(d[list[i]]);
			}
			fn = cache[str].parsefn;
		} else {
			var propArr = [],
			formatTpl = (function(str, startSelector, endSelector) {
				if (!startSelector) {
					var startSelector = '<%';
				}
				if (!endSelector) {
					var endSelector = '%>';
				}
				var tpl = str.indexOf(startSelector) == -1 ? document.getElementById(str).innerHTML: str;
				return tpl.replace(/\\/g, "\\\\").replace(/[\r\t\n]/g, " ").split(startSelector).join("\t").replace(new RegExp("((^|" + endSelector + ")[^\t]*)'", "g"), "$1\r").replace(new RegExp("\t=(.*?)" + endSelector, "g"), "';\n s+=$1;\n s+='").split("\t").join("';\n").split(endSelector).join("\n s+='").split("\r").join("\\'");
			})(str, startSelector, endSelector);
			for (var p in d) {
				propArr.push(p);
				valueArr.push(d[p]);
			}
			fn = new Function(propArr, " var s='';\n s+='" + formatTpl + "';\n return s");
			isCache && (cache[str] = {
				parsefn: fn,
				propList: propArr
			});
		}
		try {
			return fn.apply(null, valueArr);
		} catch(e) {
			function globalEval(strScript) {
				var ua = navigator.userAgent.toLowerCase(),
				head = document.getElementsByTagName("head")[0],
				script = document.createElement("script");
				if (ua.indexOf('gecko') > -1 && ua.indexOf('khtml') == -1) {
					window['eval'].call(window, fnStr);
					return
				}
				script.innerHTML = strScript;
				head.appendChild(script);
				head.removeChild(script);
			}
			var fnName = 'txTpl' + new Date().getTime(),
			fnStr = 'var ' + fnName + '=' + fn.toString();
			globalEval(fnStr);
			window[fnName].apply(null, valueArr);
		}
	}
})();

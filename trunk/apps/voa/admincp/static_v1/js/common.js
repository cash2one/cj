function operationWindowSize() {
	var $window = jQuery('html');
	var $nav = jQuery('#page-nav');
	var $container = jQuery('#page-container');
	var $browerWarning = jQuery('#brower-warning');
	var $vcyBody = jQuery('.vcy-body');
	var $vcyBodyWrapper = jQuery('.vcy-body-wrapper');
	var $vcyMenuWrapper = jQuery('.vcy-menu-wrapper');
	var height = $window.height() - $nav.outerHeight();
	$vcyMenuWrapper.height(height).css('top', 0);
	$vcyBodyWrapper.height(height).css({"right":0});
	$vcyBody.height($vcyBodyWrapper.height() - ($vcyBody.outerHeight() - $vcyBody.height())).css({"overflow-x":"hidden","margin-bottom":"0","padding-bottom":"0"});
	jQuery(".vcy-body-box").css({"margin-bottom":"0","padding-bottom":"0"});
}

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

/** 统计输入区的字符数 */
function counter(obj, allowHTML) {
	if (typeof(allowHTML) == 'undefined') {
		allowHTML = true;
	}
	var id = obj.attr('id');
	var tipObj = jQuery('#'+id+'-tip');
	var maxLength = obj.attr('maxlength') ? obj.attr('maxlength') : obj.attr('data-maxlength');
	var text = obj.val();
	var length = text.length;
	var tipString = length + ' / ' + maxLength;
	tipObj.text(tipString);
	if (length >= maxLength) {
		tipObj.css('color','red');
	} else {
		tipObj.css('color','');
	}
	/*
	if (length >= maxLength) {
		tipObj.addClass('text-danger');
	} else {
		tipObj.removeClass('text-danger');
	}
	*/
}

/** 统计输入区的字符数（包含加载页面时进行初始化计算） */
function reCounter(id, allowHTML){
	if (typeof(allowHTML) == 'undefined') {
		allowHTML = true;
	}
	var obj = jQuery('#'+id);
	counter(obj, allowHTML);
	jQuery('#'+id).keyup(function(){
		counter(obj, allowHTML);
	});
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
	WG.format = function(tpl, obj) {
		return tpl.replace(/\{([\d\w]+)\}/g, function(m, n) {
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
	WG.popup = function(options) {
		var tpl = '<div class="mod-pop tpl_popup" style="z-index:999999999;">\
         <div class="mod-pop__mask" style="position:fixed;z-index:999999999;"></div>\
         <div class="mod-pop__main" style="z-index:999999999;">\
             <div class="mod-pop__hd">\
                 <div class="mod-pop__title">{[title]}</div>\
                 <a class="mod-pop__close tpl_close" href="javascript:;"></a>\
             </div>\
             <div class="mod-pop__bd">\
                 <div class="mod-pop__content">{[content]}</div>\
             </div>\
             <div class="mod-pop__ft">\
                 <a class="btn btn-primary tpl_confirm" style="display:{[confirm_show]};" href="javascript:;">{[confirm_txt]}</a>\
			     <span class="space"></span>\
                 <a class="btn btn-default tpl_cancel" style="display:{[cancel_show]};" href="javascript:;">{[cancel_txt]}</a>\
             </div>\
         </div>\
     </div>',
		setting = {
			title: "操作提示",
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
			for (var i = 0, list = cache[str].propList, len = list.length; i < len; i++) {
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

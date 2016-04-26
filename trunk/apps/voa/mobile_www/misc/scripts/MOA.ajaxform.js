/**
 * 使用 ajax 的方式提交 form
 * $Author$
 * $Id$
 */

var MAjaxForm = (function() {
	var _q = MOA.query.one;
	var _ajaxHandle = 0;
	var _scripts = new Array();
	var _append_parent = 'append_parent';
	/**
	 * 两个字串异或操作
	 * @param	s1	字串；
	 * @param	s2	字串；
	 */
	var _stringxor = function(s1, s2) {
		var s = '';
		var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		var max = Math.max(s1.length, s2.length);
		for(var i = 0; i < max; i ++) {
			var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
			s += hash.charAt(k % 52);
		}
		return s;
	};
	/** 
	 * 得到一个定长的 hash 值， 依赖于 stringxor()
	 * @param	string	需要计算 hash 值的字串；
	 * @param	length	需要计算的长度；
	 */
	var _hash = function(string, length) {
		var length = length ? length : 32;
		var start = 0;
		var result = '';
		var filllen = length - string.length % length;
		for(var i = 0; i < filllen; i++){
			string += "0";
		}
		while(start < string.length) {
			result = _stringxor(result, string.substr(start, length));
			start += length;
		}
		return result;
	};
	/**
	 * 仿 php 的 in_array 函数
	 * @param string 需要查找的字串
	 * @param array 数组
	 */
	var _in_array = function(needle, haystack) {
		if('string' == typeof(needle) || 'number' == typeof(needle)) {
			for(var i in haystack) {
				if(haystack[i] == needle) {
					return true;
				}
			}
		}
		return false;
	};
	/**
	 * 增加一个 script 标签
	 * @param	src		javascript 链接地址；
	 * @param	text	javascript 脚本；
	 * @param	reload	是否重新加载；
	 * @param	charset	指定 javascript 的字符集；
	 */
	var _appendscript = function(src, text, reload, charset) {
		var id = _hash(src + text);
		if(!reload && _in_array(id, _scripts)) {
			return;
		}
		if(reload && _q('#' + id)) {
			_q('#' + id).parentNode.removeChild(_q('#' + id));
		}
		_scripts.push(id);
		var scriptNode = document.createElement("script");
		scriptNode.type = "text/javascript";
		scriptNode.id = id;
		scriptNode.charset = charset;
		try {
			if(src) {
				scriptNode.src = src;
			} else if(text){
				scriptNode.text = text;
			}
			_q('#' + _append_parent).appendChild(scriptNode);
		} catch(e) {}
	};
	/**
	 * 解析字串中的 javascript 脚本
	 * @param	s	字串；
	 */
	var _evalscript = function(s) {
		if(-1 == s.indexOf('<script')) {
			return s;
		}
		var p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
		s = s.replace(p, function($0, $1, $2, $3) {
			_appendscript($1, '', $2, $3);
			return '';
		});
		p = /<script (?!src)[^\>]*?( reload=\"1\")?>([^\x00]+?)<\/script>/ig;
		s = s.replace(p, function($0, $1, $2) {
			_appendscript('', $2, $1);
			return '';
		});
		return s;
	};
	/** 判断是否为数组 */
	var _isArray = function(obj) {
		try {
			if(-1 == obj.constructor.toString().indexOf("function Array")) {
				return false;
			}
		} catch(e) {
			return false;
		}
		return true;
	};
	/** 变量是否为空 */
	var _isEmpty = function(obj) {
		var result = false;
		switch(typeof(obj)) {
			case 'number':if(0 == obj) {result = true;}break;
			case 'string':if(0 == obj.length) {result = true;}break;
			case 'undefined':result = true;break;
			default:if(null == obj || (_isArray(obj) && 0 == obj.length)) {result = true;}break;
		}
		return result;
	};
	/** 判断是否为对象 */
	var _isObject = function(obj) {
		try {
			if(-1 == obj.constructor.toString().indexOf("function Object")) {
				return false;
			}
		} catch(e) {
			return false;
		}
		return true;
	};
	/**
	 * 创建临时 Iframe 框架;
	 * @param iframeid	Iframe 的 ID;
	 */
	var _createIframe = function(iframeid) {
		var iframe = _q('#' + iframeid);
		if(iframe) {
			iframe.parentNode.removeChild(iframe);
		}
		try{
			iframe = document.createElement('<iframe name="' + iframeid + '"></iframe>');
		} catch(e) {
			iframe = document.createElement('iframe');
			iframe.name = iframeid;
		}
		iframe.name = iframeid;
		iframe.id = iframeid;
		iframe.style.display = 'none';
		_q('#' + _append_parent).appendChild(iframe);
		return iframe;
	};
	/**
	 * 创建临时 Form 表单;
	 * @param formid	Form 的 ID;
	 * @param url		Form 的 action;
	 * @param method	Form 的提交方式(get, post);
	 * @param enctype	Form 的 enctype 类型;
	 */
	var _createForm = function(formid, url, method, enctype) {
		var form = _q('#' + formid);
		if(form) {
			form.parentNode.removeChild(form);
		}
		form = document.createElement("form");
		form.name = formid;
		form.id = formid;
		form.method = method;
		if(!_isEmpty(enctype)) {
			form.enctype = enctype;
		}
		form.style.display = 'none';
		_q('#' + _append_parent).appendChild(form);
		var input = document.createElement("input");
		input.type = 'hidden';
		input.name = 'inajax';
		input.value = 1;
		form.appendChild(input);
		return form;
	};
	/** 检测url是相对地址还是绝对地址 */
	var _checkUrl = function(url) {
		var re = new RegExp("^(http|https|ftp|telnet|mms|rtsp|javascript)\\:", 'ig');
		if(re.test(url)) {
			return url;
		}
		return '/' == url.charAt(0) ? url : ('/' + url);
	};
	/**
	 * 解析网址中的变量;
	 * @param url	网址;
	 * @param data	是否只是数据字串;
	 */
	var _parseUrlParam = function(url, data) {
		if(_isEmpty(data) || false === _isObject(data)) {
			data = {};
		}
		var tReg = new RegExp('\\?(.*)', 'g');
		var querys = tReg.exec(url);
		if(null != querys) {
			var query = querys[1];
			var params = query.split('&');
			for(var i in params) {
				index = params[i].indexOf('=');
				if(-1 == index) {
					continue;
				}
				data[params[i].substr(0, index)] = params[i].substr(index + 1);
			}
		}
		return data;
	};
	/**
	 * 模拟 XMLHttpRequest 的 POST 方式请求数据，这种方式可以避免乱码;
	 * @param string url	请求的地址;
	 * @param object data	提交的数据;
	 * @param string method	form 表单的提交方式;
	 * @param string showid	数据显示的对象ID;
	 * @param function callback	回调函数, 对数据的后期处理;
	 * @param string ctrlid		事件源ID;
	 */
	var _analog = function(url, data, method, callback, showid, ctrlid, timeout) {
		if(0 != _ajaxHandle) {
			return false;
		}
		if('post' == method.toLowerCase()) {
			method = 'post';
		} else {
			method = 'get';
		}
		var params = _parseUrlParam(url, data);
		var ajIframeid = 'ajaxIframe_analog';
		var ajIframe = _createIframe(ajIframeid);
		var ajFormid = 'ajaxForm_analog';
		var ajForm = _createForm(ajFormid, url, method);
		var input;
		ajForm.action = _checkUrl(url + (-1 == url.indexOf("?") ? '?' : '&') + 'inajax=1');
		ajForm.target = ajIframeid;
		for(var i in params) {
			input = document.createElement("input");
			input.type = 'hidden';
			input.name = i;
			input.value = params[i];
			ajForm.appendChild(input);
		}
		_ajaxHandle = [showid, ajIframeid, ajFormid, callback, timeout, ctrlid];
		ajIframe.removeEventListener('load', _ajaxLoad);
		ajIframe.addEventListener('load', _ajaxLoad);
		_q('#' + ajFormid).submit();
		return false;
	};
	/**
	 * 利用 Iframe 和 form 来模拟实现 Ajax 效果
	 * @param	formid		表单 ID;
	 * @param	showid		Ajax 返回时的提示信息的标签 ID;
	 * @param	callback	回调函数;
	 * @param	waiting		Ajax 请求时的提示信息;
	 * @param	ctrlid		事件源ID;
	 * @param	timeout		在关闭弹出层时的延迟事件;
	 */
	var _submit = function(formid, callback, showid, waiting, ctrlid, timeout) {
		if(0 != _ajaxHandle) {
			return false;
		}
		var ajIframeid = 'ajaxIframe';
		var ajIframe = _createIframe(ajIframeid);
		var ajForm = _q('#' + formid);
		if(null == ajForm) {
			alert("Form's id is error.");
			return false;
		}
		ajForm.target = ajIframeid;
		var formAction = ajForm.getAttribute('action');
		ajForm.setAttribute('action', formAction + (-1 == formAction.indexOf("?") ? '?' : '&') + 'inajax=1');
		_ajaxHandle = [showid, ajIframeid, formid, callback, timeout, ctrlid];
		ajIframe.removeEventListener('load', _ajaxLoad);
		ajIframe.addEventListener('load', _ajaxLoad);
		ajForm.submit();
		return false;
	};
	/** Ajax 返回信息加载完成后的处理函数； */
	var _ajaxLoad = function() {
		if("undefined" == typeof(_ajaxHandle[4])) {
			_ajaxHandle[4] = 1000;
		}
		var s = "";
		try {
			s = _q('#' + _ajaxHandle[1]).contentWindow.document.documentElement.firstChild.nodeValue;
		} catch(e) {
			s = _q('#' + _ajaxHandle[1]).contentWindow.document.XMLDocument.text;
		}
		var ajaxResult;
		if(-1 != s.indexOf('ajaxOk')) {
			ajaxResult = 1;
		} else {
			ajaxResult = 0;
		}
		s = _evalscript(s);
		if(_ajaxHandle[3]) {
			var callbackFunc = _ajaxHandle[3];
			setTimeout(function() {
				callbackFunc(s, ajaxResult);
			}, 10);
		}

		if(_q('#' + _ajaxHandle[0])) {
			_q('#' + _ajaxHandle[0]).innerHTML = s;
		}

		var ctrlId = _ajaxHandle[5];
		var hideTime = _ajaxHandle[4];
		setTimeout(function() {
			_hideMenu(ctrlId);
		}, hideTime);

		_ajaxHandle = 0;
	};
	var _hideMenu = function() {};
	
	return {'submit':_submit, 'analog':_analog};
}());
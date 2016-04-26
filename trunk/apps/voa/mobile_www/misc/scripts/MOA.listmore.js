/**
 * 读取更多
 * $Author$
 * $Id$
 */

/** Ajax 锁, 防止用户频繁点击 */
var c_list_more_ajax_lock = false;
function c_list_more() {
	var q = MOA.query.one;
	/** 源id */
	var source_id;
	/** 目标id */
	var container_id;
	/** 请求参数 key => value */
	var params = {};
	/** 数据处理回调方法 */
	var fn_cb = null;
	/** 获取参数的外部方法 */
	var fn_param = null;
	var __method__ = this;
	
	/** 读取列表 */
	this.fetch = function(e) {
		if (c_list_more_ajax_lock) {
			return;
		}

		c_list_more_ajax_lock = true;
		MLoading.show('稍等片刻...');
		var params = {};
		if ('function' == typeof(__method__.fn_param)) {
			params = __method__.fn_param();
		} else {
			for (var k in __method__.params) {
				params[k] = eval(__method__.params[k]);
			}
		}
		
		// 当前页面url加入随机字符串避免浏览器缓存
		var _url = window.location.href;
		_url = _url.replace(/[&|\?]_random=[^&]+/, '');
		if (_url.indexOf('?') != -1) {
			_url += '&';
		} else {
			_url += '?';
		}
		_url += '_random=' + Math.random();

		MAjaxForm.analog(_url, params, 'post', function (s) {
			c_list_more_ajax_lock = false;
			MLoading.hide();
			if (s.replace(/^\s+$/g, '') == '') {
				return;
			}
			if ('function' == typeof(__method__.fn_cb)) {
				__method__.fn_cb(s);
				return;
			}
			
			$append($one('#' + __method__.container_id), s);
		});
	}
	
	/** 
	 * 初始化 
	 * @param string source_id 源id
	 * @param string container_id 目标id
	 * @param object params post参数, key => value 键值对
	 * @param object funcs 回调方法相关, key => function
	 *  + params 获取参数的外部方法
	 *  + callback 处理返回数据的回调方法
	 */
	this.init = function(source_id, container_id, params, funcs) {
		__method__.source_id = source_id;
		__method__.container_id = container_id;
		if ('object' == typeof(params)) {
			__method__.params = params;
		}
		
		if ('object' == typeof(funcs)) {
			for (var k in funcs) {
				if ('params' == k) {
					__method__.fn_param = funcs[k];
				} else if ('callback' == k) {
					__method__.fn_cb = funcs[k];
				}
			}
		}
		
		var ele = q('#' + source_id);
		if (!ele) {
			return;
		}
		
		ele.addEventListener('click', __method__.fetch);
	}
}
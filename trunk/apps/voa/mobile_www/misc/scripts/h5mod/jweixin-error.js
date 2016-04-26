/**
 * 用于微信接口的公共错误处理库方法
 * Created by Deepseath on 2015/4/8.
 */
define(['zepto', 'underscore', "jweixin", 'frozen'], function ($, _, wx) {
	function JWXERROR() {
		var _self = this;
		$(function () {
			// 微信接口加载错误
			wx.error(function (res) {
				_self.errhandle(res, 'jweixin', 'config', true, true);
			});
		});
	}

	JWXERROR.prototype = {
		/**
		 * 错误的处理主调用方法
		 * @param res 微信接口返回的数据对象
		 * @param function_name 使用的接口名
		 * @param callback_name 返回错误的回调名 cancel|config|fail等
		 * @param post_report 是否发送报告，不定义或者true则不发送报告
		 * @param show_tip 是否弹出错误提示信息，不定义或者true则弹出消息提示框
		 */
		"errhandle": function(res, function_name, callback_name, post_report, show_tip) {

			var __error = this._errmsg(res['errMsg'], function_name, callback_name);
			// 显示错误提示信息
			if (typeof(show_tip) == 'undefined' || show_tip == true) {
				$.dialog({
					"content": __error,
					"button": ["关闭"]
				});
			}

			// 发送错误报告
			if (typeof(post_report) == 'undefined' || post_report == true) {
				this._report(res, function_name, callback_name);
			}

			return __error;
		},
		/**
		 * 返回可读的人性化错误提示
		 * @param msg 消息内容（一般为微信返回的res）
		 * @param function_name 使用的接口名
		 * @param callback_name 使用返回的回调名cancel|fail等
		 * @returns {string}
		 */
		"_errmsg": function (msg, function_name, callback_name) {

			// 针对获取地理位置接口的特殊标记
			if (callback_name == 'cancel' && function_name == 'getLocation') {
				return '您需要允许微信获取您的地理位置，才能继续操作';
			}

			// 检查浏览器代理头，确定微信版本号
			var wechatInfo = navigator.userAgent.match(/MicroMessenger\/([\d\.]+)/i);
			if (!wechatInfo) {
				return '对不起，本操作仅支持在微信客户端内进行';
			} else if (typeof(wechatInfo[1]) != 'undefined' && wechatInfo[1] < '6.1') {
				return '您的微信版本太低，请升级后体验更好的服务';
			}

			if (msg == 'system:function not exist') {
				return '您的微信版本太低，请升级微信客户端到最新版后再试';
			}

			if (msg.match(/accessControl\s*:\s*not allow/i) != null) {
				return '您的微信版本太低，请升级微信客户端到最新版后再试';
			}

			return '获取地理位置错误, 请确认开启了GPS, 然后重新操作';
			var callback_name_string = '';
			if (callback_name == 'config') {
				callback_name_string = '错误类型: 配置接口';
			} else if (callback_name == 'fail') {
				callback_name_string = '错误类型: 接口错误';
			} else if (callback_name == 'cancel') {
				callback_name_string = '错误类型: 操作取消';
			} else {
				callback_name_string = '错误类型: 未知';
			}

			if (msg.match(function_name) == null) {
				callback_name_string += '<br />API: ' + function_name + '';
			}

			return '微信接口请求错误：<br />' + msg + '<br />' + callback_name_string;
		},
		/**
		 * 提交错误报告
		 * @param res
		 * @param function_name
		 * @param return_name
		 */
		"_report": function (res, function_name, return_name) {
			// 取消操作不发报告
			if (return_name == 'cancel') {
				return;
			}
			var type = function_name;
			if (typeof(return_name) != 'undefined') {
				type += '_' + return_name;
			}
			$.ajax({
				"type": "POST",
				"url": "/api/common/post/report",
				"data": {
					"res": res,
					"type": type,
					"url": location.href,
					"useragent": navigator.userAgent
				},
				"dataType": "json"
			});
		}
	};
	return JWXERROR;
});

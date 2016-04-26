/**
 * 微信分享接口
 *
 * 使用方法：
 * var wxshare = new WXShow();
 * wxshare.load({
			"share_type": [],// 分享类型：timeline、appmessage、qq、weibo，默认为空，全部
			"title": "",// 分享标题
			"desc": "",// 分享描述
			"link": "",// 分享链接
			"imgUrl": "",// 分享图标
			"type": "",// 分享类型,music、video或link，不填默认为link
			"dataUrl": "",// 如果type是music或video，则要提供数据链接，默认为空
			"cb_success": "",// 成功时的回调函数名
			"cb_cancel": ""// 失败时的回调函数名
		});
 // 若某个参数想使用默认的，则不要定义
 *
 * Created by Deepseath on 2015/5/28.
 */
define(["zepto", "underscore", "jweixin", "h5mod/jweixin-error"], function ($, _, wx, jweixin_error) {

	function WXShare() {
		var _self = this;
		// 微信接口是否已加载完毕
		_self.wx_loaded = false;
		// 需要的参数
		_self.options = {
			"share_type": '',//["timeline", "appmessage", "qq", "weibo"],// 分享类型：timeline、appmessage、qq、weibo，默认为空，全部
			"title": "",// 分享标题
			"desc": "",// 分享描述
			"link": "",// 分享链接
			"imgUrl": "",// 分享图标
			"type": "",// 分享类型,music、video或link，不填默认为link
			"dataUrl": "",// 如果type是music或video，则要提供数据链接，默认为空
			"cb_success": "",// 成功时的回调函数名
			"cb_cancel": ""// 失败时的回调函数名
		};
		// 事件监听，初始化接口加载状态
		$(function () {
			if (navigator.userAgent.match(/MicroMessenger/i)) {
				// 只有微信客户端进行此操作
				// 微信接口加载完毕
				wx.ready(function () {
					_self.wx_loaded = true;
				});
				// 微信接口加载错误
				wx.error(function (res) {
					_self._errhandle(res, 'config');
				});
			}
		});
	}

	WXShare.prototype = {
		"load": function (options) {
			var _self = this;
			// 初始化参数
			_self._init(options);
			if (_self.wx_loaded) {
				// 如果接口配置已加载
				_self._doit();
			} else {
				// 未加载，则循环检测微信接口配置是否加载成功
				var it = setInterval(function () {
					// 如果已加载微信接口
					if (_self.wx_loaded) {
						_self._doit();
						// 停止定时器
						clearInterval(it);
						return false;
					}
				}, 500);
			}
		},
		/**
		 * 参数初始化
		 */
		"_init": function (options) {
			this.options = $.extend({}, this.options, options);
		},
		/**
		 * 绑定动作
		 */
		"_doit": function () {
			var _self = this;
			if (_self.options['share_type'] != "") {
				switch (_self.options['share_type']) {
					case 'timeline':
						_self._share_timeline();
						return true;
					case 'appmessage':
						_self._share_appmessage();
						return true;
					case 'qq':
						_self._share_qq();
						return true;
					case 'weibo':
						_self._share_weibo();
						return true;
				}
			}
			_self._share_timeline();
			_self._share_appmessage();
			_self._share_qq();
			_self._share_weibo();
			/*
			 if (_self.options['share_type'] == 'timeline') {
			 _self._share_timeline();
			 }
			 if (_self.options['share_type'] == 'appmessage') {
			 _self._share_appmessage();
			 }
			 if (_self.options['share_type'] == 'qq') {
			 _self._share_qq();
			 }
			 if (_self.options['share_type'] == 'weibo') {
			 _self._share_weibo();
			 }*/
		},
		/**
		 * 分享到微信圈
		 */
		"_share_timeline": function () {
			var _self = this;
			wx.onMenuShareTimeline({
				"title": _self.options['title'], // 分享标题
				"link": _self.options['link'], // 分享链接
				"imgUrl": _self.options['imgUrl'], // 分享图标
				"success": function (res) {
					_self._callback(_self.options['cb_success'], res, 'success');
				},
				"cancel": function () {
					_self._callback(_self.options['cb_fail'], res, 'fail');
				},
				"fail": function (res) {
					_self._errhandle(res, 'onMenuShareTimeline');
				}
			});
		},
		/**
		 * 分享到朋友
		 */
		"_share_appmessage": function () {
			var _self = this;
			wx.onMenuShareAppMessage({
				"title": _self.options['title'], // 分享标题
				"desc": _self.options['desc'], // 分享描述
				"link": _self.options['link'], // 分享链接
				"imgUrl": _self.options['imgUrl'], // 分享图标
				"type": '', // 分享类型,music、video或link，不填默认为link
				"dataUrl": '', // 如果type是music或video，则要提供数据链接，默认为空
				"success": function () {
					// 用户确认分享后执行的回调函数
					_self._callback(_self.options['cb_success'], res, 'success');
				},
				"cancel": function () {
					// 用户取消分享后执行的回调函数
					_self._callback(_self.options['cb_fail'], res, 'fail');
				},
				"fail": function (res) {
					_self._errhandle(res, 'onMenuShareAppMessage');
				}
			});
		},
		/**
		 * 分享到QQ
		 */
		"_share_qq": function () {
			var _self = this;
			wx.onMenuShareQQ({
				"title": _self.options['title'], // 分享标题
				"desc": _self.options['desc'], // 分享描述
				"link": _self.options['link'], // 分享链接
				"imgUrl": _self.options['imgUrl'], // 分享图标
				"success": function () {
					// 用户确认分享后执行的回调函数
					_self._callback(_self.options['cb_success'], res, 'success');
				},
				"cancel": function () {
					// 用户取消分享后执行的回调函数
					_self._callback(_self.options['cb_fail'], res, 'fail');
				},
				"fail": function (res) {
					_self._errhandle(res, 'onMenuShareQQ');
				}
			});
		},
		/**
		 * 分享到腾讯微博
		 */
		"_share_weibo": function () {
			var _self = this;
			wx.onMenuShareWeibo({
				"title": _self.options['title'], // 分享标题
				"desc": _self.options['desc'], // 分享描述
				"link": _self.options['link'], // 分享链接
				"imgUrl": _self.options['imgUrl'], // 分享图标
				"success": function () {
					// 用户确认分享后执行的回调函数
					_self._callback(_self.options['cb_success'], res, 'success');
				},
				"cancel": function () {
					// 用户取消分享后执行的回调函数
					_self._callback(_self.options['cb_fail'], res, 'fail');
				},
				"fail": function (res) {
					_self._errhandle(res, 'onMenuShareWeibo');
				}
			});
		},
		/**
		 * 失败错误处理
		 * @param res 微信接口返回的数据对象
		 * @param funcname 接口名
		 * @param wxjsType 返回错误的回调名 cancel|config|fail等
		 */
		"_errhandle": function (res, funcname, wxjsType) {
			if (typeof(wxjsType) == 'undefined') {
				wxjsType = 'fail';
			}
			var s = new jweixin_error();
			s.errhandle(res, funcname, wxjsType, true, false);
			return true;
		},
		/**
		 * 执行回调函数
		 * @param callback 回调函数
		 * @param res 接口返回的结果集
		 * @param type 回调的方法名：success、fail
		 * @returns {boolean}
		 */
		"_callback": function (callback, res, type) {
			if (callback && callback != 'undefined') {
				try {
					if (typeof(eval(callback)) == "function") {
						window[callback](res);
					}
				} catch (e) {
					console.log(e);
					alert('回调函数“' + type + ':' + callback + '”未定义或执行错误');
					return false;
				}
				return true;
			}
		}
	};

	return WXShare;
});

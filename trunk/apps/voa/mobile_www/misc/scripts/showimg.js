/**
 * 图片显示控件，用于展示指定区域的图片列表
 *
 * 举例：绑定容器类名为“._show_gallery”下的所有图片，两种方式：
 // 显示图片画廊
 require(["zepto", "frozen", "jweixin", "showimg"], function ($, fz, wx, showimg) {
	$('#cyoa-body').on('click', '._show_gallery img', function () {
		var s = new showimg();
		s.show($(this));
	});
});
 // 显示图片画廊
 require(["zepto"], function ($, showimg) {
	$('#cyoa-body').on('click', '._show_gallery img', function () {
		var showimg = require('showimg');
		var s = new showimg();
		s.show($(this));
	});
});
 *
 * Created by Deepseath on 2015/4/9.
 */
define(['zepto', 'underscore', 'jweixin', 'h5mod/jweixin-error', 'frozen'], function ($, _, wx, jweixin_error) {
	function SHOWIMG() {
		var _self = this;
		_self._wx_loaded = false;
		$(function () {
			// 微信接口加载完毕
			wx.ready(function () {
				_self._wx_loaded = true;
			});
			// 微信接口加载错误
			wx.error(function (res) {
				_self._errhandle(res, 'config');
			});
		});
	}
	SHOWIMG.prototype = {
		/**
		 * 显示图片
		 * @param container 要绑定的img所在容器的 id或class名对象
		 * @returns {boolean}
		 */
		"show": function (container, parent) {
			// 当前点击的对象
			var $t = $(container);
			var _self = this;
			// 判断当前使用的是哪个属性进行呈现。data-big | src
			var _src_attr = $t.attr('data-big');
			if (_src_attr == 'undefined' || _src_attr == null) {
				_src_attr = 'src';
			} else {
				_src_attr = 'data-big';
			}
			if (!parent || parent == undefined || parent == null) {
				parent = $t.parent();
			} else {
				parent = $t.closest(parent);
			}
			// 当前的地址
			var _cur_src = _self._get_real_path($t.attr(_src_attr));
			// 当前容器内的所有图片地址列表
			var list_src_obj = parent.find('img').map(function () {
				// 大图地址，检查地址格式
				var _src = $(this).attr(_src_attr);
				if (_src && _src != 'undefined' && _src.indexOf('loading.gif') < 0) {
					return _self._get_real_path(_src);
				}
			});
			if (typeof(list_src_obj.selector) == 'undefined') {
				return false;
			}
			// 显示图片列表
			this._showimg(_cur_src, list_src_obj.selector);
		},
		/**
		 * 带绑定的动作主方法
		 * @param name 要绑定的img所在容器的 id或class名，多个之间使用半角逗号“,”分隔，使用方式类似jquery或者zepto的 on()方法的绑定
		 * 如：
		 * <div id="test"><img src="" /><img src="" /></div>
		 * 如展示上面内的图片，可使用
		 * show('#test img');
		 */
		"show_bind": function (name) {
			var __self = this;
			// 对节点进行事件绑定，多个节点使用半角逗号分隔
			$('#cyoa-body').on('tap', name, function () {
				__self.show($(this));
			});
		},
		/**
		 * 显示图片
		 * @param _cur_src 当前图片地址
		 * @param list_src 图片地址列表
		 */
		"_showimg": function (_cur_src, list_src) {
			var __self = this;
			// loading
			var __loading = $.loading({
				"content": "请稍候……"
			});
			var it = setInterval(function () {
				if (__self._wx_loaded) {
					__loading.loading("hide");
					// 调用微信接口预览图片
					wx.previewImage({
						"current": _cur_src,
						"urls": list_src,
						"fail": function (res) {
							__self._errhandle(res, 'fail');
						},
						"cancel": function (res) {
							__self._errhandle(res, 'cancel');
						}
					});
					clearInterval(it);
					return false;
				}
			}, 500);
		},
		/**
		 * 获取真实url路径
		 * @param url
		 * @returns {*}
		 */
		"_get_real_path": function (url) {
			if (typeof(url) == 'undefined' || typeof(url) != 'string') {
				return '';
			}
			// 已是绝对路径，则直接返回
			if (url.match(/^http/i) !== null) {
				return url;
			}
			//获取当前网址，如： http://local.vchangyi.net/frontend/index/tlocal?aaa=ddd
			var cur_url = window.document.location.href;
			//获取主机地址之后的目录，如： /frontend/index/tlocal
			var cur_dir = window.document.location.pathname;
			var pos = cur_url.indexOf(cur_dir);
			//获取主机地址，如： http://local.vchangyi.net
			var host = cur_url.substring(0, pos);
			return host + url;
		},
		/**
		 * 失败错误处理
		 * @param res
		 * @param wxjsType
		 */
		"_errhandle": function (res, wxjsType) {
			var s = new jweixin_error();
			s.errhandle(res, 'previewImage', wxjsType, true, true);
		}
	};
	return SHOWIMG;
});

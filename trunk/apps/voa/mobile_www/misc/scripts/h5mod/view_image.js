/**
 * H5 微信接口上传图片
 * Create By Deepseath
 * $Author$
 * $Id$
 */

require(["zepto", "frozen", "jweixin"], function($, fz, wx) {

	/**
	 * 初始化微信js接口是否加载验证完毕
	 * @type {boolean}
	 * @private
	 */
	var _wx_loaded = false;

	// 事件监听
	$(function () {

		// 微信接口加载完毕
		wx.ready(function () {
			_wx_loaded = true;
		});

		// 图片上传组件所在容器
		var $view_image = $('._view_image');

		// 点击图片浏览大图
		$view_image.on('tap', '._view_preview', function () {
			// loading
			var __loading = $.loading({
				"content": "请稍候……"
			});
			// 当前点击的图片对象
			var __t = this;
			// 循环检查微信接口是否加载
			var it = setInterval(function () {
				if (_wx_loaded) {
					__loading.loading("hide");
					// 预览图片
					_view_preview(__t);
					clearInterval(it);
					return false;
				}
			}, 500);
		});
	});

	/**
	 * 图片预览
	 * @param t 点击的对象
	 * @private
	 */
	function _view_preview(t) {

		// 当前点击的图片对象
		var $cur = $(t);
		// 当前图片容器id
		var id = $cur.attr('data-id');
		// 当前点击的图片大图地址
		var cur_src = $cur.attr('data-big');
		// 当前图片上传容器内的所有图片大图列表
		var list_src_obj = $('#'+id).find('img._view_preview').map(function () {
			// 大图地址，检查地址格式
			var _src = $(this).attr('data-big');
			if (_src && _src != 'undefined' && _src.indexOf('loading.gif') < 0) {
				return _src;
			}
		});

		if (typeof(list_src_obj.selector) == 'undefined') {
			//alert('img null');
			return false;
		}

		// 调用微信接口预览图片
		wx.previewImage({
			current: cur_src,
			urls: list_src_obj.selector
		});
	}

});

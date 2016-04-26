/**
 * H5前端获取地理位置信息组件（依赖微信JS组件）
 * 本组件主要是提供给smarty插件使用的，可见：smarty cyoa_getlocation
 * 如，单独调用使用，需要另行加载微信JS config签名信息
 *
 * Created by Deepseath on 2015/5/8.
 *
 * HTML结构：
 * <div class="ui-form-item ui-border-t _location_box" data-location="location" data-location_address="location_address" id="get-location">
 * 	<span class="_location_icon"><span class="ui-icon-get-location-loading"><i class="ui-loading"></i></span></span>
 * 	<div class="ui-form-location">
 * 		<a class="ui-icon ui-icon-location-address _get_location" href="javascript:;"></a>
 * 		<input type="text" name="location_address" class="_location_address_input" value="" placeholder="正在获取位置信息，请稍候……" />
 * 	</div>
 * 	<input type="hidden" name="location" class="_location_input" value="" />
 * </div>
 *
 * 使用举例：
 * var gL = new GLocation($('#get-location'));
 * gL.get();
 */
define(["zepto", "underscore", "jweixin", "h5mod/jweixin-error"], function ($, _, wx, jweixin_error) {

	function GLocation(box) {
		// 对象指针
		var __self = this;
		// 已获取的经纬度信息缓存
		__self.location_data = {};
		// 微信接口是否已加载完毕
		__self.wx_loaded = false;
		// 初始化错误
		__self.wx_error = true;
		// 容器顶级对象
		if (typeof(box) == 'undefined') {
			box = null;
		}
		__self.box = box;
		// 历史位置信息
		__self.history = '';
		// 相关的操作对象集合
		__self.get_obj = {
			"get_button": null,// 手动触发获取地理位置的按钮对象
			"location_icon": null,// 用于存放编辑图标、显示加载进度的容器对象
			"location_edit_icon": null,// 用于触发编辑的按钮对象
			"location_address_input": null,// 地理位置信息文本输入对象
			"location_input": null// 储存经纬度的隐藏文本框对象
		};
		// 事件监听，初始化接口加载状态
		$(function () {
			// 微信接口加载完毕
			wx.ready(function () {
				__self.wx_error = false;// 无config错误
				__self.wx_loaded = true;// 微信接口加载完毕
			});
			// 微信接口加载错误
			wx.error(function (res) {
				__self.wx_error = true;// 存在config错误
				__self._show_error(__self._errhandle(res, 'config', false));
			});
		});
	}

	GLocation.prototype = {
		/**
		 * 获取地理位置信息主方法
		 */
		"get": function () {
			var _self = this;
			_self._get(false);
			// 绑定点击动作
			_self.box.on('click', '._get_location', function () {
				_self._get(true);
				event.stopPropagation();
			});
			// 绑定编辑按钮
			_self.box.on('click', '._location_edit', function () {
				_self.box.find('._location_address_input').focus();
			});
		},
		/**
		 * 获取经纬度以及地理位置信息
		 * @param reset 是否充值config加载
		 * @returns {boolean}
		 */
		"_get": function (reset) {
			// 对象指针
			var _self = this;
			// 获取相关操作对象集合
			_self.__get_obj();
			// 加载loading
			_self._loading(true);
			// 将历史数据存放在变量内
			_self.history = _self.get_obj.location_address_input.val();
			if (typeof(reset) == 'undefined') {
				reset = false;
			}
			// debug
			/*
			 _self.location_data = {
			 "longitude": '121.408',// 纬度，浮点数，范围为90 ~ -90
			 "latitude": '31.1732',// 经度，浮点数，范围为180 ~ -180。
			 "speed": 1,// 速度，以米/每秒计
			 "accuracy": 30// 位置精度
			 };
			 */
			///////////

			// 之前已获取过位置数据，不再请求
			if (!_.isEmpty(_self.location_data)) {
				_self._operation();
				return true;
			}
			// 循环检查微信接口是否加载
			var it = setInterval(function () {
				if (_self.wx_loaded) {
					if (_self.wx_error && !reset) {
						return false;
					}
					// loading 关闭
					wx.getLocation({
						"success": function (res) {
							_self.location_data = res;
							_self._operation();
						},
						"fail": function (res) {

							_self._show_error(_self._errhandle(res, 'fail', false));
						},
						"cancel": function (res) {

							_self._show_error(_self._errhandle(res, 'cancel', false));
						}
					});
					// 退出循环
					clearInterval(it);
					return false;
				}
			}, 501);
		},
		"bind_get": function (box, reset) {
			var __c_self = this;
			if (typeof(reset) == 'undefined') {
				reset = true;
			}
			if (reset) {
				__c_self.wx_loaded = false;
			}
			__c_self.get(reset);
		},
		/**
		 * 通过经纬度获取地理位置信息
		 */
		"_operation": function () {
			var _self = this;
			// 赋值经纬度
			_self.get_obj.location_input.val(_self.location_data.longitude + ',' + _self.location_data.latitude);
			$.ajax({
				"type": "GET",
				"url": "/api/common/get/address",
				"data": {
					"longitude": _self.location_data.longitude,
					"latitude": _self.location_data.latitude
				},
				"dataType": "json",
				"success": function (res) {
					if (typeof(res['errcode']) == 'undefined') {
						_self._show_error('获取地理位置信息发生网络错误，请重试');
						return false;
					}
					if (res['errcode'] != 0) {
						_self._show_error(res['errmsg'] + '(Err: ' + res['errcode'] + ')');
						return false;
					}
					// 终止loading
					_self._loading(false, '');
					// 输出结果
					_self.get_obj.location_address_input.val(res['result']['address']);
				}
			});
		},
		/**
		 * 获取相关操作按钮、容器对象集合
		 * @returns {{get_button: object, location_icon: object, location_input: object}}
		 */
		"__get_obj": function () {
			var _box = this.box;
			return this.get_obj = {
				"get_button": _box.find('._get_location'),
				"location_icon": _box.find('._location_icon'),
				"location_edit_icon": _box.find('._location_edit'),
				"location_address_input": _box.find('._location_address_input'),
				"location_input": _box.find('._location_input')
			};
		},
		/**
		 * 显示loading
		 * @param is_loading 是否加载loading
		 * @param msg 提示文字
		 */
		"_loading": function (is_loading, msg) {
			if (typeof(msg) == 'undefined') {
				msg = '';
			}
			var _self = this;
			var _boxc = _self.__get_obj();
			if (is_loading) {
				_boxc.location_icon.html('<span class="ui-icon-get-location-loading"><i class="ui-loading"></i></span>');
				_boxc.location_address_input.attr('placeholder', '正在获取位置信息，请稍候……');
			} else {
				_boxc.location_icon.html('<i class="ui-icon ui-icon-location-edit _location_edit"></i>');
				_boxc.location_address_input.attr('placeholder', msg);
			}
		},
		/**
		 * 显示错误提示
		 * @param msg 错误文字
		 * @param hide_loading 是否隐藏loading
		 */
		"_show_error": function (msg, hide_loading) {
			var _self = this;
			if (typeof(hide_loading) == 'undefined') {
				hide_loading = true;
			}
			if (hide_loading) {
				_self._loading(false, '');
			}
			$.dialog({
				"content": "<div style=\"color: #999;padding-bottom: 6px\">" + msg + "</div><span style=\"color: blue;\">你可以手动填写地址信息</span>",
				"button": ["关闭"]
			});
			_self.get_obj.location_address_input.val(_self.history).focus();
		},
		/**
		 * 获取经纬度所在的地理位置信息
		 * @returns {string}
		 */
		"_get_address": function () {
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
			return '';
		},
		/**
		 * 失败错误处理
		 * @param res 微信错误输出对象
		 * @param wxjsType 接口名
		 * @param show_tip 是否显示弹出错误提示框
		 * @returns {*}
		 */
		"_errhandle": function (res, wxjsType, show_tip) {
			var s = new jweixin_error();
			return s.errhandle(res, 'getLocation', wxjsType, true, show_tip);
		}
	};

	return GLocation;
});

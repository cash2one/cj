{include file='mobile/header.tpl' navtitle='外出考勤' css_file = "app_sign.css"}
<div class="s_title s_font s_margin">
	上报地理位置
</div>
<div class="s_box">
	<div class="o_location">
		<!--   <a  class ="ui-icon ui-icon-location-address _get_location"  id="o_location" style="color:red;"></a> -->
	</div>
	<div style="width:70px;height:80px;margin:0 auto;margin-top:10px;">
		<img src="/misc/images/msq_ways_1.png" alt="">
	</div>
	<form method ="post" action="/frontend/sign/uplocation" id="outform">
		<div class="s_add">

			<div class="ui-form-item _location_box" data-location="location" data-location_address="address" id="get-location">
				<span class="_location_icon"><span class="ui-icon-get-location-loading"><i class="ui-loading"></i></span></span>
				<div class="ui-form-location">

					<span for="" id="add_val">正在获取位置信息，请稍候……</span>
					<input type="hidden" name="address" class="_location_address_input" value=""  />
				</div>
				<input type="hidden" name="location" class="_location_input" value="" />
			</div>

		</div>
		<div class="s_st">
			{$uptime}
		</div>
		<div  class="s_im">

			<div class="limit">
				{cyoa_upload_image title='上传图片'  div_class='ui-form-item ui-form-item-show upload _uploader_box' attr_id='upload_image' name='atids' attachs=$data['image'] progress=1  max= 5}

			</div>

		</div>
</div>
<div  class="s_s_btn">
	<button type="submit"  class="s_btn" id="btn_create">确定</button>

</div>
</form>


<script type="text/javascript">

	function wxjsapi_config(owx) {
		if (typeof(owx) != 'undefined') {
			var wx = owx;
		}
		{cyoa_jsapi list=['getLocation'] debug=0}

	}
	if (typeof(wx) == 'undefined' || !window.wx) {
		require(["jweixin"], function (wx) {
			wxjsapi_config(wx);
		});
	} else {
		wxjsapi_config(wx);
	}


</script>



{literal}
<script type="text/javascript">
require(["jweixin"], function (wx) {
	require(["zepto", "underscore", "frozen"], function ($, _, fz) {
		$('#outform').submit(function(){

			if($('._location_box input').val() == ''){
				$.tips({content:'正在获取地理位置，请稍后...'});
				return false;
			}else{
				return true;
			}
		})
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
				"add_val":null,
				"location_icon": null,// 用于存放编辑图标、显示加载进度的容器对象
				// "location_edit_icon": null,// 用于触发编辑的按钮对象
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

			},

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
						_self.get_obj.add_val.html(res['result']['address']);

					}
				});
			},

			"__get_obj": function () {
				var _box = this.box;
				return this.get_obj = {

					"location_icon": _box.find('._location_icon'),
					//   "location_edit_icon": _box.find('._location_edit'),
					"add_val":_box.find('#add_val'),
					"location_address_input": _box.find('._location_address_input'),
					"location_input": _box.find('._location_input')
				};
			},

			"_loading": function (is_loading, msg) {
				if (typeof(msg) == 'undefined') {
					msg = '';
				}
				var _self = this;
				var _boxc = _self.__get_obj();
				if (is_loading) {
					_boxc.location_icon.html('<span class="ui-icon-get-location-loading"><i class="ui-loading"></i></span>');
					// _boxc.location_address_input.attr('placeholder', '正在获取位置信息，请稍候……');
				} else {
					// _boxc.location_icon.html('<i class="ui-icon ui-icon-location-edit _location_edit"></i>');
					_boxc.location_icon.html('');
					_boxc.location_address_input.attr('placeholder', msg);
				}
			},

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

			"_errhandle": function (res, wxjsType, show_tip) {
				var s = new jweixin_error();
				// return s.errhandle(res, 'getLocation', wxjsType, true, show_tip);
			}
		};
		var gl = new GLocation($('#get-location'));
		gl.get();

	});
});
</script>
{/literal}

</section>
<section class="section_container" style="display:none">
	<div id="addrbook" class="ui-tab"></div>
</section>

{include file='mobile/footer.tpl'}
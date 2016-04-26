/**
 * 【签到】
 * Created by Deepseath on 2015/3/23.
 */

define(["zepto", "underscore"], function ($, _) {

	/**
	 * 构造
	 */
	function SIGN() {

	}

	SIGN.prototype = {

		/**
		 * 发送 签到/签退 请求
		 * @param r 地理位置信息数据
		 */
		"sign": function (r) {
	
			var __loading = $.loading({
				"content": "请稍候……"
			});

			// 签到 or 签退
		/*	var workon = 2;
			if ($('._sign').find('._sign_on').length > 0) {
				workon = 1;// 签到
			}
*/		
			var workon = $('#sign_type').val();
			
			//var _self = this;
			/*
			// 签退时判断是否下班再在此处判断
			// 签退
			if (workon == 2) {
				// 签退容器对象
				var $workoff = $('#sign-off');
				if ($workoff.data('current') < $workoff.data('workoff')) {
					var dia = $.dialog({
						"content": "当前未到下班时间是否确定“签退”？",
						"button": ["取消", "确认"]
					});
					dia.on("dialog:action", function (e) {
						if (e.index == 0) {
							// 点击“取消”
							__loading.loading('hide');
							return;
						}
						_self._sign_ajax(r, workon, __loading);
					});
					return;
				}
			}
			*/

			this._sign_ajax(r, workon, __loading);
		},

		/**
		 * 上报地理位置
		 * @param r 地理位置信息数据
		 * @returns {boolean}
		 */
		"sign_location": function(r) {

			var __loading = $.loading({
				"content": "请稍候……"
			});
			var data;
			if (typeof(r) == 'undefined' || !r || r === null || typeof(r.latitude) == 'undefined') {
				data = {
					"latitude": '',
					"longitude": '',
					"precision": ''
				};
			} else {
				data = {
					"latitude": r.latitude,
					"longitude": r.longitude,
					"precision": r.accuracy
				}
			}
			$.ajax({
				"type": "POST",
				"url": "/api/sign/post/location",
				"data": data,
				"dataType": "json",
				"beforeSend": function () {
				},
				"success": function (data) {
					if (typeof(data['errcode']) == 'undefined') {
						$.dialog({
							"content": "提交数据发生错误，请重试",
							"button": ["关闭"]
						});
						return false;
					}
					if (data['errcode'] != 0) {
						$.dialog({
							"content": data['errmsg'],
							"button": ["关闭"]
						});
						return false;
					}

					__loading.loading('hide');

					// 请求的结果
					var result = data['result'];

					// 最后一次记录容器
					var $log_first_box = $('#location-log-first');
					// 记录列表容器
					var $log_list_box = $('#location-log-list');

					// 上一次上报记录，移除样式“ui-form-item-link”
					var history_log = $log_first_box.html().replace('ui-form-item-link', '');
					// 本次记录
					var current_log = $.tpl($('#sign-location-log-tpl').html(), {
						"time": result['time'],
						"address": result['address']
					});
					// 把当前记录写到最后一次记录容器内
					$log_first_box.html(current_log);
					// 把上一次记录移动到列表容器内
					if (history_log) {
						$log_list_box.prepend(history_log);
					}

					$('#location-log').show();
				},
				"complete": function () {
					__loading.loading('hide');
				}
			});

			return true;
		},

		/**
		 * 新增 或者 修改 备注
		 * @returns {boolean}
		 */
		"sign_reason": function () {

			// 触发备注修改的按钮对象
			var $reason_btn = $('.det_sr');
			var sr_id = $reason_btn.attr('data-srid');
			if (!sr_id || sr_id == 'undefined') {
				$.dialog({
					"content": '请先签到后再添加备注信息',
					"button": ["关闭"]
				});
				return false;
			}

			var dia=$.dialog({
				title:'签到备注',
				content:$.tpl($('#sign-reason-tpl').html(), {"content": $('#sign-reason-txt').text()}),
				button:["取消","保存"]
			});
			
			var self = this;
			dia.on("dialog:action",function(e){
				if (e.index == 0) {
					// 取消
					return false;
				}

				// 显示
				var content_source = $('#sign-reason-content').val();
				var content = $.trim(content_source);
				if(content_source != ''){
					$('#detail_li').append('<li style="border-bottom:1px dashed #ccc;line-height:30px;">备注:'+ content_source+'</li>');					
				}
					$('#sign-reason-txt').html('');
					$('button._sign_reason').text('添加备注');
					$('#sign-reason').hide();

				// 提交忽略返回
				$.ajax({
					"type": "POST",
					"url": "/api/sign/post/reason",
					"data": {
						"reason": content_source,
						"id": sr_id
					},
					"dataType": "json",
					"success": function (r) {
						if (typeof(r['errcode']) == 'undefined') {
							$.dialog({
								"content": '更新备注信息发生网络错误，请重试',
								"button": ["关闭"]
							});
							return false;
						}
						if (r['errcode'] != 0) {
							$.dialog({
								"content": r['errmsg'],
								"button": ["关闭"]
							});
							return false;
						}
					}
				});
			});
		},

		/**
		 * 签到签退请求
		 * @param r 地理位置信息数据
		 * @param workon 1=上班 or 0=下班
		 * @param __loading
		 */
		"_sign_ajax": function(r, workon, __loading) {

			var sbid = $('#sbid').val();
			var data;
			if (typeof(r) == 'undefined' || !r || r === null || typeof(r.latitude) == 'undefined') {
				data = {
					"latitude": '',
					"longitude": '',
					"precision": '',
					"sbid":sbid,
					"type": workon
				};
			} else {
				data = {
					"latitude": r.latitude,
					"longitude": r.longitude,
					"precision": r.accuracy,
					"sbid":sbid,
					"type": workon
				}
			}

			$.ajax({
				"type": "POST",
				"url": "/api/sign/post/sign",
				"data": data,
				"dataType": "json",
				"beforeSend": function() {
					// do nothing.
				},
				"success": function (data) {
					if (typeof(data['errcode']) == 'undefined') {
						$.dialog({
							"content": "提交数据发生错误，请重试",
							"button": ["关闭"]
						});
						return false;
					}
					if (data['errcode'] != 0 && data['errcode'] != 1001011 ) {
						$.dialog({
							"content": data['errmsg'] + '(Err: ' + data['errcode'] + ')',
							"button": ["关闭"]
						});
						return false;
					}else if(data['errcode'] == 1001011){
						$.dialog({
							"content": data['errmsg'] + '(Err: ' + data['errcode'] + ')',
							"button": ["关闭"]
						});
					}
		
					// 请求的结果
					var result = data['result'];

	/*				var html = _.template($('#sign-tpl').html(), {
						"time": result['time'],
						"address": result['address']
					});*/
					var s_address = result['address'];
					var s_time = result['time'];
					var sion_id = result['id'];
	
			
					// 签到原因注入当前签到ID
				//	var $reason_btn = $('#sign-reason-btn');
				//	$reason_btn.attr('data-srid', result['id']);
					//获取当前时间
					/*var mydate = new Date();
					var h = mydate.getHours(); 
					var m = mydate.getMinutes(); 
					var t = h+':'+m;*/
					
					var $sign_off = $('#sign-off');
					var sb_set = $('#sb_set').val();
					var si_on = $('#si_on').val();
					var detail_sr_id = $('#detail_sr_id').val();
					var off_signtime_hi = s_time;
					if (workon == 1) {
						var work_begin_hi = $('#work_begin_hi').val();
						var work_end_hi = $('#work_end_hi').val();
					//	var $sign_on = $('#sign-on');
						//$sign_on.html(html).css('display', 'block');
					//	$sign_off.html('<button class="ui-btn ui-btn-primary _sign_btn _sign_off">签退</button>').css('display', 'block');
						if(sb_set == 3){
							$('#common_on').html('<div style="width:280px;margin:0 auto;margin-top:40px;" class="det_sr" data-srid = '+sion_id+'><div style="width:100px;height:24px;float:left;"><b>上班</b> ('+work_begin_hi+')</div><div style="width:70px;height:24px;float:right;color:#4A4A48;font-size:14px;" class="_sign_reason">添加备注</div></div><div style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;clear:both;"><div style="width:150px;height:30px;margin:0 auto;font-size:20px;margin-top:50px;text-align:center;color:#FFFFFF;">'+s_time+'</div><div style="width:230px;height:35px;margin:0 auto;color:#FFFFFF;text-align:center;">'+s_address+'</div></div>');
							$('#show_on_t').css('display','none');
							$('#show_on_c').css('display','none');
							$('#off_show').css('display','block');
							$('#o_show').css('display','block');
							$('#commontpl').html('<div id="sign-but" style="width:280px;margin:0 auto;margin-top:40px;"><b>下班</b> ('+work_end_hi+')</div><div id="sign-b-off" style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;"><div  class="i_s_hi">'+off_signtime_hi+'</div><div class="i_s_bt"><button   class="_sign_btn _sign_off">签退</button></div></div>');
						}else if(sb_set == 1){
							$('#show_on_t').css('display','none');
							$('#show_on_c').css('display','none');
							$('#common_on').html('<div style="width:280px;margin:0 auto;margin-top:40px;" class="det_sr" data-srid = '+sion_id+'><div style="width:100px;height:24px;float:left;"><b>上班</b> ('+work_begin_hi+')</div><div style="width:70px;height:24px;float:right;color:#4A4A48;font-size:14px;" class="_sign_reason">添加备注</div></div><div style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;clear:both;"><div style="width:150px;height:30px;margin:0 auto;font-size:20px;margin-top:50px;text-align:center;color:#FFFFFF;">'+s_time+'</div><div style="width:230px;height:35px;margin:0 auto;color:#FFFFFF;text-align:center;">'+s_address+'</div></div>');
							
						}
					}else if(workon == 2){
						var work_end_hi = $('#work_end_hi').val();
							$('#sign-but').css('display','none');
							$('#sign-b-off').css('display','none');
							if(sb_set == 2){
								$('#commontpl').html('<div style="width:280px;margin:0 auto;margin-top:40px;" class="det_sr" data-srid = '+sion_id+' id="off_show" ><div style="width:100px;height:24px;float:left;"><b>下班</b> ('+work_end_hi+')</div><div style="width:70px;height:24px;float:right;color:#4A4A48;font-size:14px;" class="_sign_reason">添加备注</div></div><div style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;clear:both;" id="o_show"><div style="width:150px;height:30px;margin:0 auto;font-size:20px;margin-top:50px;text-align:center;color:#FFFFFF;">'+s_time+'</div><div style="width:230px;height:35px;margin:0 auto;color:#FFFFFF;text-align:center;">'+s_address+'</div></div>');																					
							}else if(sb_set == 3){
								$('#commontpl').html('<div style="width:280px;margin:0 auto;margin-top:40px;" class="det_sr" data-srid = '+sion_id+' id="off_show" ><div style="width:100px;height:24px;float:left;"><b>下班</b> ('+work_end_hi+')</div></div><div style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;clear:both;" id="o_show"><div style="width:150px;height:30px;margin:0 auto;font-size:20px;margin-top:50px;text-align:center;color:#FFFFFF;">'+s_time+'</div><div style="width:230px;height:35px;margin:0 auto;color:#FFFFFF;text-align:center;">'+s_address+'</div></div>');																				
							}
							//把备注绑定的id换成下班
							$('.det_sr').each(function(){
								$(this).attr('data-srid', sion_id);
							})
					}
					//更改打卡类型
					if(sb_set == 3 && workon == 1){
						$('#sign_type').val('2');
					}
					//$reason_btn.show();
				},
				"complete": function () {
					__loading.loading('hide');
				}
			});
		},

		/**
		 * 转义HTML字符串
		 * @param string
		 * @returns {XML}
		 * @private
		 */
		"_htmlspecialchars": function (string) {
			string = string.toString();
			string = string.replace(/&/g, '&amp;');
			string = string.replace(/"/g, '&quot;');
			string = string.replace(/'/g, "'");
			string = string.replace(/</g, '&lt;');
			string = string.replace(/>/g, '&gt;');
			return string;
		}
	};

	return SIGN;
});


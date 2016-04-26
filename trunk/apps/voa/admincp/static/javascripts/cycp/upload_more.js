/**
 * 畅移后台文件上传组件
 * Created by Deepseath on 2015/3/15.
 */
jQuery(function () {
	// 点击删除按钮
	jQuery('body').on('click', '.cycp_uploader_delete', function () {

		if (!confirm('是否确认删除该图片？')) {
			return false;
		}

		// 所在上传容器对象
		var $box = jQuery(this).parents('.uploader_box');

		// 附件Id所在的隐藏文本框对象
		var $input = $box.find('input._input');

		// 请求删除
		jQuery.ajax({
			"url": "/admincp/api/attachment/delete",
			"type": "POST",
			"dataType": "json",
			"data": {"id": $input.val()},
			"complete": function () {
				// 附件ID置空
				$input.val('');
				// 删除按钮隐藏
				$box.find('._showdelete').hide();
				// 图片清除
				$box.find('._showimage').html('');
			}
		});

	});

	jQuery('body').on('click', '.fileinput-button', function () {
		var initImg = jQuery(this).find('.cycp_uploader').data('initLoad');
		jQuery('.cycp_uploader').fileupload({
			"dataType": 'json',
			"add" : function(e,d){
				if(initImg.length < parseInt(jQuery(this).attr('data-limit-num'),10)){
					d.submit();
					initImg.push(d);
				}else{
					alert("超出最多上传图片张数");
				}
			},
			"fail" : function(e,d){
				for(var i=0; i<initImg.length; i++){
					if(initImg[i].files){
						if(initImg[i].files[0]===d.files[0]){
							initImg.splice(i,1);
							break;
						}
					}
				}
			},
			"done": function (e, data) {
				if (typeof(data.result) == 'undefined') {
					alert('上传文件发生系统错误，请返回重试。错误码：-1001');
					return false;
				}
				if (typeof(data.result.result) == 'undefined') {
					alert('上传文件发生系统错误，请返回重试。错误码：-1002');
					return false;
				}

				// 结果集
				var r = data.result;

				if (typeof(r.result) == 'undefined') {
					alert('上传文件发生系统错误，请返回重试。错误码：-1003');
					return false;
				}

				if (r.errcode != 0) {
					alert('上传发生错误：' + r['errmsg'] + '[错误码：' + r['errcode'] + ']');
					return false;
				}

				// 数据结果集
				var result = r['result'];

				// 当前的上传对象
				var $t = jQuery(this);
				// 整个控件所在容器对象
				var $box = jQuery(this).parents('.uploader_box');
				// 储存附件ID的文本框对象
				var $input = $box.find('input._input');
				// 如果存在图片ID，则删除旧的
				/*var old_at_id = $input.val();
				if (old_at_id) {
					jQuery.ajax({
						"url": "/admincp/api/attachment/delete",
						"type": "POST",
						"dataType": "json",
						"data": {"id": old_at_id}
					});
				}*/

				// 储存at_id的隐藏文本框对象，将当前上传的图片附件公共ID写入
				$input.val(result['id']);

				// 完全回调函数名
				var callbackall = $t.attr('data-callbackall');

				// 回调函数
				var callback = $t.attr('data-callback');

				// 完全回调方式
				if (callbackall && callbackall != 'undefined') {
					try {
						if (typeof(eval(callbackall)) == "function") {
							window[callbackall](result, $t);
						}
					} catch (e) {
						alert('单文件上传组件的完全回调函数“' + callbackall + '”未定义或执行错误');
						return false;
					}

					return true;
				}

				// 判断是否显示删除按钮
				if ($t.attr('data-hidedelete') == 1) {
					$box.find('._showdelete').hide();
				} else {
					$box.find('._showdelete').show();
				}
				
				
				// 启用自定义回调函数
				if (callback && callback != 'undefined') {
					try {
						if (typeof(eval(callback)) == "function") {
							window[callback](result, $t,data);
						}
					} catch (e) {
						alert('单文件上传组件的回调函数“' + callback + '”未定义或执行错误');
						return false;
					}
				}

				return true;
			}
		});
	});

});

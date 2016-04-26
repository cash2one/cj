/**
 * 后台多图上传组件
 * Created by Deepseath on 2015/3/22.
 */

/**
 * 改变附件ID文本框的值
 * @param $input 文本框对象
 * @param method +=增加，-=减少
 * @param id 增加的ID
 * @private
 */
function __change_multi_id($input, method, id) {

	// 原有的 at_ids 字符串
	var at_ids = ',' + $input.val() + ',';
	//return;
	if (method == '+') {
		// 新增一个id
		at_ids += ',' + id + ',';
	} else {
		// 移除一个id
		at_ids = at_ids.replace(',' + id + ',', '');
	}
	// 清理多余的“,”并整理数据
	at_ids = at_ids.replace(/\s+/, '');
	at_ids = at_ids.replace(/,{2,}/, ',');
	at_ids = at_ids.replace(/^,/, '');
	at_ids = at_ids.replace(/,$/, '');

	// 赋值
	$input.val(at_ids);

	return;
}

// 用于显示上传的图片的模板
var __showimage_tpl = '<div class="col-sm-1 _delete_image" style="min-width:64px"><div class="uploader-image-show _uploader_image_show">'
	+ '<a href="<%=bigurl%>" target="_blank" style="height:<%=max%>px">'
	+ '<img src="<%=thumburl%>" border="0" style="max-width:<%=max%>px; max-height:<%=max%>px" />'
	+ '</a>'
	+ '<button type="button" class="btn btn-danger btn-sm _uploader_delete" data-id="<%=id%>">删除</button>'
	+ '</div></div>';

jQuery(function () {

	// 删除
	jQuery('body').on('click', '._uploader_delete', function () {

		if (!confirm('是否确认删除该图片？')) {
			return;
		}

		// 当前点击的按钮对象
		$delbtn = jQuery(this);
		// 当前上传容器
		$box = $delbtn.parents('.uploader_multi_box');
		// 当前图片所在容器
		$imgbox = $delbtn.parents('._delete_image');

		// 移除图片以及容器
		$imgbox.remove();
		// 改变附件值
		__change_multi_id($box.find('input[type=hidden]'), '-', $delbtn.attr('data-id'));

		// 请求删除
		jQuery.ajax({
			"url": "/admincp/api/attachment/delete",
			"type": "POST",
			"dataType": "json",
			"data": {"id": $delbtn.attr('data-id')},
			"complete": function () {
			}
		});
	});

	// 上传
	jQuery('._cycp_uploader_multi').fileupload({
		"dataType": "json",
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
			var $box = $t.parents('.uploader_multi_box');
			// 储存附件ID的文本框对象
			var $input = $box.find('input[type=hidden]');
			// 显示图片的容器
			var $showimage = $box.find('._showimage');

			// 图片信息
			var img = result['list'][0];

			// 改变附件ID
			__change_multi_id($input, '+', result['id']);

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
					alert('多图片上传组件的完全回调函数“' + callbackall + '”未定义或执行错误');
					return false;
				}

				return true;
			}

			// 显示图片
			$showimage.append(txTpl(__showimage_tpl, {
				"bigurl": img['url'],
				"thumburl": img['thumbnailUrl'],
				"id": result['id'],
				"max": $t.attr('data-thumbsize')
			}));

			// 启用自定义回调函数
			if (callback && callback != 'undefined') {
				try {
					if (typeof(eval(callback)) == "function") {
						window[callback](result, $t);
					}
				} catch (e) {
					alert('多图片上传组件的回调函数“' + callback + '”未定义或执行错误');
					return false;
				}
			}

		}
	});

});

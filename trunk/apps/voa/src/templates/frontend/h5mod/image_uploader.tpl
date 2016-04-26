{*
	利用微信jsapi接口的照片上传公共模板
	$attachs array 需要做展示的图片列表，array(array(aid, url), array(aid, url), ... ...)
		默认：array()
	$iptname 本地附件的aid存放的文本框名
		默认：at_ids
	$iptvalue 本地附件的aid，多个之间半角逗号“,”分隔
		默认：空
	$image_uploader_id string 用于显示上传图片预览的容器ID
		默认：image_uploader_id
	$count_min int 设置最少需要上传的图片数，0=不限制，允许不传图片（暂时无效）
		默认：0
	$count_max int 设置最多允许上传的图片数，最多只允许上传20个。（暂时无效）
		默认：6
	$show_weixin_progress int 是否显示微信上传进度提示框，1=显示，2=不显示
		默认：1
	$thumbsize 缩略图尺寸
		默认：45
	$bigsize 预览时的大图尺寸
		默认：640
	
	样式说明：
		.h5mod-iu 上传区域外部的容器类
			.h5mod-iu-wrapper 上传区域外部的边缘类
				.h5mod-iu-show 用于显示图片预览的类
					.h5mod-iu-preview 图片预览的外部点击标签类
					.h5mod-iu-remove 删除图片的标签类
					.h5mod-iu-loading 上传等待图标类（需要将loading图标做背景图）
				.h5mod-iu-btn 用于触发选择图片的按钮的类
					
		如果某些应用需要特殊的呈现，可以针对该应用使用容器ID来覆盖
*}

{$system_count_max = 9}
{if empty($attachs)}
	{$attachs = array()}
{/if}
{if empty($iptname)}
	{$iptname = 'aids'}
{/if}
{if empty($iptvalue)}
	{$iptvalue = ''}
{/if}
{if empty($image_uploader_id)}
	{$image_uploader_id = 'image_uploader_id'}
{/if}
{if empty($count_min)}
	{$count_min = 0}
{/if}
{if empty($count_max) || $count_max > $system_count_max}
	{$count_max = 4}
{/if}
{if empty($show_weixin_progress) || $show_weixin_progress != 2}
	{$show_weixin_progress = 1}
{else}
	{$show_weixin_progress = 0}
{/if}
{if empty($thumbsize)}
	{$thumbsize = 45}
{/if}
{if empty($bigsize)}
	{$bigsize = 640}
{/if}

{* 只有首次才加载js *}
{if empty($__IMAGE_UPLOADER__)}
	{assign var=__IMAGE_UPLOADER__ value="1" scope="root"}

<script type="text/javascript">
require(['dialog'], function(AddrbookComponent){
	MLoading.show('稍等片刻...');
});
if (typeof(jQuery) == 'undefined' || !window.jQuery) {
	document.write("<script type=\"text/javascript\" src=\"{$wbs_javascript_path}jquery-1.9.1.min.js\"><\/script>");
}
var _h5mod_image_num = 0;
</script>
{literal}
<script type="text/javascript">
var local_ids = [];
function upload(t) {
	// 触发选择图片的按钮对象
	var jq_btn = jQuery(t);
	// 上传图片容器对象
	var jq_uploader = jq_btn.parents('._h5mod_uploader_box');
	var img_total = jQuery('._h5mod_image_remove', jq_uploader).length;
	if (5 <= img_total) {
		return;
	}
	
	// 显示图片的容器对象
	var jq_label = jq_uploader.find('._h5mod_uploader_show');
	// 储存at_id的文本框对象
	var jq_atid = jQuery('#' + jq_uploader.attr('data-inputname'));
	// 缩略图宽度
	var thumbsize = jq_uploader.attr('data-thumbsize');
	// 大图宽度
	var bigsize = jq_uploader.attr('data-bigsize');
	// 是否显示微信进度
	var show_weixin_progress = jq_uploader.attr('data-progress');
	// 当前进程的数据ID（具有唯一性）
	var c_id = 'local_' + _h5mod_image_num;
	
	var local_id = local_ids.pop();
	
	_h5mod_image_num++;
	// 展现“加载状态”
	jq_label.append('<a href="javascript:;" class="photo" data-id="' + c_id + '"><img class="_h5mod_uploader_preview" src="/admincp/static/images/loading.gif" alt="" style="background:#fff" /><i class="_h5mod_image_remove rm" data-id="' + c_id + '" onclick="javascript:image_delete_handler(this);">-</i></a>');
	// 当前正在上传的对象
	var jq_a = jq_uploader.find('a[data-id="'+c_id+'"]');
	// 当前正在上传的图片对象
	var jq_img = jq_a.find('img');
	// 上传图片
	wx.uploadImage({
		"localId": local_id,
		"isShowProgressTips": show_weixin_progress,
		"success": function (res) {
			// 返回的微信媒体文件server_id（media_id）
			var server_id = res.serverId;
			// 待发送给本地上传接口的数据
			var data = {
				"serverid": server_id,
				"thumbsize": thumbsize,
				"bigsize": bigsize
			};
			// 发送serverid到服务器转换为服务器本地附件信息获取附件ID
			jQuery.ajax('/api/attachment/get/aid', {
				"dataType": "json",
				"type": "GET",
				"data": data,
				"success": function (r, textStatus, jqXHR) {
					// 读取结果发生错误
					if (typeof(r.errcode) == 'undefined') {
						MDialog.notice('上传图片到服务器发生未知错误');
						return false;
					}
					// 上传发送错误
					if (r.errcode != 0) {
						MDialog.notice(r.errmsg+'[Err: '+r.errcode+']');
						return false;
					}
					// 接口返回的数据
					var data = r.result;
					// 显示图片小图
					jq_img.attr('src', data['thumb'] ? data['thumb'] : data['url']);
					// 定义预览图片动作
					jq_img.attr('onclick', 'javascript:image_preview(this)');
					// 赋值大图路径
					jq_img.attr('data-big', data['big'] ? data['big'] : data['url']);
					// 定义图片外层数据ID
					jq_a.attr('data-id', data['id']);
					// 赋值删除按钮的数据ID
					jq_a.find('._h5mod_image_remove').attr('data-id', data['id']);
					// 将aid写入到表单内
					image_at_ids_change(jq_atid, data['id'], '+');
				},
				"complete": function () {
					// 上传失败则清理掉与本次相关的数据
					image_remove(jq_img);
				}
			});
		},
		"fail": function (res) {
			MDialog.notice('上传图片发生错误："'+res.errMsg+'"');
			//console.log(res);
			image_remove(jq_img);
		},
		"complete": function (res) {
			var err_msg = res.errMsg;
			// 如果上传不成功，则清理已传入的数据
			if (err_msg.match(/:\s*ok$/i) == null) {
				image_remove(jq_img);
			}
			
			if (0 < local_ids.length) {
				upload(t);
			}
		},
		"cancel": function (res) {
			image_remove(jq_img);
		}
	});
}
/**
 * 选择图片并上传
 * @param t object 触发选择图片的控件对象
 * @return void
 */
function image_uploader(t) {
	// 选择图片
	wx.chooseImage({
		"success": function (res) {
			
			/**
			* 遍历选择的图片文件 ID ，然后通过接口一一上传到微信，获取到 serverid
			* 利用本地接口通过 serverid 读取微信文件并下载回本地服务器，写入附件表，返回附件信息以及附件ID
			* 显示图片缩略图（给出预览链接）并将附件ID写入到预设的隐藏文本框内 ……
			*/
			// 遍历已选择的图片id
			local_ids = res.localIds;
			upload(t);
		},
		"fail": function (res) {
			MDialog.notice('选择图片发生错误："'+res.errMsg+'"');
			//console.log(res);
		},
		"complete": function (res) {
		},
		"cancel": function (res) {
		}
	});
}

/**
 * 移除指定的图片
 * @param object t 触发删除图片的控件对象
 * @return void
 */
function image_remove(t) {
	
	// 待删除的附件ID
	var at_id = 0;
	// 当前图片的jquery对象
	var jq_img = jQuery(t);
	// 当前图片的ID字符串
	var at_id_str = jq_img.attr('data-id');
	// 如果是整型数字，则认为是附件ID
	if (at_id_str.match(/^\d+$/) != null) {
		at_id = at_id_str;
		var input_id = jq_img.parents('._h5mod_uploader_box').attr('data-inputname');
		var jq_input = jQuery('#' + input_id);
		image_at_ids_change(jq_input, at_id , '-');
	}

	// 如果是附件ID，则请求api删除附件数据，由于不需要知道返回结果，因此直接请求而不输出返回结果
	if (at_id > 0) {
		jQuery.ajax({
			"url": '/api/attachment/delete/delete',
			"type": 'DELETE',
			"dataType": 'json',
			"data": {
				"ids": at_id
			},
			"success": function (result) {
				//alert(typeof(result.errcode) + "\n" + result.errcode + "\n" + result.result);
			},
			"error": function (XMLHttpRequest, textStatus, errorThrown) {
				//alert(textStatus);
			}
		});
	}

	// 移除图片
	jQuery(t).parents('a').remove();
}

/**
 * 手动执行删除图片动作
 * @param object t 图片对象
 * @return void
 */
function image_delete_handler(t) {
	MDialog.confirm(
		'删除确认',
		'您确定删除该图片吗？',
		null,
		'取消', null, null,
		'确定', function () {
			image_remove(t);
		}
	);
}

/**
 * 图片预览
 * @param object t 触发图片预览的图片对象
 * return void
 */
function image_preview(t) {

	// 当前点击的图片对象
	var jq_current = jQuery(t);
	// 当前图片所在的容器对象
	var jq_show = jq_current.parents('._h5mod_uploader_show');
	// 当前容器内所有图片地址的字符串组合（以comma“\n”分隔）
	var imglist_str = '';
	var comma = "";
	// 遍历容器内所有图片，并组合图片地址，图片地址之间使用“\n”分隔
	jQuery.each(jq_show.find('img'), function (i, o) {
		src = jQuery(o).attr('data-big');
		if (src != '') {
			imglist_str += comma + src;
			comma = "\n";
		}
	});
	// 图片预览
	wx.previewImage({
		"current": jq_current.attr('data-big'),
		"urls": imglist_str.split("\n")
	});
}

/**
 * 改变附件ID数据
 * @param object input 储存at_id的文本框对象
 * @param int at_id 附件ID
 * @param string method 改变
 */
function image_at_ids_change(jq_input, at_id, method) {
	
	// 原有的 at_ids 字符串
	var at_ids = jq_input.val();
	at_ids = ',' + at_ids + ',';
	if (method == '+') {
		// 新增一个id
		at_ids += ',' + at_id + ',';
	} else {
		// 移除一个id
		at_ids = at_ids.replace(','+at_id+',', '');
	}
	// 清理多余的“,”并整理数据
	at_ids = at_ids.replace(/\s+/, '');
	at_ids = at_ids.replace(/,{2, }/, ',');
	at_ids = at_ids.replace(/^,/, '');
	at_ids = at_ids.replace(/,$/, '');
	//MDialog.notice(at_ids);
	jq_input.val(at_ids);
	//alert(at_ids);
}

/**
 * 异步初始化监听动作，以期在微信验证并加载完jsapi需要的config
 */
wx.ready(function () {

	jQuery(function () {

		// 删除图片
		jQuery('._h5mod_image_remove').bind('click', function (event) {
			event.stopPropagation();
			image_delete_handler(this);
		});

		// 选择并上传图片
		jQuery('._h5mod_image_uploader').on('click', function () {
			image_uploader(this);
		});

		/*
		// 预览图片
		jQuery(document).on('click', '._h5mod_uploader_preview', function () {
			//image_preview(this);
		});
		*/

		// 加载对话框组件
		require(['dialog'], function(AddrbookComponent){
			MLoading.hide();
		});
	});

});
</script>
{/literal}
{/if}

<div id="{$image_uploader_id}" class="_h5mod_uploader_box mod_photo_uploader" data-inputname="{$iptname}" data-bigsize="{$bigsize}" data-thumbsize="{$thumbsize}" data-progress="{$show_weixin_progress}">
	<label style="left:50px;width:100%;" class="_h5mod_uploader_show mod_photo_uploader readonly">
{if !empty($attachs)}
	{foreach $attachs as $att}
		<a href="javascript:;" class="photo" data-id="{$att['at_id']}">
			<img class="_h5mod_uploader_preview" src="{voa_h_attach::attachment_url($att['at_id'], $thumbsize)}?ts={$timestamp}&sig={voa_h_attach::attach_sig_create($att['at_id'])}" data-big="{voa_h_attach::attachment_url($att['at_id'], $bigsize)}" />
			<i class="rm _h5mod_image_remove" data-id="{$att['at_id']}">-</i>
		</a>
	{/foreach}
{/if}
	</label>
	<input type="hidden" id="{$iptname}" name="{$iptname}" value="{$iptvalue}" />
	<a href="javascript:;" class="add _h5mod_image_uploader">+</a>
</div>

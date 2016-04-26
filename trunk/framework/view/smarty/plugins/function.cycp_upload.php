<?php
/**
 * function.cycp_upload.php
 * 后台异步上传文件函数
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 用于后台的上传组件
 * @param array $params
 * @param array $template
 */
function smarty_function_cycp_upload($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义参数
	$defaults = array(
		'inputname' => 'at_id',
		'thumbsize' => 45,
		'callback' => null,
		'attachid' => null,
		'hidedelete' => 0,
		'btnstring' => '上传图片',
		'callbackall' => null,
		'showimage' => 1,
		'tip' => ''
	);

	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	// 默认的附件ID
	$attachid = $params['attachid'] ? $params['attachid'] : '';

	// api url
	$api_url = '/admincp/api/attachment/upload/?file=file';
	if ($params['thumbsize'] > 0) {
		$api_url .= '&thumbsize='.$params['thumbsize'];
	}

	if (!defined('_CYCP_UPLOAD_')) {
		define('_CYCP_UPLOAD_', 1);
		$template->append('_cycp_js_', 'upload.js');
	}

	// js 回调函数名
	$callback = $params['callback'] ? $params['callback'] : '';
	// js 完全回调函数名
	$callbackall = $params['callbackall'] ? $params['callbackall'] : '';

	// 按钮显示的文字
	$btnstring = $params['btnstring'];

	// 存在图片
	if ($attachid) {
		$showimage = '<a href="'.voa_h_attach::attachment_url($attachid, 0).'" target="_blank">
				<img src="'.voa_h_attach::attachment_url($attachid, $params['thumbsize']).'"
						border="0" alt="" style="max-width:64px;max-height:32px;" /></a>';
	} else {
		$showimage = '';
	}

	// 从不显示删除按钮
	if ($params['hidedelete'] == 1) {
		$showdelete_display = ' style="display: none"';
	} else {
		if ($showimage) {
			$showdelete_display = '';
		} else {
			$showdelete_display = ' style="display: none"';
		}
	}

	$tip = '';
	if ($params['tip']) {
		$tip = '<em style="font-size:12px; font-style:normal">'.$params['tip'].'</em>';
	}

	$showimage_display = '';
	if (empty($params['showimage'])) {
		$showimage_display = ' style="display: none"';
	}

	return <<<EOF
<div class="uploader_box">
	<input type="hidden" class="_input" name="{$params['inputname']}" value="{$attachid}" />
	<span class="btn btn-success fileinput-button">
		<i class="glyphicon glyphicon-plus"></i>
		<span>{$btnstring}</span>
		<input class="cycp_uploader" type="file" name="file" data-url="{$api_url}" data-callback="{$callback}" data-callbackall="{$callbackall}" data-hidedelete="{$params['hidedelete']}" data-showimage="{$params['showimage']}" />
		{$tip}
	</span>
	<span class="_showdelete"{$showdelete_display}><a href="javascript:;" class="btn btn-danger cycp_uploader_delete">删除</a></span>
	<span class="_showimage"{$showimage_display}>{$showimage}</span>
</div>
EOF;
}

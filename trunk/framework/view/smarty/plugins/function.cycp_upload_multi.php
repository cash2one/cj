<?php
/**
 * function.cycp_upload_multi.php
 * 后台异步上传多图片函数
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 用于后台的上传多图片组件
 * @param array $params
 * @param array $template
 */
function smarty_function_cycp_upload_multi($params, $template) {

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
		'max' => 5,
		'attachid' => null,
		'btnstring' => '上传图片',
		'tip' => '',
		'callbackall' => null,
		'callback' => null,
		'showimage' => 1
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

	if (!defined('_CYCP_UPLOAD_MULTI_')) {
		define('_CYCP_UPLOAD_MULTI_', 1);
		$template->append('_cycp_js_', 'upload_multi.js');
	}

	// js 回调函数名
	$callback = $params['callback'] ? $params['callback'] : '';
	// js 完全回调函数名
	$callbackall = $params['callbackall'] ? $params['callbackall'] : '';

	// 按钮显示的文字
	$btnstring = $params['btnstring'];

	// 存在图片
	$showimage = '';
	if ($attachid) {
		foreach (explode(',', $attachid) as $_id) {
			$_id = trim($_id);
			if (!is_numeric($_id) || $_id <= 0) {
				continue;
			}
			$showimage .= '<div class="col-sm-1 _delete_image" style="min-width:64px"><div class="uploader-image-show _uploader_image_show">';
			$showimage .= '<a href="'.voa_h_attach::attachment_url($_id, 0).'" target="_blank" style="height:'.$params['thumbsize'].'px">
				<img src="'.voa_h_attach::attachment_url($_id, $params['thumbsize']).'"
						border="0" alt=""
						style="max-width:'.$params['thumbsize'].'px;max-height:'.$params['thumbsize'].'px;" /></a>';
			$showimage .= '<button type="button" class="btn btn-danger btn-sm _uploader_delete" data-id="'.$_id.'">删除</button>';
			$showimage .= '</div></div>';
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
<div class="uploader_multi_box">
	<div class="uploader_multi_line">
		<input type="hidden" name="{$params['inputname']}" value="{$attachid}" />
		<span class="btn btn-success fileinput-button">
			<i class="glyphicon glyphicon-plus"></i>
			<span>{$btnstring}</span>
			<input class="_cycp_uploader_multi" type="file" name="file" multiple="multiple" data-thumbsize="{$params['thumbsize']}" data-limitMultiFileUploads="{$params['max']}" data-url="{$api_url}" data-callback="{$callback}" data-callbackall="{$callbackall}" />
			{$tip}
		</span>
	</div>
	<div class="row"{$showimage_display}><div class="uploader_multi_showimage _showimage">{$showimage}</div></div>
</div>
EOF;
}

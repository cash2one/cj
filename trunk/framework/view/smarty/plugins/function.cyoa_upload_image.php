<?php
/**
 * function.cyoa_upload_image.php
 * 上传图片
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_upload_image($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(
		// 用于显示上传图片预览的容器ID
		//'id' => 'image_uploader_id',
		// 需要做展示的图片列表，array(array(at_id => int[, url => string]), array(at_id => int[, url => string]), ... ...)
		'attachs' => array(),
		// 本地附件的aid存放的文本框表单名
		'name' => 'at_ids',
		// 本地附件的aid，多个之间半角逗号“,”分隔，也可以传入数组array(int, int, int)，默认从attachs提取
		'value' => '',
		// 设置最少需要上传的图片数，0=不限制，允许不传图片（暂时无效）
		'min' => 0,
		// 设置最多允许上传的图片数，最多只允许上传20个。（暂时无效）
		'max' => 5,
		// 是否显示微信上传进度提示框，1=显示，2=不显示
		'progress' => 1,
		// 缩略图尺寸
		'thumbsize' => 45,
		// 预览时的大图尺寸
		'bigsize' => 0,
		// 是否允许上传图片
		'allow_upload' => 1,
		// 描述性文字
		'description' => '',
		'div' => array(
			'class' => ''
		),
		'styleid' => 1,
		'title' => '',
	);

	// 格式化默认值
	$params = _cyoa_format_params($params, array('div'));

	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	// 如果禁止上传 且 图片列表为空，则直接输出
	if (!$params['allow_upload'] && empty($params['attachs'])) {
		return '';
	}

	// 标记已载入此组件
	if (!defined('_TPL_UPLOAD_IMAGE_')) {
		define('_TPL_UPLOAD_IMAGE_', 1);
		$first_load = 1;
	} else {
		$first_load = 0;
	}

	// 重新整理附件列表
	$at_ids = array();
	$attachs = array();
	$attach_num = 1;
	foreach ($params['attachs'] as $_attach) {
		$_aid = isset($_attach['at_id']) ? $_attach['at_id'] : $_attach['aid'];
		if (isset($at_ids[$_aid])) {
			continue;
		}
		$at_ids[$_aid] = $_aid;
		$_attach['_thumb'] = voa_h_attach::attachment_url($_aid, $params['thumbsize']);
		$_attach['_big'] = voa_h_attach::attachment_url($_aid, $params['bigsize']);
		// 确保图片地址“唯一”
		if (strpos($_attach['_big'], '?') === false) {
			$_attach['_big'] .= '?_num='.$attach_num;
		} else {
			$_attach['_big'] .= '&_num='.$attach_num;
		}
		$attachs[$_aid] = $_attach;
		$attach_num++;
	}

	// 如果未给定at_ids，则使用附件列表内的at_id
	if (empty($params['value'])) {
		$params['value'] = $at_ids;
	}
	// 整理value为字符串格式
	if (is_array($params['value'])) {
		$params['value'] = implode(',', $params['value']);
	}

	$func = 'style_'.$params['styleid'];
	if (!function_exists($func)) {
		return 'styleid '.$params['styleid'].' not exists!';
	}

	// 标记载入了upload image
	if ($first_load) {
		$template->append('_cyoa_jsapi_', 'chooseImage');
		$template->append('_cyoa_jsapi_', 'previewImage');
		$template->append('_cyoa_jsapi_', 'uploadImage');
	}

	// 显示图片列表
	$attach_list_show = '';
	foreach ($attachs as $_aid => $_attach) {
		$attach_list_show .= <<<EOF
<div class="ui-badge-wrap">
	<img src="{$_attach['_thumb']}" data-big="{$_attach['_big']}" alt="" border="0" class="_uploader_preview" data-aid="{$_aid}" />
	<div class="ui-badge-cornernum _uploader_remove" data-aid="{$_aid}">-</div>
</div>
EOF;
	}

	return $func($params, $template, $attachs, $attach_list_show);
}

/**
 * 风格1
 * @param array $params
 * @param object $template
 */
function style_1($params, $template, $attachs, $attach_list_show) {

	if (!defined('_TPL_UPLOAD_IMAGE_1_')) {
		define('_TPL_UPLOAD_IMAGE_1_', true);
		$template->append('_cyoa_h5mod_', 'upload_image.js');
	}

	if (empty($params['div']['class'])) {
		$params['div']['class'] = 'ui-form-item ui-form-item-show ui-border-t upload';
	}

	// 描述文字
	$description = '';
	if ($params['description']) {
		$description = <<<EOF
<div class="upload-time">{$params['description']}</div>
EOF;
	}

	// 显示上传按钮
	$upload_handle = '';
	if ($params['allow_upload']) {
		$upload_handle = '<div class="ui-badge-wrap _uploader_add" data-max="'.$params['max'].'" data-progress="'.$params['progress'].'" data-thumbsize="'.$params['thumbsize'].'" data-bigsize="'.$params['bigsize'].'"'.(count($attachs) >= $params['max'] ? ' style="display: none"' : '').'></div>';
	}

	// 构造容器内的数据属性
	$attrs = '';
	foreach ($params as $_attr => $_value) {
		if (in_array($_attr, array('attachs', 'value', 'description', 'div'))) {
			continue;
		}
		$attrs .= ' data-'.$_attr.'="'.$_value.'"';
	}

	return <<<EOF
<div class="{$params['div']['class']} _uploader_box"{$attrs}>
	<div class="upload-box clearfix _uploader_image">
		<div class="_uploader_image_box">{$attach_list_show}</div>
		{$upload_handle}
	</div>
	{$description}
	<input type="hidden" name="{$params['name']}" value="{$params['value']}" />
</div>
EOF;
}

/**
 * 风格2
 * @param unknown $params
 * @param unknown $template
 * @param unknown $attachs
 */
function style_2($params, $template, $attachs, $attach_list_show) {

	if (!defined('_TPL_UPLOAD_IMAGE_1_')) {
		define('_TPL_UPLOAD_IMAGE_1_', true);
		$template->append('_cyoa_h5mod_', 'upload_image.js');
	}

	// 构造容器内的数据属性
	$attrs = '';
	foreach ($params as $_attr => $_value) {
		if (in_array($_attr, array('attachs', 'value', 'description', 'div'))) {
			continue;
		}
		$attrs .= ' data-'.$_attr.'="'.$_value.'"';
	}

	$upload_handle = '';
	if ($params['allow_upload']) {
		$upload_handle = '<a class="ui-icon-photo ui-icon _uploader_add" data-max="'.$params['max'].'" data-progress="'.$params['progress'].'" data-thumbsize="'.$params['thumbsize'].'" data-bigsize="'.$params['bigsize'].'"'.(count($attachs) >= $params['max'] ? ' style="display: none"' : '').'></a>';
	}

	if (empty($params['div']['class'])) {
		$params['div']['class'] = 'ui-form-item';
	}

	return <<<EOF
<div class="_uploader_box"{$attrs}>
	<div class="{$params['div']['class']}">
		<label>{$params['title']}</label>
		{$upload_handle}
		<input type="hidden" name="{$params['name']}" value="{$params['value']}" />
	</div>
	<div class="upload clearfix _uploader_image upload-padding-0">
		<div class="_uploader_image_box">
		{$attach_list_show}
		</div>
	</div>
</div>
EOF;
}

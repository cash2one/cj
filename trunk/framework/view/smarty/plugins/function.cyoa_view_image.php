<?php
/**
 * function.cyoa_view_image.php
 * 浏览图片
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_view_image($params, $template) {

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
		'id' => 'image_view_id',
		// 需要做展示的图片列表，array(array(at_id => int[, url => string]), array(at_id => int[, url => string]), ... ...)
		// 也可以at_id列表
		'attachs' => array(),
		// 本地附件的aid存放的文本框表单名
		'name' => 'at_ids',
		// 本地附件的aid，多个之间半角逗号“,”分隔，也可以传入数组array(int, int, int)，默认从attachs提取
		'value' => '',
		// 缩略图尺寸
		'thumbsize' => 45,
		// 预览时的大图尺寸
		'bigsize' => 0,
		// 描述性文字
		'description' => '',
		// 仅显示模块本身而不显示外部的html
		'onlymodule' => 0,
	);

	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	// 没有任何图片
	if (empty($params['attachs']) || !is_array($params['attachs'])) {
		return '';
	}
	// 确定传入的 attachs 是id列表还是附件信息列表
	if (is_numeric($params['attachs'][0])) {
		// 传入的是附件id列表
		$ids = $params['attachs'];
		$params['attachs'] = array();
		foreach ($ids as $_id) {
			$params['attachs'][] = array(
				'at_id' => $_id,
			);
		}
		unset($ids, $_id);
	}

	// 标记已载入此组件
	if (!defined('_TPL_VIEW_IMAGE_')) {
		define('_TPL_VIEW_IMAGE_', 1);
		$first_load = 1;
	} else {
		$first_load = 0;
	}

	// 整理各容器的ID名
	// 描述文字
	$id_description = $params['id'].'_description';
	// at_id文本框
	$id_atid_input = $params['id'].'_input';

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

	// 描述文字
	$description = '';
	if ($params['description']) {
		$description_id = ' id="'.$id_description.'"';
		$description = <<<EOF
<div class="upload-time"{$description_id}>{$params['description']}</div>
EOF;
	}

	// 显示图片列表
	$attach_list_show = '';
	foreach ($attachs as $_aid => $_attach) {
		$attach_list_show .= <<<EOF
<div class="ui-badge-wrap">
	<img src="{$_attach['_thumb']}" data-big="{$_attach['_big']}" alt="" border="0" class="_view_preview" data-id="{$params['id']}" data-aid="{$_aid}" style="max-height:{$params['thumbsize']}px;max-width:{$params['thumbsize']}px;margin-top:1px" />
</div>
EOF;
	}

	// 构造容器内的数据属性
	$attrs = '';
	foreach ($params as $_attr => $_value) {
		if (in_array($_attr, array('attachs', 'value', 'description'))) {
			continue;
		}
		$attrs .= ' data-'.$_attr.'="'.$_value.'"';
	}

	// 标记载入了view image
	if ($first_load) {
		$template->append('_cyoa_h5mod_', 'view_image.js');
		$template->append('_cyoa_jsapi_', 'previewImage');
	}

	$input = <<<EOF
<input type="hidden" id="{$id_atid_input}" name="{$params['name']}" value="{$params['value']}" />
EOF;

	if (!empty($params['onlymodule'])) {
		return $attach_list_show.$input;
	}

	return <<<EOF
<div class="ui-form-item ui-form-item-show upload" id="{$params['id']}"{$attrs}>
	<div class="upload-box clearfix _view_image">
	{$attach_list_show}
	{$input}
	</div>
</div>
{$description}
EOF;
}

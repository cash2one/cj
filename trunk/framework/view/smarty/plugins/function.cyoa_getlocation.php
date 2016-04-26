<?php
/**
 * function.cyoa_getlocation.php
 * 获取地理位置信息控件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 获取地理位置信息
 * @param array $params 请求的参数
 * @param object $template smarty对象
 * @return string
 */
function smarty_function_cyoa_getlocation($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(
		'get_id' => 'get-location',
		'input_location_name' => 'location',
		'input_location_value' => '',
		'input_address_name' => 'location_address',
		'input_address_value' => '',
		'onlyread' => 0,
		'div' => array(
			'class' => 'ui-form-item ui-border-t',
		),
		'styleid' => 1
	);

	// 格式化请求的参数以符合默认定义规则
	$params = _cyoa_format_params($params);
	// 设置默认的data属性值
	$params = _cyoa_merge_attrdata($defaults, $params);
	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	if ($params['onlyread']) {
		_location_view($params, $template);
	} else {
		_location_get($params, $template);
	}
}

/**
 * 构造容器属性
 * @param unknown $params
 * @return multitype:string
 */
function _make_attr($params) {
	$attr = array();
	$attr[] = 'data-location="'.$params['input_location_name'].'"';
	$attr[] = 'data-location_address="'.$params['input_address_name'].'"';
	return implode(' ', $attr);
}

/**
 * 显示只读的位置控件
 * @param array $param
 * @param object $template
 */
function _location_view($params, $template) {
	$address = rhtmlspecialchars($params['input_address_value']);
	$location = rhtmlspecialchars($params['input_location_value']);
	$address_string = !$address ? ($address != -1 ? '未提供地址信息' : '') : '';
	$attr_string = _make_attr($params);
	echo <<<EOF
<div class="{$params['div']['class']} _location_box" {$attr_string}>
	<div class="ui-form-location">
		<i class="ui-icon ui-icon-location-address"></i>
		{$address_string}
	</div>
	<input type="hidden" name="{$params['input_location_name']}" class="_location_input" value="{$location}" />
	<input type="hidden" name="{$params['input_address_name']}" class="_location_address_input" value="{$address}" />
</div>
EOF;
}

/**
 * 显示一个位置获取控件，可更改
 * @param unknown $param
 * @param unknown $template
 */
function _location_get($params, $template) {

	if (!defined('__TPL_LOCATION__')) {
		define('__TPL_LOCATION__', true);
		$js_code = <<<EOF
require(["zepto", "underscore", "frozen", "h5mod/getlocation"], function ($, _, fz, getLocation) {
	var gl = new getLocation($('#{$params['get_id']}'));
	gl.get();
	$('#cyoa-body').on('click', '._get_location', function () {
		gl.get($(this).parents('._location_box'), false);
	});
});
EOF;
		$template->append('_cyoa_jsapi_', 'getLocation');
		$template->append('_cyoa_jsapi_code_', $js_code);
	}

	$attr_string = _make_attr($params);
	echo <<<EOF
<div class="{$params['div']['class']} _location_box" {$attr_string} id="{$params['get_id']}">
	<span class="_location_icon"><span class="ui-icon-get-location-loading"><i class="ui-loading"></i></span></span>
	<div class="ui-form-location">
		<a class="ui-icon ui-icon-location-address _get_location" href="javascript:;"></a>
		<input type="text" name="{$params['input_address_name']}" class="_location_address_input" value="" placeholder="正在获取位置信息，请稍候……" />
	</div>
	<input type="hidden" name="{$params['input_location_name']}" class="_location_input" value="" />
</div>
EOF;

}

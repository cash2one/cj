<?php
/**
 * function.cy_input_datetime.php
 * datetime输入框
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 构造时间日期输入框
 * @param array $params 请求的参数
 * @param object $template smarty对象
 * @return string
 */
function smarty_function_cyoa_input_datetime($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(

		// attr_xx 定义input标签标准属性
		'attr' => array(
			'name' => 'datetime',
			'value' => '',
			'class' => ''
		),
		// label_xx 定义label标签属性
		'label' => array(
			'id' => null,
			'class' => null,
			'style' => null,
			'for' => null
		),
		// div_xx 定义外层div标签属性
		'div' => array(
			'class' => null,
			'id' => null,
			'style' => null
		),
		// data_xx 定义input的data-x属性
		'data' => array(),
		// extra_xx 定义input的自定义扩展属性
		'extra' => array(),
		'title' => '',
		'tip' => '',
		'onlymodule' => 0,// 是否仅显示输入组件而不显示外部html
		'date' => array(
			'max' => null,
			'min' => null
		),
		'time' => array(
			'max' => null,
			'min' => null
		),
		'all'=> 1
	);

	// 格式化请求的参数以符合默认定义规则
	$params = _cyoa_format_params($params, array('attr', 'label', 'div', 'data', 'extra', 'date', 'time', 'all'));
	// 设置默认值
	$params = _cyoa_merge($defaults, $params);
	$params['attr']['type'] = 'hidden';
	unset($defaults);

	foreach ($params['date'] as $_key => &$_val) {
		if ($_val === null || ($_key != 'max' && $_key != 'min')) {
			continue;
		}
		if (!is_numeric($_val)) {
			continue;
		}
		$_val = rgmdate($_val, 'Y-m-d');
	}

	foreach ($params['time'] as $_key => &$_val) {
		if ($_val === null || ($_key != 'max' && $_key != 'min')) {
			continue;
		}
		if (!is_numeric($_val)) {
			continue;
		}
		$_val = rgmdate($_val, 'H:i');
	}

	$default_date = rgmdate(time(), 'Y-m-d');
	$default_time = rgmdate(time(), 'H:i');
	if ($params['attr']['value']) {
		if (is_numeric($params['attr']['value'])) {
			$datetime = rgmdate($params['attr']['value'], 'Y-m-d H:i');
		} else {
			$datetime = $params['attr']['value'];
		}
		list($default_date, $default_time) = explode(' ', $datetime);
	}
	$params['date']['value'] = $default_date;
	$params['time']['value'] = $default_time;

	$date_attr = _cyoa_attr_string($params['date']);
	$time_attr = _cyoa_attr_string($params['time']);

	$params['attr']['value'] = $default_date.' '.$default_time;

	// 所有标签和属性列表
	$tag_attrs = array();
	// 标签
	$tag_attrs[] = 'input';
	if ($params['attr']['class']) {
		$params['attr']['class'] .= ' _input_datetime_value';
	} else {
		$params['attr']['class'] = '_input_datetime_value';
	}
	// input 标签属性字符串
	$_attrs = _cyoa_attr_string($params['attr']);

	if ($_attrs) {
		$tag_attrs[] = $_attrs;
	}
	// data-*属性字符串
	$_data = _cyoa_attr_string($params['data']);
	if ($_data) {
		$tag_attrs[] = $_data;
	}
	// 扩展属性
	$_extra = _cyoa_attr_string($params['extra']);
	if ($_extra) {
		$tag_attrs[] = $_extra;
	}
	$tag_attrs_string = implode(' ', $tag_attrs);
	unset($tag_attrs, $_attrs, $_data, $_extra);



	// 标记载入了input
	if (!defined('_TPL_DATETIME_')) {
		define('_TPL_DATETIME_', true);
		$template->append('_cyoa_h5mod_', 'input_datetime.js');
	}

	$module = "<{$tag_attrs_string} />";
	$module .= '<input type="date" class="ui-form-item-date _input_datetime" '.$date_attr.' />';
	if($params['all']) {
		$module .= '<input type="time" class="ui-form-item-time _input_datetime" '.$time_attr.' />';
	}

	if ($params['onlymodule']) {
		return $module;
	}

	// label 标签
	$label = '';
	if ($params['title']) {
		// label 标签属性字符串
		$label_attrs = array();
		$label_attrs[] = 'label';
		//if ($params['attr']['id']) {
		//	$params['label']['for'] = $params['attr']['id'];
		//}
		$_lable_attr = _cyoa_attr_string($params['label']);
		if ($_lable_attr) {
			$label_attrs[] = $_lable_attr;
		}
		$label = '<'.implode(' ', $label_attrs).'>'.$params['title'].'</label>';
		unset($label_attrs, $_lable_attr);
	}

	// div属性
	$div_attrs = array();
	if (empty($params['div']['class'])) {
		$params['div']['class'] = 'ui-form-item ui-border-t';// 默认的样式
	}
	$div_attrs[] = 'div';
	$div_attrs[] = _cyoa_attr_string($params['div']);
	$div_attrs_string = implode(' ', $div_attrs);
	unset($div_attrs, $params);

	return <<<EOF
<{$div_attrs_string}>
	{$label}
	<div class="ui-form-datetime">
	{$module}
	</div>
</div>
EOF;
}

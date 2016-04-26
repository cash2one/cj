<?php
/**
 * function.cyoa_select.php
 * 构造前端H5选择器
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_select($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(

		// attr_xx 定义select标签标准属性
		'attr' => array(
			'name' => null,
			'id' => null,
			'value' => null,
			'options' => array(),
			'disabled' => null,
			'required' => null,
			'multiple' => null,
			'size' => null,
			'style' => null,
			'class' => null
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
		// data_xx 定义select的data-x属性
		'data' => array(
			'callbackall' => null,// 完全回调函数
			'callback' => null// 执行后的回调函数
		),
		'title' => '',
		'tip' => '',
		'onlymodule' => 0,// 是否仅需要输入控件而不需要外部的html代码
		'styleid' => 0,
		'icodown' => 1// 是否显示下拉的图标
	);

	// 格式化请求的参数以符合默认定义规则
	$params = _cyoa_format_params($params);
	// 设置默认值
	$params = _cyoa_merge($defaults, $params);
	unset($defaults);
	// 选择框默认数据
	$select_value = $params['attr']['value'];
	// 选择框默认数据对应的文本名
	$select_value_text = array();

	// 可选项
	$init_options = $params['attr']['options'];

	// 由于选择框的默认数据并不属于属性值，因此自属性值内移除
	unset($params['attr']['value'], $params['attr']['options']);

	// 所有标签和属性列表
	$tag_attrs = array();
	// 标签
	$tag_attrs[] = 'select';
	// input 标签属性字符串
	$_attrs = _cyoa_attr_string($params['attr']);
	if ($_attrs) {
		$tag_attrs[] = $_attrs;
	}
	unset($_attrs);

	// 下拉文字区显示的下拉图标
	$ico_down = '';
	if (!empty($params['icodown'])) {
		$ico_down = ' <i class="label-tag label-tag-down"></i>';
	}

	// data-*属性字符串
	if (!empty($params['data'])) {
		$_data = _cyoa_attr_string($params['data']);
		if ($_data) {
			$tag_attrs[] = $_data;
		}
		unset($_data);
	}

	// 扩展属性
	if (!empty($params['extra'])) {
		$_extra = _cyoa_attr_string($params['extra']);
		if ($_extra) {
			$tag_attrs[] = $_extra;
		}
		unset($_extra);
	}
	// 拼凑属性字符串
	$tag_attrs_string = implode(' ', $tag_attrs);
	unset($tag_attrs);

	// label 标签
	$label = '';
	if ($params['title']) {
		// label 标签属性字符串
		$label_attrs = array();
		$label_attrs[] = 'label';
		if ($params['attr']['id']) {
			$params['label']['for'] = $params['attr']['id'];
		}
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
		if ($params['styleid'] == 1) {
			$params['div']['class'] = 'ui-selector-item ui-border-b';
		} else {
			$params['div']['class'] = 'ui-form-item ui-form-item-order ui-form-item-link';// 默认的样式
		}
	}
	$div_attrs[] = 'div';
	$div_attrs[] = _cyoa_attr_string($params['div']);
	$div_attrs_string = implode(' ', $div_attrs);
	unset($div_attrs);

	// 选项列表
	$options = array();
	// 下拉框第一个选择对象值
	$first_value = false;
	// 下拉框第一个选择对象的文本
	$first_value_text = '';
	// 可选项列表
	foreach ($init_options as $_val => $_txt) {
		$selected = '';
		if ($first_value === false) {
			$first_value = $_val;
			$first_value_text = $_txt;
		}
		if ($_val == $select_value
				|| (is_array($select_value)
						&& in_array($_val, $select_value))) {
			$selected = ' selected="selected"';
			$select_value_text[] = $_txt;
		}
		$options[] = '<option value="'.rhtmlspecialchars($_val).'"'.$selected.'>'
				.rhtmlspecialchars($_txt).'</option>';
	}

	// 模块本身
	$module = "<{$tag_attrs_string}>".implode('', $options)."</select>";

	// 只使用输入控件而不需要外部样式
	if ($params['onlymodule']) {
		return $module;
	}

	$value = array();
	if ($select_value_text) {
		if (is_array($select_value_text)) {
			$value = rhtmlspecialchars($select_value_text);
		} else {
			$value[] = rhtmlspecialchars($select_value_text);
		}
	} else {
		$value[] = $first_value_text;
	}
	$value = implode(', ', $value);

	if (!defined('_TPL_SELECT_')) {
		define('_TPL_SELECT_', 1);
		$template->append('_cyoa_h5mod_', 'select.js');
	}

	// 用于下拉展示的风格
	if ($params['styleid'] == 1) {
		return <<<EOF
<div class="ui-selector ui-selector-line">
	<div class="ui-selector-content">
		<{$div_attrs_string}>
			<p><span>{$value}</span>{$ico_down}</p>
			{$module}
		</div>
	</div>
</div>
EOF;
	}

	// 做为表单控件使用的风格
	return <<<EOF
<{$div_attrs_string}>
	{$label}
	<p>{$value}</p>
	{$module}
</div>
EOF;
}

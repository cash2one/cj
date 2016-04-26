<?php
/**
 * function.cy_input_switch.php
 * 开关类的input组件(checkbox)
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 开关构造
 * @param array $params 请求的参数
 * @param object $template smarty对象
 * @return string
 */
function smarty_function_cyoa_input_switch($params, $template) {

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
			'name' => null,
			'id' => null,
			'value' => '',
			//'open' => 0,
			'disabled' => null,
			//'max' => null,
			//'min' => null,
			//'maxlength' => null,
			//'pattern' => null,
			'readonly' => null,
			//'required' => null,
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
		// data_xx 定义input的data-x属性
		'data' => array(),
		// extra_xx 定义input的自定义扩展属性
		'extra' => array(),
		'title' => '',
		'tip' => '',
		'onlymodule' => 0,// 是否仅显示输入组件而不显示外部html
		'open' => 0,// 开关状态
	);

	// 格式化请求的参数以符合默认定义规则
	$params = _cyoa_format_params($params);
	// 设置默认值
	$params = _cyoa_merge($defaults, $params);
	$params['attr']['type'] = 'checkbox';
	if ($params['open']) {
		$params['attr']['checked'] = 'checked';
		$open = 1;
	} else {
		$open = 0;
	}
	unset($defaults);

	// 所有标签和属性列表
	$tag_attrs = array();
	// 标签
	$tag_attrs[] = 'input';
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

	if (empty($params['attr']['id'])) {
		return '<strong>cyoa_input_switch "attr_id" is null</strong>';
	}

	$module = "<label class=\"ui-switch ui-label-switch\" for=\"{$params['attr']['id']}\"><{$tag_attrs_string} /></label>";
	if ($params['onlymodule']) {
		return $module;
	}

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
		$params['div']['class'] = 'ui-form-item ui-form-item-switch ui-border-t';// 默认的样式
	}
	$div_attrs[] = 'div';
	$div_attrs[] = _cyoa_attr_string($params['div']);
	$div_attrs_string = implode(' ', $div_attrs);
	unset($div_attrs, $params);

	return <<<EOF
<{$div_attrs_string}>
	{$label}
	{$module}
</div>
EOF;
}

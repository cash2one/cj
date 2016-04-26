<?php
/**
 * function.cy_textarea.php
 * 文本区
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 文本区构造
 * @param array $params 请求的参数
 * @param object $template smarty对象
 * @return string
 */
function smarty_function_cyoa_textarea($params, $template) {

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
			'cols' => null,
			'rows' => null,
			'disabled' => null,
			'max' => null,
			'min' => null,
			'maxlength' => null,
			'pattern' => null,
			'placeholder' => '输入内容',
			'readonly' => null,
			'required' => null,
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
		'data' => array(
			'amount' => 1
		),
		// extra_xx 定义input的自定义扩展属性
		'extra' => array(),
		'title' => '',
		'tip' => '',
		'onlymodule' => 0,// 是否仅需要输入控件而不需要外部的html代码
		'styleid' => 0,// 风格样式结构
		'hidecount' => 0,// 是否隐藏剩余字数
	);

	// 格式化请求的参数以符合默认定义规则
	$params = _cyoa_format_params($params);
	// 设置默认的data属性值
	$params = _cyoa_merge_attrdata($defaults, $params);
	// 设置默认值
	$params = _cyoa_merge($defaults, $params);
	unset($defaults);
	// 文本区默认数据
	$textarea_value = $params['attr']['value'];
	// 由于文本区的默认数据并不属于属性值，因此自属性值内移除
	unset($params['attr']['value']);

	// 最多允许输入的字符数
	$maxlength = (int)$params['attr']['maxlength'];
	$maxlength_limit = '';
	if ($maxlength > 0) {
		//$params['data']['maxlength'] = $maxlength;
		$total = mb_strlen($textarea_value, 'utf-8');
		if (empty($params['data']['amount'])) {
			$residue_length = $maxlength - $total;
			$residue_length < 0 && $residue_length = 0;
			$maxlength_limit = '<div class="remaining-words _remaining">剩余字数<strong style="font-weight: normal">'.$residue_length.'</strong></div>';
		} else {
			$maxlength_limit = '<div class="remaining-words _remaining"><strong style="font-weight: normal">'.$total.'</strong>/'.$maxlength.'</div>';
		}
	}
	$textarea_value = rhtmlspecialchars($textarea_value);

	// 所有标签和属性列表
	$tag_attrs = array();
	// 标签
	$tag_attrs[] = 'textarea';
	// input 标签属性字符串
	$_attrs = _cyoa_attr_string($params['attr']);
	if ($_attrs) {
		$tag_attrs[] = $_attrs;
	}
	unset($_attrs);
	if ($params['styleid'] == 1) {
		$tag_attrs[] = 'class="ui-form-textarea"';
	}

	// data-*属性字符串
	$_data = _cyoa_attr_string($params['data']);

	if ($_data) {
		$tag_attrs[] = $_data;
	}
	unset($_data);

	// 扩展属性
	$_extra = _cyoa_attr_string($params['extra']);
	if ($_extra) {
		$tag_attrs[] = $_extra;
	}
	unset($_extra);

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
			$params['div']['class'] = 'ui-form-item';
		} else {
			$params['div']['class'] = 'ui-form-item ui-form-item-textarea ui-border-t clearfix';// 默认的样式
		}
	}
	$div_attrs[] = 'div';
	if (empty($params['div']['class'])) {
		$params['div']['class'] = '_textarea';
	} else {
		$params['div']['class'] .= ' _textarea';
	}
	$div_attrs[] = _cyoa_attr_string($params['div']);
	$div_attrs_string = implode(' ', $div_attrs);
	unset($div_attrs);

	// 标记载入了textarea
	if (!defined('_TPL_TEXTAREA_')) {
		if (empty($params['hidecount'])) {
			define('_TPL_TEXTAREA_', true);
			$template->append('_cyoa_h5mod_', 'textarea.js');
		}
	}

	$module = "<{$tag_attrs_string}>{$textarea_value}</textarea>";

	// 只使用输入控件而不需要外部样式
	if ($params['onlymodule']) {
		return $module;
	}

	if ($params['hidecount']) {
		$maxlength_limit = '';
	}

	if ($params['styleid'] == 1) {
		// 无标签的通栏文本区
		return <<<EOF
<{$div_attrs_string}>
	{$module}
</div>
{$maxlength_limit}
EOF;
	} else {
		return <<<EOF
<{$div_attrs_string}>
	{$label}
	{$module}
</div>
{$maxlength_limit}
EOF;
	}
}

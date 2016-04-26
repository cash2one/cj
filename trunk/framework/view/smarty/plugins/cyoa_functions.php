<?php
/**
 * cyoa_functions.php
 * 扩展函数库
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 合并两个数组，以前一个参数的值作为默认（如果后一个参数未定义）
 * @param array $defaults 默认值
 * @param array $params 自定义值
 * @return array
 */
function _cyoa_merge(array $defaults, array $params) {

	$merge = array();

	foreach ($defaults as $_key => $_val) {

		// 未定义则直接使用默认
		if (!isset($params[$_key]) || (!is_scalar($params[$_key]) && !is_array($params[$_key]))) {
			$merge[$_key] = $_val;
			continue;
		}

		// 数组
		if (is_array($_val)) {

			// 不对 data-* 和 扩展属性进行整理
			if ($_key == 'data' || $_key == 'extra') {
				$merge[$_key] = $params[$_key];
				continue;
			}

			if (is_array($params[$_key])) {
				if (!empty($_val)) {
					$merge[$_key] = _cyoa_merge($_val, $params[$_key]);
				} else {
					$merge[$_key] = $params[$_key];
				}
			} else {
				$merge[$_key] = $_val;
			}

			continue;
		}

		$merge[$_key] = $params[$_key];
	}

	return $merge;
}

/**
 * 合并默认值
 * @param unknown $default
 * @param unknown $params
 * @param unknown $types
 * @return unknown
 */
function _cyoa_merge_attrdata($default, $params, $types = array('data')) {
	foreach ($types as $_type) {
		if (!isset($default[$_type])) {
			continue;
		}
		if (!is_array($default[$_type])) {
			continue;
		}
		foreach ($default[$_type] as $_key => $_value) {
			if (!isset($params[$_type.'-'.$_key])) {
				$params[$_type][$_key] = $_value;
				$params[$_type.'-'.$_key] = $_value;
			}
		}
	}
	return $params;
}

/**
 * 自给定的参数值里获取关于标签属性的定义数组
 * 提取：attr、data、extra
 * @param array $params
 * @return array
 */
function _cyoa_format_params(array $params, array $types = array()) {

	$attrs = array();

	// 固有的前缀属性
	if (empty($types)) {
		$types = array('attr', 'data', 'extra', 'label', 'div');
	}
	foreach ($types as $_key) {
		if (in_array($_key, $types)) {
			continue;
		}
		$types[] = $_key;
	}
	unset($_key);

	$format = array();
	foreach ($types as $_key) {
		$format[$_key] = array();
	}

	foreach ($params as $_k => $_v) {

		// 寻找前缀分隔符号的位置
		$offset = strpos($_k, '_');
		if (!$offset) {
			// 分隔符号不存在或者为第一位，则忽略
			$format[$_k] = $_v;
			continue;
		}

		// 真实的属性名
		$_key = substr($_k, $offset + 1);
		// 属于哪个属性值
		$_type = substr($_k, 0, $offset);
		// 不属于内定的属性类型：attr、data、extra
		if (!in_array($_type, $types)) {
			$format[$_k] = $_v;
			continue;
		}

		// 清理掉参数根内对应的数据
		unset($params[$_k]);

		switch ($_type) {
			case 'data':
				$format['data']['data-'.$_key] = $_v;
				break;
			default:
				$format[$_type][$_key] = $_v;
		}
	}

	return $format;
}

/**
 * 构造一组HTML标签属性字符串
 * @param array $attr 默认属性与值列表
 * @todo 属性值不允许存在数组
 * @return string
 */
function _cyoa_attr_string(array $attr) {

	$available = array();

	foreach ($attr as $_name => $_value) {
		if (is_numeric($_name) || is_null($_value) || !$_name) {
			continue;
		}
		$available[] = $_name.'="'.addcslashes($_value, '"').'"';
	}

	return implode(' ', $available);
}

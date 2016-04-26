<?php
/**
 * voa_s_oa_diy_data
 * 产品数据
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_diy_data extends voa_s_abstract {
	// 整型
	const TYPE_INT = 'int';
	// 浮点型
	const TYPE_FLOAT = 'float';
	// 单选
	const TYPE_RADIO = 'radio';
	// 下拉选择
	const TYPE_SELECT = 'select';
	// 复选
	const TYPE_CHECKBOX = 'checkbox';
	// 附件
	const TYPE_ATTACH = 'attach';
	// 富文本
	const TYPE_TEXT = 'text';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 数据转换, 把实际字段名转成别名
	 * @param array $data 数组数组
	 * @param array $ids 附件id数组
	 * @return boolean
	 */
	public function translate_field(&$data, &$ids, $columns) {

		// 字段类型分类
		$ac = array(self::TYPE_ATTACH, self::TYPE_CHECKBOX);
		$ifrs = array(self::TYPE_INT, self::TYPE_FLOAT, self::TYPE_RADIO, self::TYPE_SELECT);

		// 先取出所有附件id
		foreach ($columns as $_col) {
			$f_alias = '_'.$_col['tc_id'];
			$field = empty($_col['field']) ? $f_alias : $_col['field'];

			// 如果有别名
			if (!empty($_col['field'])) {
				$data[$_col['field']] = $data[$f_alias];
				unset($data[$f_alias]);
			}

			// 给定默认值
			if (empty($data[$field])) {
				$data[$field] = in_array($_col['ct_type'], $ac) ? array() : (in_array($_col['ct_type'], $ifrs) ? '0' : '');
			} elseif (in_array($_col['ct_type'], $ac)) {
				$data[$field] = explode(',', $data[$field]);
			}

			// 如果是附件
			if (self::TYPE_ATTACH == $_col['ct_type']) {
				$ids = array_merge($ids, $data[$field]);
			}
		}

		return true;
	}

	/**
	 * 检查 diy 内容
	 * @param unknown $val
	 * @param unknown $column
	 * @param unknown $opts
	 * @return boolean
	 */
	public function chk_diy(&$val, $column, $opts = null) {

		// 根据类型格式化数据
		$func_chk = 'chk_string';
		if (in_array($column['ct_type'], array(self::TYPE_RADIO, self::TYPE_SELECT))) {
			$func_chk = 'chk_radio_select';
		} elseif (self::TYPE_CHECKBOX == $column['ct_type']) {
			$func_chk = 'chk_checkbox';
		} elseif (in_array($column['ct_type'], array(self::TYPE_INT, self::TYPE_FLOAT))) {
			$func_chk = 'chk_int_float';
		} elseif (self::TYPE_ATTACH == $column['ct_type']) {
			$func_chk = 'chk_attach';
		}

		// 按类型检查
		if (!$this->$func_chk($val, $column, $opts)) {
			return false;
		}

		// 字段为空并且该字段必填, 则
		if (empty($val) && $column['required']) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_REQUIRED, $column['fieldname']);
			return false;
		}

		// 如果有正则表达式
		if ($val && $column['reg_exp'] && !preg_match("/".$column['reg_exp']."/", $val)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_FORMAT_ERR, $column['fieldname']);
			return false;
		}

		// 数据结果
		$val = (string)$val;

		return true;
	}

	/**
	 * 对数值进行整型/浮点型验证
	 * @param array $coltype 自定义字段类型
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	public function chk_int_float(&$val, $coltype, $options) {

		if (self::TYPE_INT == $coltype['ct_type']) { // 如果为整型
			$val = (int)$val;
		} elseif (self::TYPE_FLOAT == $coltype['ct_type']) { // 如果为浮点型
			$p_s = explode('.', $val);
			$val = ((int)$p_s[0]).'.'.(isset($p_s[1]) ? (int)$p_s[1] : '0');
		}

		// 判断值是否处在定义的范围内
		if ((0 < $coltype['min'] && $coltype['min'] > $val) || (0 < $coltype['max'] && $coltype['max'] < $val)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_VALUE_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		return true;
	}

	/**
	 * 对单选/下拉选择数据进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	public function chk_radio_select(&$val, $coltype, $options) {

		$val = (int)$val;

		// 判断值是否为已定义的选项
		if (!empty($val) && !array_key_exists($val, $options)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_VALUE_INVALID, $coltype['fieldname']);
			return false;
		}

		return true;
	}

	/**
	 * 对复选框数据进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	public function chk_checkbox(&$val, $coltype, $options) {

		$chk_vs = array();
		// 遍历所有选项
		$val = (array)$val;
		foreach ($val as $_opt) {
			// 如果选项不在已定义的范围内
			if (!array_key_exists($_opt, $options)) {
				continue;
			}

			$chk_vs[] = $_opt;
		}

		// 判断选项数是否正确
		$len = count($chk_vs);
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_CHECKED_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		$val = implode(',', $chk_vs);

		return true;
	}

	/**
	 * 检查附件信息
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	public function chk_attach(&$val, $coltype, $options) {

		// 如果值不是数据
		if (!is_array($val)) {
			$val = array($val);
		}

		$ids = array();
		// 遍历附件信息
		foreach ($val as $_opt) {
			$_opt = (int)$_opt;
			// 如果值为空
			if (empty($_opt)) {
				continue;
			}

			$ids[$_opt] = $_opt;
		}

		// 判断选项数是否正确
		$len = count($ids);
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_CHECKED_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		// 重新拼凑返回值, 以 , 分隔
		$val = implode(',', $ids);

		return true;
	}

	/**
	 * 对字串进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	public function chk_string(&$val, $coltype, $options) {

		$val = (string)$val;
		$len = strlen($val);
		// 判断数据长度是否在已定义的范围内
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_LENGTH_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		return true;
	}

}

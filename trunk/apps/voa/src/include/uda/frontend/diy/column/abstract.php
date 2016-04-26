<?php
/**
 * voa_uda_frontend_diy_column_abstract
 * 统一数据访问/自定义数据表格属性/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_diy_column_abstract extends voa_uda_frontend_diy_abstract {
	// service tablecol
	protected $_serv_tablecol = null;

	public function __construct() {

		parent::__construct();
		$this->_serv_tablecol = new voa_s_oa_diy_tablecol();
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $table 数据结果
	 * @param boolean $ignore_null 忽略 null 值
	 * @return boolean
	 */
	protected function _parse_gp(&$column, $ignore_null = false) {

		$fields = array(
			array('fieldname', self::VAR_STR, array($this->_serv_tablecol, 'chk_fieldname'), null, $ignore_null),
			array('tc_desc', self::VAR_STR, null, null, $ignore_null),
			array('ct_type', self::VAR_STR, array($this->_serv_tablecol, 'chk_ct_type'), null, $ignore_null),
			array('ftype', self::VAR_INT, null, null, $ignore_null),
			array('min', self::VAR_INT, null, null, $ignore_null),
			array('max', self::VAR_INT, null, null, $ignore_null),
			array('reg_exp', self::VAR_STR, array($this->_serv_tablecol, 'chk_reg_exp'), null, $ignore_null),
			array('initval', self::VAR_STR, null, null, $ignore_null),
			array('orderid', self::VAR_INT, null, null, $ignore_null),
			array('required', self::VAR_INT, null, null, $ignore_null),
			array('unit', self::VAR_STR, null, null, $ignore_null),
		);
		// 提取数据
		if (!$this->extract_field($column, $fields)) {
			return false;
		}

		// 规范取值
		$column['required'] = 0 == $column['required'] ? 0 : 1;
		// 取字段信息
		$columntypes = voa_h_cache::get_instance()->get('columntype', 'oa');
		$columntype = $columntypes[$column['ct_type']];
		// 整理最大值/最小值
		if ($column['min'] > $column['max']) {
			$_tmp = $column['min'];
			$column['min'] = $column['max'];
			$column['max'] = $_tmp;
		}

		$column['min'] = $column['min'] < $columntype['min'] ? $columntype['min'] : $column['min'];
		$column['max'] = $column['max'] > $columntype['max'] ? $columntype['max'] : $column['max'];

		return true;
	}

}

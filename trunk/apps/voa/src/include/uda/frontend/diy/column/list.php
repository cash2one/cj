<?php
/**
 * voa_uda_frontend_diy_column_list
 * 统一数据访问/自定义数据表格属性/获取属性列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_column_list extends voa_uda_frontend_diy_column_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tc_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$conds['tid'] = self::$_s_table['tid'];
		// 读取表格字段
		$out = $this->_serv_tablecol->list_by_conds($conds, null, array('orderid' => 'asc'));

		// 遍历列表, 如果有 fieldalias 则替换 field
		foreach ($out as &$_v) {
			if (!empty($_v['fieldalias'])) {
				$_v['field'] = $_v['fieldalias'];
			}

			unset($_v['fieldalias']);
		}

		return true;
	}

}

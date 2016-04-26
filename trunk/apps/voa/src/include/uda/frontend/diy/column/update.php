<?php
/**
 * voa_uda_frontend_diy_column_update
 * 统一数据访问/自定义数据表格属性/更新属性
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_column_update extends voa_uda_frontend_diy_column_abstract {

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
		// 提取数据
		$column = array();
		if (!$this->_parse_gp($column)) {
			return false;
		}

		// 开始更新
		$tc_id = (int)$this->get('tc_id');
		$curcol = $this->_serv_tablecol->get($tc_id);
		if (empty($curcol)) {
			return true;
		}

		$conds = array(
			'tc_id' => $tc_id,
			'tid' => self::$_s_table['tid']
		);
		// 如果是系统字段, 则只允许改名称
		$data = array();
		if (voa_d_oa_diy_tablecol::COLTYPE_SYS == $curcol['coltype']) {
			$data = array(
				'fieldname' => $column['fieldname']
			);
		} else {
			$data = $column;
		}

		$this->_serv_tablecol->update_by_conds($conds, $data);

		return true;
	}

}

<?php
/**
 * voa_uda_frontend_diy_data_count
 * 统一数据访问/自定义数据表格数据/获取数据总数
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_count extends voa_uda_frontend_diy_data_abstract {

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
		// 查询的条件
		$fields = array(
			array('start', self::VAR_INT, null, null, true), // 当前页码
			array('limit', self::VAR_INT, null, null, true), // 每页记录数
			array('dr_id', self::VAR_INT, null, null, true)
		);
		$conds = array('tid' => self::$_s_table['tid']);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$page_option = array($conds['start'], $conds['limit']);
		unset($conds['start'], $conds['limit']);

		// 合并外部特殊条件
		$conds = array_merge($conds, $this->_special_conds);

		$out = $this->_serv_data->count_by_column_conds(self::$_s_tablecols, $conds, $page_option);

		return true;
	}

}

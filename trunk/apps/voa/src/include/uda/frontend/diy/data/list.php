<?php
/**
 * voa_uda_frontend_diy_data_list
 * 统一数据访问/自定义数据表格数据/数据列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_list extends voa_uda_frontend_diy_data_abstract {

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
			array('page', self::VAR_INT, null, null, true), // 当前页码
			array('limit', self::VAR_INT, null, null, true), // 每页记录数
			array('dr_id', self::VAR_ARR, null, null, true)
		);
		$conds = array('tid' => self::$_s_table['tid']);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		list($start, $limit, $page) = voa_h_func::get_limit($conds['page'], $conds['limit']);
		$page_option = array($start, $limit);
		unset($conds['page'], $conds['limit']);

		// 合并外部特殊条件
		$conds = array_merge($conds, $this->_special_conds);
		// 读取数据
		if (!$out = $this->_serv_data->list_by_column_conds(self::$_s_tablecols, $conds, $page_option, array('updated' => 'desc'))) {
			return true;
		}

		// 重新整理数据
		foreach ($out as $_k => &$_v) {
			// 数据转换
			$at_ids = array();
			$this->_serv_data->translate_field($_v, $at_ids, self::$_s_tablecols);
		}

		return true;
	}

}

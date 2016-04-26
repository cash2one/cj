<?php
/**
 * voa_uda_frontend_travel_ordergoods_list
 * 统一数据访问/旅游产品应用/产品销售列表
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_ordergoods_countsaleorder extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('start_date', self::VAR_STR, null, null, true),
			array('end_date', self::VAR_STR, null, null, true),
			array('saleuid', self::VAR_ARR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$serv = new voa_d_oa_travel_ordergoods();
		// 读取总数
		$this->_total = $serv->count_saleorder($conds);
		$out = $this->_total;

		return true;
	}

}

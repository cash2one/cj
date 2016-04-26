<?php
/**
 * voa_uda_frontend_travel_diyindex_del
 * 统一数据访问/旅游产品应用/删除自定义首页
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_diyindex_del extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tiid', self::VAR_ARR, null, null, false)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// tiid = 1 是通用首页
		$conds['tiid>?'] = 1;
		$serv = new voa_d_oa_travel_diyindex();
		// 读取总数
		if ($serv->delete_by_conds($conds)) {
			$out = true;
		}

		return true;
	}
}

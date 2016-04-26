<?php
/**
 * voa_uda_frontend_travel_material_get
 * 统一数据访问/旅游产品应用/获取指定素材信息(单条)
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_material_get extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('mtid', self::VAR_INT, null, null, false)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$serv = new voa_d_oa_travel_material();
		// 读取记录
		if (!$out = $serv->get($conds['mtid'])) {
			return false;
		}

		return true;
	}
}

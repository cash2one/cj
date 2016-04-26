<?php
/**
 * voa_uda_frontend_travel_material_update
 * 统一数据访问/旅游产品应用/更新(新增)素材信息
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_material_update extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('mtid', self::VAR_INT, null, null, true),
			array('subject', self::VAR_STR, null, null, true),
			array('message', self::VAR_STR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$serv = new voa_d_oa_travel_material();
		// 如果有mtid, 则说明是更新操作
		if (empty($conds['mtid'])) {
			$out = $serv->insert($conds);
		} else {
			$mtid = $conds['mtid'];
			unset($conds['mtid']);
			$out = $serv->update($mtid, $conds);
		}

		return true;
	}
}

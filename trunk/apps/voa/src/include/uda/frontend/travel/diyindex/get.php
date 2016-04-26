<?php
/**
 * voa_uda_frontend_travel_diyindex_get
 * 统一数据访问/旅游产品应用/获取指定自定义首页信息(单条)
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_diyindex_get extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tiid', self::VAR_INT, null, null, true),
			array('uid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$serv = new voa_s_oa_travel_diyindex();
		// 读取记录
		if (!$out = $serv->get_by_conds($conds)) {
			return true;
		}

		// 格式化
		$out = empty($out) ? array() : $out;
		$this->_format($out, false, $serv);

		return true;
	}
}

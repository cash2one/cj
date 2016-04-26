<?php
/**
 * voa_uda_frontend_express_sign
 * 统一数据访问/快递助手/签收确认
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_sign extends voa_uda_frontend_express_abstract {

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
			array('eid', self::VAR_INT, null, null, false)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}
		// 如果快递不存在
		$express = array();
		if (!$express = $this->_serv->get($conds['eid'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_express::EXPRESS_IS_NOT_EXISTS);
			return false;
		}


		$out=$this->_serv->update_by_conds($conds['eid'], array('flag' => voa_d_oa_express::GET_YES,'get_time'=>startup_env::get('timestamp')));
		return true;
	}

}

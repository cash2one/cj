<?php
/**
 * voa_uda_frontend_express_delete
 * 统一数据访问/快递助手/删除快递信息
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_delete extends voa_uda_frontend_express_abstract {

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
			array('eid', self::VAR_ARR, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

	    //删除快递基本信息
		$this->_serv->delete($data['eid']);
		// 删除扩展信息
		$serv_p = &service::factory('voa_d_oa_express_mem');
		$conds = array();
		$conds['eid in (?)'] = $data['eid'];
		$serv_p->delete_by_conds($conds);

		return true;
	}

}

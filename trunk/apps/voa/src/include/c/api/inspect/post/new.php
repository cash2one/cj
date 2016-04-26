<?php
/**
 * 新的巡店信息
 *　voa_c_api_inspect_new
 * $Author$
 * $Id$
 */

class 　voa_c_api_inspect_new extends voa_c_api_inspect_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 巡店ID
			'csp_id' => array('type' => 'int', 'required' => true),
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}
		// 巡店ID
		if (empty($this->_params['csp_id'])) {
			return $this->_set_errcode(voa_errcode_api_inspect::NEW_CSP_ID_NULL);
		}

		//入库操作
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['ins_id']
		);

		return true;
	}

	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_inspect_insert');
		/** 抄送人信息 */
		$params = $this->request->getx();
		$params['_user'] = $this->_user;
		if (!$uda->inspect_new($params, $inspect)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}
		$this->_return = $inspect;
		
		return true;
	}
}

<?php
/**
 * voa_c_api_reimburse_post_billdel
 * 删除报销清单
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_post_billdel extends voa_c_api_reimburse_base {

	public function execute() {
		// 请求参数
		$fields = array(
			// 明细表ID
			'id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$rbb_id = $this->_params['id'];
		$serv_b = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bill = $serv_b->fetch_by_id($rbb_id);
		if (empty($rbb_id) || empty($bill)) {
			//$this->_error_message('reimburse_bill_is_not_exists');
			return $this->_set_errcode(voa_errcode_api_reimburse::REIMBURSE_BILL_IS_NOT_EXISTS);
		}

		if ($bill['m_uid'] != $this->_member['m_uid']) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_reimburse::NO_PRIVILEGE);
		}


		if (!$this->_del($rbb_id)) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_reimburse::NO_PRIVILEGE);
		}

		return true;

	}

	/**
	 * 删除操作
	 * @param unknown $rbb_id
	 * @return boolean
	 */
	protected function _del($rbb_id) {
		$uda = &uda::factory('voa_uda_frontend_reimburse_delete');
		/** 报销清单信息 */
		if (!$uda->reimburse_bill_delete($rbb_id)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		return true;
	}
}

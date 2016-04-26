<?php
/**
 * 删除报销清单
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_bill_del extends voa_c_frontend_reimburse_bill {

	public function execute() {
		$rbb_id = (int)$this->request->get('rbb_id');
		$serv_b = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bill = $serv_b->fetch_by_id($rbb_id);
		if (empty($rbb_id) || empty($bill)) {
			$this->_error_message('reimburse_bill_is_not_exists');
			return false;
		}

		if ($bill['m_uid'] != $this->_user['m_uid']) {
			$this->_error_message('no_privilege');
		}

		
		if (!$this->_del($rbb_id)) {
			$this->_error_message('no_privilege');
		}

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
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('报销清单明细删除成功', "/reimburse/new/");
	}
}

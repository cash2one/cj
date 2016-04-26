<?php
/**
 * voa_c_api_superreport_post_add
 * 增加超级报表
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_post_add extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 门店ID
			'csp_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_add', $this->_ptname);
		$uda->member = $this->_member;
		if (!$uda->add_superreport($this->_params,  $result)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;

			return true;
		}

		$this->_result = array(
			'dr_id' => $result['dr_id'],
			'date' => $result['date'],
			'csp_id' => $result['csp_id']
		);

		return true;
	}

}


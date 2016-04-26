<?php
/**
 * voa_c_api_superreport_post_edit
 * 编辑超级报表
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_post_edit extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 日报ID
			'dr_id' => array('type' => 'int', 'required' => true),
			'csp_id' => array('type' => 'int', 'required' => true),

		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_edit', $this->_ptname);
		if (!$uda->edit_superreport($this->_params,  $result)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'date' => $result['date'],
			'csp_id' => $result['csp_id'],
			'dr_id' => $this->_params['dr_id']
		);

		return true;
	}

}


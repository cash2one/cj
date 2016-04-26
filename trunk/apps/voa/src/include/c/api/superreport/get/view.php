<?php
/**
 * voa_c_api_superreport_get_view
 * 获取超级报表日报编辑数据
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_view extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 日报ID
			'dr_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_view', $this->_ptname);
		$uda->member = $this->_member;
		if (!$uda->get_view($this->_params,  $result)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 输出结果
		$this->_result = array(
			'int' => $result['int'] ? array_values($result['int']) : array(),
			'text' => $result['text'] ? array_values($result['text']) : array()
		);

		return true;
	}

}


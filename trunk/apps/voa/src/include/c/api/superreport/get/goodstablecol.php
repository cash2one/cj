<?php
/**
 * voa_c_api_travel_get_goodstablecol
 * 获取表格列属性信息
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_goodstablecol extends voa_c_api_superreport_abstract {

	protected function _before_action($action) {

		// 检查权限
		$this->_require_login = false;

		return parent::_before_action($action);
	}

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_tablecol', $this->_ptname);
		$uda->member = $this->_member;
		if (!$uda->get_raw_template($this->_params,  $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}

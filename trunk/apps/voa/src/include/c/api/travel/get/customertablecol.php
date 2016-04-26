<?php
/**
 * voa_c_api_travel_get_customertablecol
 * 获取表格列属性信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_customertablecol extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_customer_tablecol', $this->_ptname);
		if (!$uda->list_all($this->_params, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}

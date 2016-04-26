<?php
/**
 * voa_c_api_customer_delete_customertable
 * 删除表格信息
 * $Author$
 * $Id$
 */

class voa_c_api_customer_delete_customertable extends voa_c_api_customer_abstract {

	public function execute() {

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_customer_table', $this->_ptname);
		if (!$uda->delete($this->_params)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}

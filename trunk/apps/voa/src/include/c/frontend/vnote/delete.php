<?php
/**
 * 删除备忘
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_delete extends voa_c_frontend_vnote_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_frontend_vnote_delete');
		if (!$uda->delete_vnote()) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('vnote_delete_succeed', '/vnote/so');
	}
}

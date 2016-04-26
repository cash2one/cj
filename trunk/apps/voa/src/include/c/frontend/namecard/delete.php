<?php
/**
 * 删除名片信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_delete extends voa_c_frontend_namecard_base {

	public function execute() {
		$nc_id = (int)$this->request->get('nc_id');
		$uda = &uda::factory('voa_uda_frontend_namecard_delete');
		if (!$uda->namecard_delete($nc_id)) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('删除操作成功', '/namecard/list');
	}
}

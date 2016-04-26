<?php
/**
 * voa_c_cyadmin_manage_adminergroup_delete
 * 主站后台/后台管理/管理组/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminergroup_delete extends voa_c_cyadmin_manage_adminergroup_base {

	public function execute() {

		// 待删除的管理组cag_id
		$cag_id = $this->request->get('cag_id');
		$cag_id = rintval($cag_id, false);

		$this->_adminergroup_delete($cag_id);

		$this->message('success', '指定管理组删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list'), false);
	}

}

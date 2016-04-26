<?php
/**
 * voa_c_admincp_manage_department_delete
 * 企业后台/企业管理/部门管理/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_department_delete extends voa_c_admincp_manage_department_base {

	public function execute() {

		$cd_id = $this->request->get('cd_id');
		$cd_id = rintval($cd_id, false);

		$uda_delete = &uda::factory('voa_uda_frontend_department_delete');
		if ($uda_delete->delete($cd_id)) {
			$this->message('success', '指定部门删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		} else {
			$this->message('error', $uda_delete->error);
		}

	}

}

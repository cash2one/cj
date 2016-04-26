<?php
/**
 * voa_c_admincp_system_adminer_add
 * 企业后台/系统设置/管理员/添加管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminer_add extends voa_c_admincp_system_adminer_base {

	public function execute() {
		$ca_id = 0;
		if ($this->_is_post()) {
			$this->_response_submit_edit($ca_id);
		}
		$this->view->set('ca_id', $ca_id);
		$this->view->set('adminer', $this->_adminer_detail($ca_id));
		$this->view->set('actionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		/** 所有部门列表 */
		$this->view->set('departmenuList', $this->_department_list());

		$this->output('system/adminer/edit_form');
	}

}

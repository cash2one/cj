<?php
/**
 * voa_c_admincp_system_adminer_list
 * 企业后台/系统设置/管理员/管理员列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_system_adminer_list extends voa_c_admincp_system_adminer_base {

	public function execute() {

		/** 管理员列表 */
		$this->view->set('adminerList', $this->_adminer_list());
		/** 删除基本链接 */
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('ca_id'=>'')));
		/** 编辑基础链接 */
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('ca_id'=>'')));
		$this->output('system/adminer/list');
	}
}

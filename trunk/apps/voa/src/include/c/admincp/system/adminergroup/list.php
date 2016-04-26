<?php
/**
 * voa_c_admincp_system_adminergroup_list
 * 企业后台/系统设置/后台管理组/管理组列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminergroup_list extends voa_c_admincp_system_adminergroup_base {
	public function execute(){

		/** 管理组列表 */
		$this->view->set('groupList', $this->_adminergroup_list());
		/** 系统管理组 */
		$this->view->set('systemgroup', $this->adminergroup_enables['sys']);

		/** 删除某个管理组的基本链接 */
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('cag_id'=>'')));

		/** 编辑某个管理组的基本链接 */
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('cag_id'=>'')));

		$this->output('system/adminergroup/list');

	}
}

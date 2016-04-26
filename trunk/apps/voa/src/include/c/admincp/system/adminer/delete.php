<?php
/**
 * voa_c_admincp_system_adminer_delete
 * 企业后台/系统设置/管理员/删除管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminer_delete extends voa_c_admincp_system_adminer_base{
	public function execute(){
		/** 待删除的管理员 id */
		$ca_id = $this->request->get('ca_id');
		$this->_adminer_delete($ca_id);
		$this->message('success', '指定管理员删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
	}
}

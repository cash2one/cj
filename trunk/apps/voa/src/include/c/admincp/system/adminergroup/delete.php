<?php
/**
 * voa_c_admincp_system_adminergroup_delete
 * 企业后台/系统设置/后台管理组/删除指定管理组
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminergroup_delete extends voa_c_admincp_system_adminergroup_base {

	public function execute() {

		/** 待删除的管理组id */
		$cag_id	=	$this->request->get('cag_id');

		// 查询管理组下 是否有管理员
		if ($this->_count_adminer_by_cag_id($cag_id) > 0) {
			$this->_error_message('该管理组下还有管理员,不能删除');
		};

		$this->_adminergroup_delete($cag_id);
		$this->message('success', '指定管理组删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
	}

}

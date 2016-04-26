<?php
/**
 * voa_c_admincp_system_adminergroup_add
 * 企业后台/系统设置/后台管理组/添加管理组
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminergroup_add extends voa_c_admincp_system_adminergroup_base {

	public function execute(){

		// 重置所有菜单，移除系统固有的菜单
		$this->_module_list = $this->_remove_cpmenu_system_all($this->_module_list);
		$this->_operation_list = $this->_remove_cpmenu_system_all($this->_operation_list);
		$this->_subop_list = $this->_remove_cpmenu_system_all($this->_subop_list);
		$this->view->set('module_list', $this->_module_list);
		$this->view->set('operation_list', $this->_operation_list);
		$this->view->set('subop_list', $this->_subop_list);

		/** 提交添加动作 */
		if ( $this->_is_post() ) {
			$this->_response_submit_edit(0);
		}

		/** 添加管理组的提交表单目标路径 */
		$this->view->set('editTargetUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		/** 管理组的默认表单数据填充 */
		$this->view->set('groupDetail', $this->_group_detail(0));

		$this->output('system/adminergroup/edit_form');
	}

}

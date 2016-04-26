<?php
/**
 * voa_c_cyadmin_manage_adminergroup_list
 * 主站后台/后台管理/管理组/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminergroup_list extends voa_c_cyadmin_manage_adminergroup_base {

	public function execute() {

		// 管理组列表
		$this->view->set('adminergroup_list', $this->_adminergroup_list());

		// 系统管理组
		$this->view->set('system_group', voa_d_cyadmin_common_adminergroup::ENABLE_SYS);

		// 删除某个管理组的基本链接
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', array('cag_id'=>'')));

		// 编辑某个管理组的基本链接
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', array('cag_id'=>'')));

		$this->output('cyadmin/manage/adminergroup/adminergroup_list');
	}

}

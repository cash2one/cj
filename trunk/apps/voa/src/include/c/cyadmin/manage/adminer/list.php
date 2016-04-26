<?php
/**
 * voa_c_cyadmin_manage_adminer_list
 * 主站后台/后台管理/管理员/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminer_list extends voa_c_cyadmin_manage_adminer_base {

	public function execute() {

		// 获取当前列表的管理员总数，分页，列表
		list($adminer_total, $adminer_multi, $adminer_list) = $this->_adminer_list();
		$this->view->set('adminer_list', $adminer_list);
		$this->view->set('adminer_total', $adminer_total);
		$this->view->set('adminer_multi', $adminer_multi);

		// 系统管理员的状态标记
		$this->view->set('system_adminer', voa_d_cyadmin_common_adminer::LOCKED_SYS);

		// 删除基本链接
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', array('ca_id'=>'')));

		// 编辑基础链接
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', array('ca_id'=>'')));

		$this->output('cyadmin/manage/adminer/adminer_list');
	}

}

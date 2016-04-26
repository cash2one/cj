<?php
/**
 * voa_c_admincp_manage_department_list
 * 企业后台/企业管理/部门管理/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_department_list extends voa_c_admincp_manage_department_base {

	public function execute(){

		$department_list = array();
		$uda_get = &uda::factory('voa_uda_frontend_department_get');
		$uda_get->list_all($department_list, 'all');

		// 所有部门列表
		$this->view->set('department_list', $department_list);

		// 编辑链接
		$this->view->set('displayorder_edit_url', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id));

		// 删除链接
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('cd_id' => '')));

		// 编辑链接
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('cd_id' => '')));

		// 部门通讯录连接
		$addressbook_url_base = $this->cpurl($this->_module, 'addressbook', 'search');
		if ($addressbook_url_base) {
			$addressbook_url_base .= '?issearch=1&amp;cd_id=';
		}
		$this->view->set('addressbook_url_base', $addressbook_url_base);

		$this->output('manage/department/list');
	}

}

<?php
/**
 * voa_c_admincp_manage_department_add
 * 企业后台/企业管理/部门管理/添加部门
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_department_add extends voa_c_admincp_manage_department_base {

	public function execute() {

		// 初始化当前新增的部门id
		$cd_id = 0;

		// 提交添加
		if ($this->_is_post()) {

			// 初始化uda
			$uda_department_update = &uda::factory('voa_uda_frontend_department_update');

			// 新数据
			$cd_name = (string)$this->request->post('cd_name');
			$department = array(
				'cd_name' => trim($cd_name),
				'cd_upid' => (int)$this->request->post('cd_upid'),
				'cd_displayorder' => (int)$this->request->post('cd_displayorder')
			);

			// 真实的数据
			$update = array();
			if (!$uda_department_update->update(array(), $department, $update)) {
				$this->message('error', $uda_department_update->error);
			} else {

				// 更新该部门的成员数
				$uda_department_update->update_usernum($update['cd_id'], 0);

				$this->message('success', $uda_department_update->error, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
			}

		}

		// 载入uda基本控制
		$uda_base = &uda::factory('voa_uda_frontend_department_base');
		// 载入部门获取
		$uda_get = &uda::factory('voa_uda_frontend_department_get');

		// 部门信息默认数据
		$department = array();
		$uda_get->department($cd_id, $department);

		// 赋值模板变量：当前操作的部门id
		$this->view->set('cd_id', $cd_id);

		// 赋值模板变量：当前操作的部门信息
		$this->view->set('department', $department);
		$this->view->set('department_select', $this->_department_select('cd_upid', array()));

		// 赋值模板变量：部门名称限制
		$this->view->set('name_rule', $uda_base->department_name_length);

		// 赋值模板变量：显示顺序限制
		$this->view->set('displayorder_rule', $uda_base->department_displayorder);

		// 赋值模板变量：提交添加时的url
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('manage/department/edit_form');

	}

}

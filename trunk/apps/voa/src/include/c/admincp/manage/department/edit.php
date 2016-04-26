<?php
/**
 * voa_c_admincp_manage_department_edit
 * 企业后台/企业管理/部门管理/编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_department_edit extends voa_c_admincp_manage_department_base {

	public function execute() {

		if (!isset($_POST['displayorder'])) {
			// 不是更新显示顺序操作则为编辑某个部门，找到该部门信息
			$cd_id = $this->request->get('cd_id');
			$cd_id = rintval($cd_id, false);
			$department = array();
			$uda_get = &uda::factory('voa_uda_frontend_department_get');
			$uda_get->department($cd_id, $department);

			if (empty($department['cd_id']) || $department['cd_id'] != $cd_id) {
				$this->message('error', '指定待编辑的部门不存在 或 已被删除');
			}
		}

		/**
		 * 提交更新
		 */
		if ($this->_is_post()) {

			// 初始化uda
			$uda_department_update = &uda::factory('voa_uda_frontend_department_update');

			// 新数据
			$cd_name = (string)$this->request->post('cd_name');
			$cd_name = trim($cd_name);
			$cd_upid = (int)$this->request->post('cd_upid');
			$cd_displayorder = (int)$this->request->post('cd_displayorder');

			if ($cd_name == $department['cd_name'] && $cd_upid == $department['cd_upid']) {

				// 提交更新显示顺序
				$displayorder = array();
				if (!empty($cd_displayorder)) {
					$displayorder[$department['cd_id']] = $cd_displayorder;
				} else {
					$displayorder = (array)$this->request->post('displayorder');
				}

				if ($uda_department_update->displayorder_update($displayorder)) {
					$this->message('success', '更新部门显示顺序操作成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
				} else {
					$this->message('error', $uda_department_update->error);
				}

			} else {
				// 提交更新某个部门
				$new_data = array(
					'cd_name' => $cd_name,
					'cd_upid' => $cd_upid,
					'cd_displayorder' => $cd_displayorder
				);

				// 真实的数据
				$update = array();
				if (!$uda_department_update->update($department, $new_data, $update)) {
					$this->message('error', $uda_department_update->error);
				} else {

					// 更新该部门的成员数
					$uda_department_update->update_usernum($cd_id, 0);

					$this->message('success', $uda_department_update->error, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
				}
			}
		}

		// 载入uda基本控制
		$uda_base = &uda::factory('voa_uda_frontend_department_base');

		// 赋值模板变量：当前操作的部门id
		$this->view->set('cd_id', $cd_id);

		// 赋值模板变量：当前操作的部门信息
		$this->view->set('department', $department);
		$this->view->set('department_select', $this->_department_select('cd_upid', array()));

		// 赋值模板变量：部门名称限制
		$this->view->set('name_rule', $uda_base->department_name_length);

		// 赋值模板变量：显示顺序限制
		$this->view->set('displayorder_rule', $uda_base->department_displayorder);

		// 赋值模板变量：提交修改时的url
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('cd_id' => $cd_id)));

		$this->output('manage/department/edit_form');
	}

}

<?php
/**
 * voa_c_cyadmin_manage_adminergroup_add
 * 主站后台/后台管理/管理组/添加新管理组
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminergroup_add extends voa_c_cyadmin_manage_adminergroup_base {

	public function execute() {

		// 管理组id
		$cag_id = 0;
		$adminergroup = $this->_adminergroup_get($cag_id, true);

		// 提交添加动作
		if ($this->_is_post()) {

			// 操作结果信息
			$result_msg = '';
			// 提交的数据
			$submit = array(
				'cag_title' => $this->request->post('cag_title'),
				'cag_enable' => $this->request->post('cag_enable'),
				'cag_description' => $this->request->post('cag_description'),
				'cag_role' => $this->request->post('cag_role'),
			);

			if ($this->_adminergroup_update($adminergroup, $submit, $result_msg)) {
				$this->message('success', '添加新管理组操作已完毕', $this->cpurl($this->_module, $this->_operation, 'list'));
			} else {
				$this->message('error', $result_msg);
			}
		}

		// 添加管理组的提交表单目标路径
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, array('cag_id' => $cag_id)));
		// 管理组详情
		$this->view->set('adminergroup', $this->_adminergroup_format($adminergroup));
		// 系统组标记
		$this->view->set('system_group', voa_d_cyadmin_common_adminergroup::ENABLE_SYS);
		// 启用状态描述映射关系
		$this->view->set('adminergroup_enable_map', $this->_adminergroup_enable_map);

		$this->output('cyadmin/manage/adminergroup/edit_form');
	}

}

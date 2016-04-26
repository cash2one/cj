<?php
/**
 * voa_c_cyadmin_manage_adminer_add
 * 主站后台/后台管理/管理员管理/添加管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminer_add extends voa_c_cyadmin_manage_adminer_base {

	public function execute() {

		$ca_id = 0;

		// 管理员详情
		$adminer = $this->_adminer_get($ca_id, true);

		// 提交添加动作
		if ($this->_is_post()) {

			// 返回的消息
			$result_msg = '';

			// 提交的数据
			$submit = array();
			$submit['ca_username'] = $this->request->post('ca_username');
			$submit['ca_password'] = $this->request->post('ca_password');
			$submit['ca_locked'] = $this->request->post('ca_locked');
			$submit['cag_id'] = $this->request->post('cag_id');
			$submit['ca_realname'] = $this->request->post('ca_realname');
			$submit['ca_mobilephone'] = $this->request->post('ca_mobilephone');

			if ($this->_adminer_update($adminer, $submit, $result_msg)) {
				// 更新成功
				$this->message('success', '添加管理员操作成功', $this->cpurl($this->_module, $this->_operation, 'list'));
			} else {
				// 更新失败
				$this->message('error', $result_msg);
			}
		}

		// 当前编辑的管理员ca_id
		$this->view->set('ca_id', $ca_id);
		// 当前编辑的管理员信息
		$this->view->set('adminer', $this->_adminer_format($adminer));
		// 表单提交链接
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, array('ca_id'=>$ca_id)));
		// 管理员状态数组推送到模板内
		$this->view->set('adminer_locked_map', $this->_adminer_locked_map);
		// 系统管理员的状态标记
		$this->view->set('system_adminer', voa_d_cyadmin_common_adminer::LOCKED_SYS);
		// 管理组列表
		$this->view->set('adminergroup_list', $this->_adminergroup_list());
		// 添加管理组的链接
		$add_adminergroup_url = $this->cpurl($this->_module, 'adminergroup', 'add');
		$this->view->set('add_adminergroup_url', $this->show_link($add_adminergroup_url, '', '添加新管理组', 'fa-plus'));

		$this->output('cyadmin/manage/adminer/edit_form');

	}

}

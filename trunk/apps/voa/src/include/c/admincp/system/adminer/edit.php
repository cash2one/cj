<?php
/**
 * voa_c_admincp_system_adminer_edit
 * 企业后台/系统设置/管理员/编辑管理员信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminer_edit extends voa_c_admincp_system_adminer_base {

	public function execute() {

		$ca_id = $this->request->get('ca_id');
		if (!$ca_id) {
			$this->message('error', '请指定要编辑的管理员');
		}

		// 管理员详情
		$adminerDetail = $this->_adminer_detail($ca_id);
		// 管理员不存在
		if (!$adminerDetail || !$adminerDetail['ca_id']) {
			$this->message('error', '对不起，指定的管理员不存在 或 已被删除');
		}

		// 提交修改动作
		if ($this->_is_post()) {
			// 如果是编辑自己的信息, 则不能编辑自己所处的组
			if ($adminerDetail['ca_id'] == $this->_user['ca_id'] || 1 == $adminerDetail['cag_id']) {
				if (isset($_POST['cag_id']) && $this->request->post('cag_id') != $adminerDetail['cag_id']) {
					$this->message('error', '不能更改自己或超级管理员的所属组');
				}
			}

			$this->_response_submit_edit($ca_id);
		}

		$adminerDetail['_lastlogin'] = $adminerDetail['ca_lastlogin'] ? rgmdate($adminerDetail['ca_lastlogin'], 'Y-m-d H:i') : '---';
		$this->view->set('ca_id', $ca_id);
		$this->view->set('adminer', $adminerDetail);
		$this->view->set('actionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('ca_id'=>$ca_id)));
		/** 所有部门列表 */
		$this->view->set('departmenuList', $this->_department_list());

		$this->output('system/adminer/edit_form');
	}

}

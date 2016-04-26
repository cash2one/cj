<?php
/**
 * voa_c_admincp_manage_member_add
 * 编辑员工信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_add extends voa_c_admincp_manage_member_base{

    //deprecated
	public function execute() {
return;
		$m_uid = 0;

		if (!$this->_department_list(1)) {
			$department_cp_url = $this->cpurl('manage', 'department', 'list');
			$this->message('error', '尚未设置部门，点击 <a href="'.$department_cp_url.'">这里设置部门</a> 后，再进行员工管理');
		}

		// 获取当前用户信息
		$member = array();
		$this->_uda_member_get->member_default_data($member);
		$member['m_active'] = voa_d_oa_member::ACTIVE_YES;

		if ($this->_is_post()) {
			// 提交添加
			$this->_submit();
		}

		// 格式化用户信息
		$this->_uda_member_format->format($member);

		$this->view->set('m_uid', $m_uid);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('m_uid'=>$m_uid)));
		$this->view->set('member', $member);
		$this->view->set('gender_list', $this->_uda_member_get->gender_list);
		$this->view->set('active_list', $this->_uda_member_get->active_list);
		$this->view->set('department_select', $this->_department_select('cd_id', $member['cd_id']));
		$this->view->set('job_list', $this->_job_list());

		$this->output('manage/member/edit_form');
	}

	private function _submit() {
		// 添加后返回的用户数据
		$m = array();

		// 提交的用户数据
		$submit = array();

		// 提交的字段 与 uda 内的字段对应映射关系
		$filed_map = array(
			'm_username' => 'm_username',
			'm_mobilephone' => 'm_mobilephone',
			'm_email' => 'm_email',
			'm_password' => 'm_password',
			'm_face' => 'm_face',
			'cd_id' => 'cd_id',
			'cj_id' => 'cj_id',
			'm_number' => 'm_number',
			'm_active' => 'm_active',
			'm_gender' => 'm_gender',
			'mf_qq' => 'mf_qq',
			'mf_weixinid' => 'mf_weixinid',
			'mf_telephone' => 'mf_telephone',
			'mf_birthday' => 'mf_birthday',
			'mf_address' => 'mf_address',
			'mf_idcard' => 'mf_idcard',
			'mf_remark' => 'mf_remark'
		);

		// 获取提交的用户数据
		foreach ($filed_map as $p => $u) {
			if ($p == 'm_password') {
				// 密码
				$password = $this->request->post($p);
				if ($password) {
					$submit[$u] = md5($password);
				} else {
					// 未设置密码
					$submit[$u] = false;
				}
			} elseif ($p == 'm_face') {
				// 头像
				$face = $this->request->post($p);
				if ($face) {
					$submit[$u] = $face;
				} else {
					$submit[$u] = false;
				}
			} else {
				$submit[$u] = $this->request->post($p);
			}
		}

		// 添加新用户
		if (!$this->_uda_member_insert->add($submit, $m)) {
			$this->message('error', '['.$this->_uda_member_insert->errcode.'] '.$this->_uda_member_insert->errmsg);
		}

		$this->message('success', '添加新员工操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
	}

}

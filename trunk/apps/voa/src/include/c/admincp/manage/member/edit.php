<?php
/**
 * voa_c_admincp_manage_member_edit
 * 编辑员工信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_edit extends voa_c_admincp_manage_member_base{

    //deprecated
	public function execute(){
return;
		$m_uid = (int)$this->request->get('m_uid');
		if ($m_uid < 0) {
			$this->message('error', '请指定要编辑的员工');
			return false;
		}

		if (!$this->_department_list(1)) {
			$department_cp_url = $this->cpurl('manage', 'department', 'list');
			$this->message('error', '尚未设置部门，点击 <a href="'.$department_cp_url.'">这里设置部门</a> 后，再进行员工管理');
			return false;
		}

		// 获取当前用户信息
		$member = array();
		if (!$this->_uda_member_get->member_by_uid($m_uid, $member, true)) {
			$this->message('error', $this->_uda_member_get->errmsg);
			return false;
		}

		// 格式化用户信息
		$this->_uda_member_format->format($member);

		/** 提交更改 */
		if ($this->_is_post()) {
			$mem = array();
			$mem_field = array();
			$uda_mem_up = &uda::factory('voa_uda_frontend_member_update');
			if (!$uda_mem_up->update($member, $this->request->getx(), $mem, $mem_field)) {
				$this->message('error', $uda_mem_up->error);
				return false;
			}

			$this->message('success', '编辑员工信息操作完毕', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('m_uid'=>$m_uid)), false);
		}

		$this->view->set('m_uid', $m_uid);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('m_uid'=>$m_uid)));
		$this->view->set('member', $member);
		$this->view->set('gender_list', $this->_uda_member_get->gender_list);
		$this->view->set('active_list', $this->_uda_member_get->active_list);
		$this->view->set('department_select', $this->_department_select('cd_id', $member['cd_id']));
		$this->view->set('job_list', $this->_job_list());

		$this->output('manage/member/edit_form');
	}

}

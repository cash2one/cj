<?php
/**
 * 展示分享的用户通讯录信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_addressbook_share extends voa_c_frontend_addressbook_base {

	public function _before_action($action) {

		$ts = $this->request->get('ts');
		$sig = $this->request->get('sig');
		$id = (int)$this->request->get('id');
		if ($sig == voa_h_func::sig_create($id, $ts)) {
			$this->_require_login = false;
		}

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$id = (int)$this->request->get('id');
		/** 读取分享字段 */
		$serv_ms = &service::factory('voa_s_oa_member_share');
		$share = $serv_ms->fetch_by_id($id);
		if (empty($share)) {
			$this->_error_message('不存在该分享或已被删除');
		}

		/** 读取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member');
		$mem = $serv_m->fetch_by_uid($share['m_uid']);
		if (empty($mem)) {
			$this->_error_message('该用户不存在或已被删除');
		}

		/** 取用户其他信息 */
		$serv_mf = &service::factory('voa_s_oa_member_field');
		$mem_field = $serv_mf->fetch_by_id($share['m_uid']);

		voa_h_user::push($mem);
		/** 部门 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');

		/** 职位 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');

		$this->view->set('member', $mem);
		$this->view->set('member_field', $mem_field);
		$this->view->set('department', $departments[$mem['cd_id']]);
		$this->view->set('job', $jobs[$mem['cj_id']]);
		$this->view->set('user', $mem);
		$this->view->set('navtitle', $this->_setting['sitename']);

		$this->_output('addressbook/share');
	}

}

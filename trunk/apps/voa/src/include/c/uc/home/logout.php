<?php
/**
 * voa_c_uc_home_logout
 * uc用户退出登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_home_logout extends voa_c_uc_home_base {

	public function execute() {

		// 清除cookie
		$this->_uc_auth_clear();

		$this->view->set('navtitle', '退出登录');

		$this->_success_message('', $this->_get_redirect_url(array()));
	}

}

<?php
/**
 * voa_c_cyadmin_auth_logout
 * 主站后台/认证/退出登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_auth_logout extends voa_c_cyadmin_base {

	public function execute() {

		// 如果之前保持登录则，记住登录用户名
		if ( !$this->session->getx('auth_remember') ) {
			$this->session->remove($this->_auth_cookie_names['username']);
		}

		$this->session->remove($this->_auth_cookie_names['skeycp']);

		$this->redirect($this->cpurl(''));

		return true;
	}

}

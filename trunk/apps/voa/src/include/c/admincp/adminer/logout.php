<?php
/**
 * voa_c_admincp_adminer_logout
 * 首页
 *
 * $Author$
 * $Id$
 */
class voa_c_admincp_adminer_logout extends voa_c_admincp_base {

	public function execute() {
		/** 如果之前保持登录则，记住登录用户名 */

		$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');

		$uda_adminer_update->adminer_logout($this->session);
		if (!$this->session->getx($this->_auth_cookie_names['adminer_remember'])) {
			$this->session->remove($this->_auth_cookie_names['username']);
		}
		//$this->session->remove($this->_auth_cookie_names['skeycp']);

		$this->redirect($this->cpurl('')); return;
	}
}

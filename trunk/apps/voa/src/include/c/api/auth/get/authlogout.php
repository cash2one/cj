<?php
/**
 * authlogout.php
 * 退出登录接口（清除cookie）
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_api_auth_get_authlogout extends voa_c_api_auth_base {

	public function execute() {

		foreach ($_COOKIE AS $_key => $_value) {
			$this->session->remove($_key);
		}

		return true;
	}

}

<?php
/**
 * voa_c_api_attachment_base
 * 附件基础控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_attachment_base extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		// 取后台登录信息
		$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
		// cookie 信息
		$cookie_data = array();
		$uda_member_get->adminer_auth_by_cookie($cookie_data, $this->session);
		if (!empty($cookie_data['uid']) && 0 < $cookie_data['uid']) {
			$this->_require_login = false;
		}

		return parent::_access_check();
	}

	protected function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}

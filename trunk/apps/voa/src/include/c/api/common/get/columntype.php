<?php
/**
 * voa_c_api_common_get_columntype
 * 获取配置列表
 * $Author$
 * $Id$
 */

class voa_c_api_common_get_columntype extends voa_c_api_common_abstract {


	protected function _before_action($action) {

		// 取后台登录信息
		$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
		// cookie 信息
		$cookie_data = array();
		$uda_member_get->adminer_auth_by_cookie($cookie_data, $this->session);
		if (!empty($cookie_data['uid']) && 0 < $cookie_data['uid']) {
			$this->_require_login = false;
		}

		return parent::_before_action($action);
	}

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_common_columntype');
		if (!$uda->list_all($this->_params, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}

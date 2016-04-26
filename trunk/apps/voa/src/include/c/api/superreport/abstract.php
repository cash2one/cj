<?php
/**
 * voa_c_api_superreport_abstract
 * 超级报表基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_superreport_abstract extends voa_c_api_base {
	// 插件id
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'superreport';
	// 表格名称
	protected $_tname = '';
	// uda's ptname
	protected $_ptname = array();

	protected $_setting = array();
	// 管理后台 cookie
	protected $_cookie_data = array();


	public function __construct() {

		parent::__construct();

	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		// 取后台登录信息
		$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
		// cookie 信息
		$uda_member_get->adminer_auth_by_cookie($this->_cookie_data, $this->session);
		if (!empty($this->_cookie_data['uid']) && 0 < $this->_cookie_data['uid']) {
			// 如果后台登陆信息存在, 则清理前台登陆账号
			$this->session->remove('uid');
			$this->_require_login = false;
		}

		return parent::_access_check();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 当前应用的设置信息
		$this->_plugin_setting = voa_h_cache::get_instance()->get('plugin.superreport.setting', 'oa');

		// 如果是普通用户
		if (0 < startup_env::get('wbs_uid') || (!$this->_require_login && empty($this->_cookie_data['uid']))) {
			return true;
		}

		// 取管理员配置
		$serv_common_adminer = &service::factory('voa_s_oa_common_adminer');
		if (!$adminer = $serv_common_adminer->fetch($this->_cookie_data['uid'])) {
			$this->_set_errcode(voa_errcode_oa_travel::PLEASE_LOGIN);
			$this->_output();
			return true;
		}

		// 取用户信息
		$serv_mem = &service::factory('voa_s_oa_member');
		if (!$this->_member = $serv_mem->fetch_by_mobilephone($adminer['ca_mobilephone'])) {
			$this->_set_errcode(voa_errcode_oa_travel::PLEASE_LOGIN);
			$this->_output();
			return true;
		}

		// 管理员标识
		$this->_is_admin = 1;

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);

		return true;
	}

}

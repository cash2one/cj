<?php
/**
 * voa_c_api_talk_abstract
 * 聊天基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_talk_abstract extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		startup_env::set('open_appid', config::get('voa.weixin.crm_appid'));
		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		startup_env::set('tv_uid', (int)$this->session->get('tv_uid'));

		return true;
	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		// 获取登录类型
		$logintype = $this->_get_logintype();
		switch ($logintype) {
			case 'qy':
				return $this->_access_check_qy();
				break;
			case 'mp':
				return $this->_access_check_mp();
				break;
		}

		return false;
	}

	// 判断登陆企业
	protected function _access_check_qy() {

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');

		// cookie 信息
		$cookie_data = array();
		if (!$uda_member_get->member_auth_by_cookie($cookie_data, $this->session)) {
			// 无法取得当前用户的cookie信息
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_NO_COOKIE);
		}

		if (empty($cookie_data)) {
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_NO_COOKIE);
		}

		$this->_member = array();
		if (!$uda_member_get->member_info_by_cookie($cookie_data['uid'], $cookie_data['auth'], $cookie_data['lastlogin'], $this->_member)) {
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_AUTH_ERROR);
		}

		startup_env::set('wbs_uid', $this->_member['m_uid']);
		startup_env::set('wbs_username', $this->_member['m_username']);

		return true;
	}

	// 判断登录公众号
	protected function _access_check_mp() {

		$uda = new voa_uda_frontend_mpuser_login();
		$uda->set_session($this->session);
		$uda->set_type(voa_uda_frontend_mpuser_login::TYPE_COOKIE);
		$result = array();
		if (!$uda->execute(array(), $result)) {
			return false;
		}

		$this->_member = $result['member'];
		startup_env::set('wbs_uid', $this->_member['mpuid']);
		startup_env::set('wbs_username', $this->_member['username']);
		startup_env::set('web_access_token', isset($this->_member['web_access_token']) ? $this->_member['web_access_token'] : '');
		startup_env::set('web_token_expires', isset($this->_member['web_token_expires']) ? $this->_member['web_token_expires'] : 0);

		return true;
	}

	// 获取登录类型
	protected function _get_logintype() {

		// 如果全局变量存在, 则
		if (startup_env::get('logintype')) {
			return startup_env::get('logintype');
		}

		// 平台标识
		$types = array('qy', 'mp');
		$logintype = $this->session->get('logintype');

		$cnames = array('travel', 'order');
		$controller_name = $this->route->get_controller();
		if (empty($logintype) || !in_array($logintype, $types)) {
			$logintype = in_array($controller_name, $cnames) ? 'mp': 'qy';
		}

		// 如果不在营销 CRM 控制层, 则为企业应用
		if (!in_array($controller_name, $cnames)) {
			$logintype = 'qy';
		}

		//$this->session->set('logintype', $logintype);
		startup_env::set('logintype', $logintype);

		return $logintype;
	}

}

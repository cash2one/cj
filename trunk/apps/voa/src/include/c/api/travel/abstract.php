<?php
/**
 * voa_c_api_travel_abstract
 * 商品基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_travel_abstract extends voa_c_api_base {
	// 插件id
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'travel';
	// 表格名称
	protected $_tname = '';
	// uda's ptname
	protected $_ptname = array();
	// 产品源数据标识
	const SRC_YES = 1;
	const SRC_NO = 2;
	// 是否管理员
	protected $_is_admin = 0;
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

		startup_env::set('open_appid', config::get('voa.weixin.crm_appid'));
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

		/** 读取站点配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_pluginname.'.setting', 'oa');

		// 如果是普通用户
		if (0 < startup_env::get('wbs_uid') || (!$this->_require_login && empty($this->_cookie_data['uid']))) {
			return true;
		}

		// 取管理员配置
		$serv_common_adminer = &service::factory('voa_s_oa_common_adminer');
		if (!$adminer = $serv_common_adminer->fetch($this->_cookie_data['uid'])) {
			$this->_set_errcode(voa_errcode_oa_travel::PLEASE_LOGIN);
			$this->_output();
			return false;
		}

		// 取用户信息
		$serv_mem = &service::factory('voa_s_oa_member');
		if (!$this->_member = $serv_mem->fetch_by_mobilephone($adminer['ca_mobilephone'])) {
			$this->_member = array(
				'm_uid' => 1,
				'm_username' => 'admin'
			);
		}

		// 管理员标识
		$this->_is_admin = 1;

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
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

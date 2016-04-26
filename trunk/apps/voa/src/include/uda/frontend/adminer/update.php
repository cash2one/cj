<?php
/**
 * update.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_adminer_update extends voa_uda_frontend_adminer_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 退出登录
	 * @return boolean
	 */
	public function adminer_logout($session) {
		foreach ($this->_cookie_names as $k) {
			//$session->remove($k);
			$session->set($k, '', -3600);
		}

		return true;
	}

	/**
	 * 用户登录
	 * @param number $ca_id 登录管理员的id
	 * @param array $cookie_names 定义cookie名
	 * + uid_cookie_name
	 * + lastlogin_cookie_name
	 * + auth_cookie_name
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的用户信息和cookie数据
	 * @param array $cookie_names COOKIE储存名，为空则使用系统自定名
	 * + uid_cookie_name ID储存名
	 * + lastlogin_cookie_name 最后登录时间储存名
	 * + auth_cookie_name 认证加密字符串储存名
	 * @return boolean
	 */
	public function adminer_login($ca_id, &$result = array(), $cookie_names = array()) {

		if (empty($cookie_names)) {
			$cookie_names = $this->_cookie_names;
		}

		// 获取管理员信息
		$adminer = array();
		$adminergroup = array();
		$uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');
		if (!$uda_adminer_get->adminer_by_uid($ca_id, $adminer, $adminergroup)) {
			return $this->set_errmsg(voa_errcode_oa_adminer::ADMINER_ID_NOT_EXISTS);
		}

		// 最后登录时间
		$lastlogin = startup_env::get('timestamp');
		// cookie认证加密字符串
		$auth = $this->adminer_generate_auth($adminer['ca_password'], $ca_id, $lastlogin);

		// 系统设置
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');

		// 输出管理员信息
		$adminer_data = array(
			'id' => $adminer['ca_id'],
			'mobilephone' => $adminer['ca_mobilephone'],
			'email' => $adminer['ca_email'],
			'username' => $adminer['ca_username'],
			'cag_id' => $adminer['cag_id'],
			'group' => array(
				'id' => $adminergroup['cag_id'],
				'title' => $adminergroup['cag_title'],
				'enable' => $adminergroup['cag_enable'],
				'role' => $adminergroup['cag_role'],
				'description' => $adminergroup['cag_description']
			),
			'locked' => $adminer['ca_locked'],
			'lastlogin' => $adminer['ca_lastlogin'],
			'lastloginip' => $adminer['ca_lastloginip'],
			'enterprise' => array(
				'domain' =>$settings['domain'],
				'ep_id' => $settings['ep_id'],
				'name' => $settings['sitename'],
				'corpid' => $settings['corp_id'],
				'enumber' => preg_replace('/'.preg_quote('.'.config::get('voa.oa_top_domain')).'$/is', '', $settings['domain'])
			),
		);

		$result = array(
			'auth' => array(
				array(
					'name' => $cookie_names['uid_cookie_name'],
					'value' => $ca_id,
				),
				array(
					'name' => $cookie_names['lastlogin_cookie_name'],
					'value' => $lastlogin,
				),
				array(
					'name' => $cookie_names['auth_cookie_name'],
					'value' => $auth,
				),
				array(
					'name' => 'ep_domain',
					'value' => $settings['domain']
				),
				array(
					'name' => $cookie_names['uname_cookie_name'],
					'value' => $adminer['ca_username']
				)

			),
			'data' => $adminer_data
		);

		return true;
	}

	/**
	 * 更改管理员密码
	 * @param number $ca_id
	 * @param string $new_password 新密码，可以是原文也可以是md5值
	 * @param string $is_original $new_password 是否为md5值
	 * @return boolean
	 */
	public function adminer_pwd_modify($ca_id, $new_password, $is_original = true) {

		// 储存在用户表的密码和盐值
		list($password, $salt) = voa_h_func::generate_password($new_password, '', $is_original, 6);

		$this->serv_common_adminer->update(array(
			'ca_password' => $password,
			'ca_salt' => $salt
		), $ca_id);

		return true;
	}

}

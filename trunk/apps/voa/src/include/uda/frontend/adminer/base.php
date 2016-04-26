<?php
/**
 * base.php
 * 后台管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_adminer_base extends voa_uda_frontend_base {

	public $serv_common_adminer = null;
	public $serv_common_adminergroup = null;

	/** cookie名定义 */
	protected $_cookie_names = array(
		'uid_cookie_name' => 'uid',
		'lastlogin_cookie_name' => 'lastlogin',
		'auth_cookie_name' => 'auth',
		'uname_cookie_name' => 'username'
	);

	public function __construct() {
		parent::__construct();
		if ($this->serv_common_adminer == null) {
			$this->serv_common_adminer = &service::factory('voa_s_oa_common_adminer');
			$this->serv_common_adminergroup = &service::factory('voa_s_oa_common_adminergroup');
		}

		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		list($domain_name) = explode('.', isset($setting['domain']) ? $setting['domain'] : $_SERVER['HTTP_HOST']);
		$this->_cookie_names = array(
			'uid_cookie_name' => $domain_name.'_uid',
			'lastlogin_cookie_name' => $domain_name.'_lastlogin',
			'auth_cookie_name' => $domain_name.'_auth',
			'uname_cookie_name' => $domain_name.'_username',
		);
	}

	/**
	 * 生成用于登录后台认证的cookie验证值
	 * @param string $password
	 * @param number $uid
	 * @param number $lastlogin
	 * @return string
	 */
	public function adminer_generate_auth($password, $uid, $lastlogin) {
		return md5($password."\t".$uid."\t".$lastlogin);
	}

}

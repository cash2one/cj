<?php
/**
 * get.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_adminer_get extends voa_uda_frontend_adminer_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 根据uid读取管理员信息
	 * @param number $uid
	 * @param array $adminer <strong style="color:red">(引用结果)</strong>管理员信息
	 * @param array $adminergroup <strong style="color:red">(引用结果)</strong>管理员所在管理组信息
	 * @return boolean
	 */
	public function adminer_by_uid($uid = 0, &$adminer, &$adminergroup = false) {

		$adminer = $this->serv_common_adminer->fetch($uid);
		if (empty($adminer)) {
			return false;
		}

		if ($adminergroup === false) {
			return true;
		}

		if (!$adminer['cag_id']) {
			return false;
		}

		$adminergroup = $this->serv_common_adminergroup->fetch($adminer['cag_id']);
		if (empty($adminergroup)) {
			return false;
		}

		return true;
	}

	/**
	 * 自cookie中读取管理员认证字符串信息
	 * @param array $cookie_data <strong style="color:red">（引用结果）</strong>cookie信息
	 * - uid 用户uid
	 * - auth 认证字符串
	 * - lastlogin 上次登录时间
	 * @param object $session session操作对象
	 * @param array $cookie_names cookie名定义
	 * 默认：array(
	 *  'uid_cookie_name' => 'uid',// 保存uid的cookie名
	 *  'lastlogin_cookie_name' => 'lastlogin',// 保存最后登录时间的cookie名
	 *  'auth_cookie_name' => 'auth'// 保存认证字符串的cookie名
	 * );
	 * @return boolean
	 */
	public function adminer_auth_by_cookie(&$cookie_data, $session, $cookie_names = array()) {

		if (empty($cookie_names)) {
			$cookie_names = $this->_cookie_names;
		}

		// 一些变态的意外处理
		if (is_array($_COOKIE)) {
			foreach ($_COOKIE as $key => $value) {
				$key = str_ireplace('Cookie:_', '', $key);
				$_COOKIE[$key] = $value;
			}
		}

		// uid非法
		$uid = (int)$session->get($cookie_names['uid_cookie_name']);
		if ($uid <= 0) {
			return false;
		}

		// 最后登录时间 - 登录时写入的
		$lastlogin = (int)$session->get($cookie_names['lastlogin_cookie_name']);
		// 如果超过一天未更新, 则需要重新登录
		if ($lastlogin + 86400 < startup_env::get('timestamp')) {
			return false;
		}

		// 认证字符串
		$auth = (string)$session->get($cookie_names['auth_cookie_name']);

		$cookie_data = array(
			'uid' => $uid,
			'lastlogin' => $lastlogin,
			'auth' => $auth
		);

		return true;
	}

	/**
	 * 自cookie中读取管理员信息
	 * @param number $uid
	 * @param string $auth
	 * @param number $lastlogin
	 * @param unknown $adminer
	 * @return boolean
	 */
	public function adminer_info_by_cookie($uid = 0, $auth = '', $lastlogin = 0, &$adminer, &$adminergroup = false) {

		// 获取当前管理员信息
		$adminer = array();
		$adminergroup = array();
		if (!$this->adminer_by_uid($uid, $adminer, $adminergroup)) {
			return false;
		}

		if ($this->adminer_generate_auth($adminer['ca_password'], $uid, $lastlogin) != $auth) {
			return false;
		}

		$session = &session::get_instance();
		$session->set($this->_cookie_names['lastlogin_cookie_name'], startup_env::get('timestamp'));
		$session->set($this->_cookie_names['auth_cookie_name'], $this->adminer_generate_auth($adminer['ca_password'], $uid, startup_env::get('timestamp')));
		//startup_env::set('wbs_uid', $adminer['m_uid']);
		//startup_env::set('wbs_username', $adminer['m_username']);

		return true;
	}

	/**
	 * 通过登录帐号（手机号、邮箱）获取管理表信息
	 * @param string $account 登录帐号（手机号、邮箱等）
	 * @param array $adminer <strong style="color:red">(引用结果)</strong> 用户信息
	 * @param string $adminergroup 是否读取用户扩展信息
	 * @return boolean
	 */
	public function adminer_by_account($account, &$adminer = array(), &$adminergroup = false) {

		$method = '';
		if (validator::is_email($account)) {
			$method = 'get_by_email';
		} elseif (validator::is_mobile($account)) {
			$method = 'get_by_mobilephone';
		} else {
			return $this->set_errmsg(voa_errcode_oa_adminer::ADMINER_ACCOUNT_ERROR);
		}

		$adminer = array();
		if (!($this->$method($account, $adminer))) {
			return $this->set_errmsg(voa_errcode_oa_adminer::ADMINER_ACCOUNT_NOT_EXISTS, rhtmlspecialchars($account));
		}

		// 用户被标记为删除状态
		if ($adminer['ca_status'] == voa_d_oa_common_adminer::STATUS_REMOVE) {
			return $this->set_errmsg(voa_errcode_oa_adminer::ADMINER_FORBID);
		}

		// 管理员被锁定
		if ($adminer['ca_locked'] == voa_d_oa_common_adminer::LOCKED_YES) {
			return $this->set_errmsg(voa_errcode_oa_adminer::ADMINER_LOCKED);
		}

		if ($adminergroup !== false) {
			// 读取所在管理组信息
			$adminergroup = $this->serv_common_adminergroup->fetch($adminer['cag_id']);
			if (empty($adminergroup)) {
				return $this->set_errmsg(voa_errcode_oa_adminer::ADMINERGROUP_NOT_EXISTS);
			}
			if ($adminergroup['cag_enable'] == voa_d_oa_common_adminergroup::ENABLE_NO) {
				return $this->set_errmsg(voa_errcode_oa_adminer::ADMINERGROUP_DISABLED);
			}
		}

		return true;
	}

	/**
	 * 通过email获取管理员信息
	 * @param string $email Email
	 * @param array $adminer <strong style="color:red">(引用返回)</strong>管理员信息
	 * @return boolean
	 */
	public function get_by_email($email = '', &$adminer = array()) {

		if (!($adminer = $this->serv_common_adminer->fetch_by_email($email))) {
			return false;
		}

		return true;
	}

	/**
	 * 通过手机号码获取管理员信息
	 * @param string $mobilephone 手机号码
	 * @param array $adminer <strong style="color:red">(引用返回)</strong>管理员信息
	 * @return boolean
	 */
	public function get_by_mobilephone($mobilephone = '', &$adminer = array()) {

		if (!($adminer = $this->serv_common_adminer->fetch_by_mobilephone($mobilephone))) {
			return false;
		}

		return true;
	}

}

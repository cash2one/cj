<?php
/**
 * wechatlogin.php
 * 使用微信unionid以及帐号、密码登录并进行绑定操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_auth_post_wechatlogin extends voa_c_api_auth_base {

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 登录帐号
			'account' => array('type' => 'string', 'required' => true),
			// 登录密码
			'password' => array('type' => 'string', 'required' => true),
			// 设备类型
			'device' => array('type' => 'string', 'required' => false),
			// 微信的unionid加密字符串
			'unionid' => array('type' => 'string', 'required' => true)
		);

		// 参数的基本校验
		$this->_check_params($fields);

		$this->_params['account'] = trim($this->_params['account']);

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');

		// 检查密码格式
		if (strlen($this->_params['password']) != 32) {
			$this->_set_errcode(voa_errcode_api_auth::AUTH_PASSWORD_NOT_MD5);
			return false;
		}

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');

		// 检查unionid
		$unionid = '';
		if (!$this->_params['unionid'] || !$uda_member_update->unionlogin_crypt($this->_params['unionid'], 'DECODE', $unionid)) {
			$this->_errcode = $uda_member_update->errcode;
			$this->_errmsg = $uda_member_update->errmsg;
			$this->_result = array();
			return false;
		}

		// 根据帐号进行登录
		// 用户信息
		$this->_member = array();
		if (!$uda_member_get->member_by_account($this->_params['account'], $this->_member, true)) {
			$this->_errcode = $uda_member_get->errcode;
			$this->_errmsg = $uda_member_get->errmsg;
			$this->_result = array();
			return false;
		}

		// 转换密码的md5值字符串为小写
		$this->_params['password'] = rstrtolower($this->_params['password']);
		// 根据用户储存的散列值来计算给定的密码储存值
		list($submit_password) = voa_h_func::generate_password($this->_params['password'], $this->_member['m_salt'], false);
		// 密码不正确
		if ($submit_password != $this->_member['m_password']) {
			return $this->_set_errcode(voa_errcode_api_auth::AUTH_PASSWORD_ERROR);
		}
		$uid = $this->_member['m_uid'];

		// 写入unionid的关联
		if (!$uda_member_update->bind_wechat($uid, $unionid)) {
			$this->_errcode = $uda_member_update->errcode;
			$this->_errmsg = $uda_member_update->errmsg;
			$this->_result = array();
			return false;
		}

		$result = array();
		// 进行登录，并写入cookie获取用户信息
		$cookie_names = array(
			'uid_cookie_name' => $this->_uid_cookie_name,
			'lastlogin_cookie_name' => $this->_lastlogin_cookie_name,
			'auth_cookie_name' => $this->_auth_cookie_name
		);
		if (!$uda_member_update->member_login($uid, $cookie_names, $this->_params['device'], $result)) {
			$this->_errcode = $uda_member_update->errcode;
			$this->_errmsg = $uda_member_update->errmsg;
			$this->_result = array();
			return false;
		}

		// 返回给客户端的数据
		$this->_result = $result;

		// 写入cookie
		$cookielife = 86400 * 7;
		foreach ($result['auth'] as $arr) {
			$this->session->set($arr['name'], $arr['value'], $cookielife);
		}

		return true;
	}

}

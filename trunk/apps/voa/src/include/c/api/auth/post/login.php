<?php
/**
 * login.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_auth_post_login extends voa_c_api_auth_base {

	/** 当前登录用户信息 */
	public $_member = array();

	public function execute() {

		// 需要的参数
		$fields = array(
			// 账号
			'account' => array('type' => 'string', 'required' => true),
			// 密码
			'password' => array('type' => 'string', 'required' => true),
			// 设备类型
			'device' => array('type' => 'string', 'required' => false)
		);

		// 基本验证检查
		$this->_check_params($fields);

		$this->_params['account'] = trim($this->_params['account']);

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');

		// 检查密码格式
		if (strlen($this->_params['password']) != 32) {
			$this->_set_errcode(voa_errcode_api_auth::AUTH_PASSWORD_NOT_MD5);
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

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');

		$result = array();
		if (!$uda_member_update->member_login($uid, $this->_params['device'], $result)) {
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

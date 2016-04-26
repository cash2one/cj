<?php
/**
 * login.php
 * uc登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_home_login extends voa_c_uc_home_base {

	protected $_uda_member = null;

	public function execute() {

		$this->view->set('navtitle', '登录 ');

		$this->_uda_member = &uda::factory('voa_uda_uc_member');

		if ($this->_is_post()) {
			$this->_submit_login();
			return true;
		}

		$this->_is_login();

		$this->output('uc/login');
	}

	/**
	 * 判断是否登录
	 * @return boolean
	 */
	protected function _is_login() {

		// 检查来自cookie的信息
		$auth = $this->_uc_auth($this->session->getx($this->_uc_auth_cookie_name));
		if ($auth['m_id'] > 0) {
			// 已经登录

			// 通过m_id找到用户信息
			$member = array();
			if (!$this->_uda_member->get_by_id($auth['m_id'], $member)) {
				return false;
			}
			$this->_login_success($member);
			return true;
		}
		return false;
	}

	/**
	 * 处理提交动作
	 */
	protected function _submit_login() {

		// 提交的登录帐号
		$account = (string)$this->request->post('account');
		// 提交的登录密码
		$password = (string)$this->request->post('password');

		if ($account == '' || $password == '') {
			// 帐号 或 密码为空
			return $this->_error_message(voa_errcode_uc_system::LOGIN_INPUT_NULL);
		}

		if (!preg_match('/^[0-9a-f]{32}/i', $password)) {
			// 密码非32位md5字符串
			return $this->_error_message(voa_errcode_uc_system::LOGIN_PASSWORD_NOT_MD5);
		}

		// 当前登录的用户信息
		$member = array();
		if (!$this->_uda_member->get_by_account($account, $member)) {
			// 未找到对应的用户信息
			return $this->_error_message($this->_uda_member->errcode.':'.$this->_uda_member->errmsg);
		}

		if (voa_h_func::generate_password($password, $member['m_salt'], false) != $member['m_password']) {
			// 密码验证错误
			return $this->_error_message(voa_errcode_uc_system::LOGIN_PASSWORD_ERROR);
		}

		return $this->_login_success($member);
	}

	/**
	 * 登录成功的动作
	 * @param array $member 用户信息
	 */
	protected function _login_success($member) {

		// 写入uc自身的cookie
		$this->_uc_auth(array(
			'm_id' => $member['m_id'],
			'm_password' => $member['m_password'],
			'time' => startup_env::get('timestamp')
		));

		// 更新登录记录
		$this->_uda_member->update_field($member['m_id'], array(
			'lastloginip' => $this->request->get_client_ip(),
			'lastlogin' => startup_env::get('timestamp')
		));

		$member_data = $this->_member2client($member);

		// 返回到客户端指定的页面
		return $this->_success_message('登录成功', $this->_get_redirect_url($member_data));
	}

}

<?php
/**
 * 处理密码相关
 * $Author$
 * $Id$
 */

class voa_c_frontend_member_password extends voa_c_frontend_base {

	public function execute() {
		/** 如果是登录操作 */
		if ($this->_is_post()) {
			/** 验证密码的合法性 */
			$passwd = trim($this->request->get('passwd'));
			$repasswd = trim($this->request->get('repasswd'));
			if ($passwd != $repasswd || 32 != strlen($passwd) || !preg_match("/^[0-9a-zA-Z]+$/i", $passwd)) {
				$this->_error_message('password_invalid', get_referer());
			}

			/** 存储密码 */
			$salt = random(6);
			$pw = $this->_generate_passwd($passwd, $salt);
			$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$serv->update(array(
				'm_passwd' => $pw,
				'm_salt' => $salt
			), array('m_uid' => startup_env::get('wbs_uid')));

			$this->_success_message('password_set_succeed');
		}

		$this->_output('member/password/set');
	}
}


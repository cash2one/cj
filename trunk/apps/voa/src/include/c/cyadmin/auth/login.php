<?php
/**
 * voa_c_cyadmin_auth_login
 * 主站后台/认证/登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_auth_login extends voa_c_cyadmin_base {

	public function execute() {
		if ($this->_is_post()) {
			// 如果是 post 操作, 则进行登陆判断

			$this->_login();
		} else {
			// 从 cookie 数据中判断

			if ($this->_is_login()) {
				$this->redirect($this->cpurl(''));
				return;
			}
		}

		// 自cookie判断用户当前是否记住用户名以及保持登录1周

		$adminer_remember = $this->session->getx($this->_auth_cookie_names['adminer_remember']);

		// 登录Url
		$this->view->set('form_action_url', isset($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : '');

		// 推送记住登录变量到模板
		$this->view->set('adminer_remember', $adminer_remember ? 1 : 0);

		// 推送记住的用户名变量到模板
		$this->view->set('adminer_username', $adminer_remember ? rhtmlspecialchars($this->session->getx($this->_auth_cookie_names['username'])) : '');

		$this->view->set('css_extend_files', array('style_login.css'));

		$this->output('cyadmin/auth/auth_login');
	}

	/**
	 * 登录操作
	 */
	protected function _login() {

		// 获取用户名/密码
		$username = $this->request->post('username');
		$passwd = $this->request->post('password');
		$adminer_remember = $this->request->post('adminer_remember') ? true : false;
		// 用户名/密码为空时
		if (empty($username) || empty($passwd)) {
			$this->view->set('err_msg', '登录帐号和密码不能为空');
			return;
		}

		// 判断登陆
		if ($this->_is_login($username, $passwd, $adminer_remember)) {
			$this->redirect($this->cpurl(''));
			return;
		}

		$this->view->set('err_msg', '登录帐号或密码错误');
	}

}

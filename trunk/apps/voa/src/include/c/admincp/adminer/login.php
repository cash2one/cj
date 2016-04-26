<?php

/**
 * 获取信息，登录/联合登录
 * Class voa_c_admincp_adminer_login
 */
class voa_c_admincp_adminer_login extends voa_c_admincp_base {

	private $_uda_adminer_get = null;

	// 密钥
	private $__state_secret_key = null;

	public function execute() {
		$this->_uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');
		$this->__state_secret_key = config::get('voa.rpc.client.auth_key');

		// 验证 快速登录
		$secret = $this->request->get('secret');
		if (!empty($secret)) {
			// 解码
			$secret_data = null;
			$this->_secretdata($secret, $secret_data);
			/*
			 * $secret_data 数组内容
			 * array (
			 *  '0' => ca_id
			 *  '1' => email
			 *  '2' => ep_id
			 *  '3' => timestamp 生成链接的时间
			 * );
			 */
			$this->_secret_login($secret_data);
		}

		// 普通登录
		if ($this->request->get('login') && $this->_referer) {
			// 通过get请求自动跳转登录
			$this->_login($this->request->get('account'), $this->request->get('password'));
			$this->output('adminer/login');
			return;
		}

		if ($this->_is_login()) {
			// 已登录
			$this->redirect($this->_referer ? $this->_referer : $this->cpurl(''));
			return;
		}

		$this->view->set('referer', rhtmlspecialchars($this->_referer));
		$action_url = isset($_SERVER['REQUEST_URI']) ? rhtmlspecialchars($_SERVER['REQUEST_URI']) : '';
		if ($this->_referer) {
			if ($action_url) {
				$action_url .= '?referer=' . rawurlencode($this->_referer);
			} else {
				$action_url = $this->_referer;
			}
		}

		if ($this->_is_post()) {
			// 处理提交登录
			$this->_login();
			$this->output('adminer/login');
			return;
		}

		// 提交的URL
		$this->view->set('action_url', isset($_SERVER['REQUEST_URI']) ? rhtmlspecialchars($_SERVER['REQUEST_URI']) : '');
		// 保存登录名的时间
		$this->view->set('adminer_remember', $this->session->getx($this->_cookie_remember_adminer_name));
		// 保存的登录名
		$this->view->set('adminer_username', rhtmlspecialchars($this->session->getx($this->_cookie_adminer_username_name)));

		$this->output('adminer/login');
	}

	/**
	 * 设置报错信息
	 * @param $code 带错代码
	 * @param $message 报错信息
	 * @return array
	 */
	private function errmsg($code, $message) {
		return array(
			'errcode' => (string)$code,
			'errmsg' => (string)$message
		);
	}

	/**
	 * 处理用户提交登录
	 */
	protected function _login($account = '', $password = '', $adminer_remember = 0) {

		if (!$account && !$password) {
			$account = (string)$this->request->post('account');
			$password = (string)$this->request->post('password');
			$adminer_remember = $this->request->post('adminer_remember') ? 1 : 0;
		}

		$this->view->set('adminer_remember', $adminer_remember);
		$this->view->set('adminer_username', rhtmlspecialchars($account));

		if (empty($account) || empty($password)) {
			$this->view->set('err_msg', '登录帐号和密码不能为空');
			return;
		}

		$adminer = array();
		$adminergroup = array();
		if (!$this->_uda_adminer_get->adminer_by_account($account, $adminer, $adminergroup)) {
			$this->view->set('err_msg', $this->_uda_adminer_get->errmsg . '[' . $this->_uda_adminer_get->errcode . ']');
			return;
		}

		if (empty($adminer)) {
			$this->view->set('err_msg', '登录帐号或登录密码错误[-101]');
			return;
		}

		// 转换密码的md5值字符串为小写
		$password = rstrtolower($password);
		// 根据用户储存的散列值来计算给定的密码储存值
		list($submit_password) = voa_h_func::generate_password($password, $adminer['ca_salt'], false);
		// 密码不正确
		if ($submit_password != $adminer['ca_password']) {
			$this->view->set('err_msg', '登录帐号或登录密码错误[-102]');
			return;
		}

		$result = array();
		$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');
		if (!$uda_adminer_update->adminer_login($adminer['ca_id'], $result)) {
			if ($uda_adminer_update->errcode) {
				$this->view->set('err_msg', $uda_adminer_update->errmsg . '[' . $uda_adminer_update->errcode . ']');
			} else {
				$this->view->set('err_msg', '读取登录信息发生错误');
			}
			return;
		}

		if (empty($result) || empty($result['data'])) {
			$this->view->set('err_msg', '帐号或者密码错误');
			return;
		}

		$adminer_remember = $this->request->post('adminer_remember') ? 1 : 0;

		$this->session->setx($this->_cookie_remember_adminer_name, $adminer_remember, 86400 * 365);

		if ($adminer_remember) {
			// 如果是记住登录名
			$this->session->setx($this->_cookie_adminer_username_name, $account, 86400 * 7);
		} else {
			$this->session->setx($this->_cookie_adminer_username_name, null, -3600);
			$this->session->remove($this->_cookie_adminer_username_name);
		}

		// 写入cookie
		foreach ($result['auth'] as $c) {
			$this->session->set($c['name'], $c['value']);
		}

		$url = '';
		if ($this->_referer) {
			$url = $this->_referer;
		} else {
			$url = $this->cpurl('');
		}
		$this->redirect($url);
		return;
	}

	/**
	 * 解密
	 * @param $secretdata 传入的数据
	 * @param $secret_merge 解密后的数据
	 */
	protected function _secretdata($secretdata, &$secret_merge) {
		$secretdata = rbase64_decode($secretdata);
		$secretdata = authcode($secretdata, $this->__state_secret_key, 'DECODE');
		$secret_merge = explode("`!`", $secretdata);
		// 判断密钥时间是否过期
		$nowtime = startup_env::get('timestamp');
		$remaintime = $nowtime - $secret_merge[3];
		if ($remaintime > 900) {
			$this->view->set('err_msg', '密钥时间过期[10008]');
			$this->output('adminer/login');
		}
	}

	/**
	 * 快速登录
	 * @param $secret 快速登录验证数据
	 */
	protected function _secret_login($secret) {

		if (empty($secret) ||
				empty($secret[2])) {
			$this->view->set('err_msg', '快速登录失败');
			return false;
		}

		if (validator::is_email($secret[0])) {
			$account = $secret[0];
		} elseif (validator::is_mobile($secret[1])) {
			$account = $secret[1];
		} else {
			$this->view->set('err_msg', '快速登录失败');
			return;
		}
		$adminer = array();
		$adminergroup = array();
		if (!$this->_uda_adminer_get->adminer_by_account($account, $adminer, $adminergroup)) {
			$this->view->set('err_msg', $this->_uda_adminer_get->errmsg . '[' . $this->_uda_adminer_get->errcode . ']');
			return;
		}

		if (empty($adminer)) {
			$this->view->set('err_msg', '快速登录失败');
			return;
		}

		if (empty($adminer['enterprise']['corpid']) != $secret[2]) {
			$this->view->set('err_msg', '快速登录失败');
			return;
		}

		$result = array();
		$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');
		if (!$uda_adminer_update->adminer_login($adminer['ca_id'], $result)) {
			if ($uda_adminer_update->errcode) {
				$this->view->set('err_msg', $uda_adminer_update->errmsg . '[' . $uda_adminer_update->errcode . ']');
			} else {
				$this->view->set('err_msg', '读取登录信息发生错误');
			}
			return;
		}

		if (empty($result) || empty($result['data'])) {
			$this->view->set('err_msg', '帐号或者密码错误');
			return;
		}

		// 写入cookie
		foreach ($result['auth'] as $c) {
			$this->session->set($c['name'], $c['value']);
		}

		$url = '';
		if ($this->_referer) {
			$url = $this->_referer;
		} else {
			$url = $this->cpurl('');
		}
		$this->redirect($url);
		return;
	}
}

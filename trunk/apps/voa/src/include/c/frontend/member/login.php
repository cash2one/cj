<?php
/**
 * voa_c_frontend_member_login
 * 用户登录（前台普通用户、后台管理员）
 * get/post
 * + $this->_return_callback_varname
 * + account 登录帐号（手机号、邮箱地址）
 * + password 登录密码（md5加密）
 * + type 登录类型。pc=前台用户、admincp=后台管理员。默认：admincp
 * + member_remember 是否记住登录名（后台）
 * $Author$
 * $Id$
 */
class voa_c_frontend_member_login extends voa_c_frontend_base {

	/**
	 * 外部请求的回调函数的变量名
	 * @var string
	 */
	protected $_return_callback_varname = 'login_result';

	/**
	 * 外部请求的回调函数名
	 * @var string
	 */
	protected $_return_callback_name = '';

	/** 当前提交的登录帐号 */
	protected $_account = '';
	/** 当前提交的登录密码 */
	protected $_password = '';
	/** 登录目标类型：pc（PC版）、admincp（后台），默认：admincp（登录后台）。 */
	protected $_login_type = 'admincp';

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		$is_submit = isset($_GET[$this->_return_callback_varname]) || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] && isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false);

		if ($is_submit) {

			// 用于jsonp的回调函数名
			if (isset($_GET[$this->_return_callback_varname]) && is_string($_GET[$this->_return_callback_varname])) {
				$this->_return_callback_name = rhtmlspecialchars($this->request->get($this->_return_callback_varname));
			}

			$this->_account = (string)$this->request->get('account');
			$this->_password = (string)$this->request->get('password');
			$this->_login_type = (string)$this->request->get('type');

			if (empty($this->_account) || empty($this->_password)) {
				return $this->_out_msg(4000, '登录帐号及登录密码不能为空');
			}

			// 转换密码的md5值字符串为小写
			$this->_password = rstrtolower($this->_password);

			switch ($this->_login_type) {
				case 'pc':
					$this->_response_pc_submit();
					break;
				default:
					$this->_response_admincp_submit();
			}

			return;
		}

		$this->_output('member/login');
	}

	/**
	 * 响应登录后台的提交请求
	 * @return boolean
	 */
	protected function _response_admincp_submit() {

		$adminer = array();
		$adminergroup = array();
		$uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');
		if (!$uda_adminer_get->adminer_by_account($this->_account, $adminer, $adminergroup)) {
			$this->_out_msg($uda_adminer_get->errcode, $uda_adminer_get->errmsg);
			return;
		}

		if (empty($adminer)) {
			$this->_out_msg(4101, '登录帐号或登录密码错误');
			return;
		}

		// 根据用户储存的散列值来计算给定的密码储存值
		list($submit_password) = voa_h_func::generate_password(rstrtolower($this->_password), $adminer['ca_salt'], false);
		// 密码不正确
		if ($submit_password != $adminer['ca_password']) {
			$this->_out_msg(4102, '登录帐号或登录密码错误');
			return;
		}

		$result = array();
		$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');
		if (!$uda_adminer_update->adminer_login($adminer['ca_id'], $result)) {
			if ($uda_adminer_update->errcode) {
				$this->_out_msg($uda_adminer_update->errcode, $uda_adminer_update->errmsg);
			} else {
				$this->_out_msg(4103, '读取登录信息发生错误');
			}
			return;
		}

		if (empty($result) || empty($result['data'])) {
			$this->_out_msg(4104, '帐号或者密码错误');
			return;
		}

		$cookie_adminer_username_name = 'admincp_remember';

		$adminer_remember = $this->request->post('adminer_remember') ? 1 : 0;

		$this->session->setx($cookie_adminer_username_name, $adminer_remember, 86400*365);

		if ($adminer_remember) {
			// 如果是记住登录名
			$this->session->setx($cookie_adminer_username_name, $this->_account, 86400*7);
		} else {
			$this->session->setx($cookie_adminer_username_name, null, -3600);
			$this->session->remove($cookie_adminer_username_name);
		}

		// 写入cookie
		foreach ($result['auth'] as $c) {
			$this->session->set($c['name'], $c['value']);
		}

		$this->_out_msg(0, 'OK', $result);
		return true;
	}

	/**
	 * 响应登录前台PC的提交请求
	 */
	protected function _response_pc_submit() {

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');
		// 用户信息
		$member = array();
		// 根据帐号进行登录
		if (!$uda_member_get->member_by_account($this->_account, $member, true)) {
			return $this->_out_msg($uda_member_get->errcode, $uda_member_get->errmsg.'('.$this->_account.')');
		}

		// 转换密码的md5值字符串为小写
		$this->_password = rstrtolower($this->_password);
		// 根据用户储存的散列值来计算给定的密码储存值
		list($submit_password) = voa_h_func::generate_password($this->_password, $member['m_salt'], false);
		// 密码不正确
		if ($submit_password != $member['m_password']) {
			return $this->_out_msg(4005, '登录帐号或者密码不正确');
		}
		$uid = $member['m_uid'];

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');

		$result = array();
		if (!$uda_member_update->member_login($uid, XingeApp::DEVICE_BROWSER, $result)) {
			return $this->_out_msg($uda_member_update->errcode, $uda_member_update->errmsg);
		}

		// 写入cookie
		foreach ($result['auth'] as $arr) {
			$this->session->set($arr['name'], $arr['value']);
		}

		$this->_out_msg(0, 'OK', $result);
	}

	/**
	 * 输出消息
	 * @param unknown $errcode
	 * @param unknown $errmsg
	 * @param unknown $result
	 */
	protected function _out_msg($errcode, $errmsg, $result = array()) {
		$this->errcode = $errcode;
		$this->errmsg = $errmsg;
		$this->result = $result;

		$this->_login_output();
		exit;
	}

	/**
	 * 输出 json 格式数据
	 * @param mixed $output_data
	 * @param array $result
	 */
	protected function _login_output($output_data = null) {
		if ($output_data !== null) {
			if ($this->_return_callback_name) {
				echo $this->_return_callback_name.'('.$output_data.')';
			} else {
				echo $output_data;
			}
			exit;
		}

		if (!$this->_return_callback_name) {
			@header("Content-type: application/json;charset=utf-8");
		} else {
			//@header("Content-type: application/json;charset=utf-8");
		}
		$data = array(
			'errcode' => $this->errcode,
			'errmsg' => $this->errmsg,
			'result' => $this->result
		);
		if ($this->_return_callback_name) {
			echo $this->_return_callback_name.'('.rjson_encode($data).')';
		} else {
			echo rjson_encode($data);
		}
		exit;
	}

}

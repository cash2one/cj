<?php
/**
 * voa_c_uc_home_register
 * UC用户注册
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_home_register extends voa_c_uc_home_base {

	protected $_uda_member = null;

	protected $_member = array();

	/** 短信验证码有效期 */
	protected $_smscode_expire = 600;

	public function execute() {

		$this->view->set('navtitle', '注册');

		$action = $this->request->get('action');
		$this->_uda_member = &uda::factory('voa_uda_uc_member');

		if ($action == 'sendsms') {
			// 发送短信验证码
			$this->_sendsms($this->request->get('mobilephone'));
			return;
		} elseif ($action == 'validator_email') {
			// 验证email
			$this->_validator_email($this->request->get('email'));
			return;
		} elseif ($action == 'validator_realname') {
			// 验证真实姓名
			$this->_validator_realname($this->request->get('mobilephone'));
			return;
		} else {
			// 处理页面注册业务
			if ($this->_is_post()) {
				$this->_submit_register();
				return true;
			}
			$this->output('uc/register');
		}
	}

	/**
	 * 提交注册
	 * @return boolean
	 */
	protected function _submit_register() {

		// 接收提交的数据
		$mobilephone = $this->request->post('mobilephone');
		$email = $this->request->post('email');
		$realname = $this->request->post('realname');
		$password = $this->request->post('password');
		$smscode = $this->request->post('smscode');

		// 检查手机号码填写
		if (!$this->_validator_mobilephone($mobilephone)) {
			return false;
		}

		// 检查email填写
		if (!$this->_validator_email($email)) {
			return false;
		}

		// 检查真实姓名填写
		if (!$this->_validator_realname($realname)) {
			return false;
		}

		// 检查手机短信验证码
		$smscode = (string)$smscode;
		$smscode = trim($smscode);
		$uda_smscode_get = &uda::factory('voa_uda_uc_smscode_get');
		if (!$uda_smscode_get->validator($mobilephone, $smscode, $this->_smscode_expire)) {
			$this->_error_message($uda_smscode_get->errcode.':'.$uda_smscode_get->errmsg);
			return false;
		}

		// 检查密码格式（前端传入需进行md5加密）
		if (!preg_match('/^[0-9a-f]{32}$/', $password)) {
			$this->_error_message(voa_errcode_uc_member::PASSWORD_IS_NOT_MD5);
		}

		// 用户提交的注册信息
		$submit = array(
			'mobilephone' => $mobilephone,
			'email' => $email,
			'realname' => $realname,
			'password' => $password
		);

		// 新注册的用户数据
		$member = array();
		// 写入用户数据
		if ($this->_uda_member->new_member($submit, $member)) {

			// 写入uc自身的cookie
			$this->_uc_auth(array(
				'm_id' => $member['m_id'],
				'm_password' => $member['m_password'],
				'time' => startup_env::get('timestamp')
			));
			return $this->_success_message('注册成功', $this->_get_redirect_url($this->_member2client($member)));
		} else {
			return $this->_error_message($this->_uda_member->errcode.':'.$this->_uda_member->errmsg);
		}
	}

	/**
	 * 发送短信验证码
	 * @param string $mobilephone
	 * @return boolean
	 */
	protected function _sendsms($mobilephone) {

		// 检查手机号
		if (!$this->_validator_mobilephone($mobilephone)) {
			return false;
		}
		$uda_smscode_insert = &uda::factory('voa_uda_uc_smscode_insert');
		if (!$uda_smscode_insert->send($mobilephone, '', $this->_smscode_expire, null)) {
			$this->_error_message($uda_smscode_insert->errcode.':'.$uda_smscode_insert->errmsg);
		}

		return true;
	}

	/**
	 * 验证手机号码合法性
	 * @param string $mobilephone
	 * @return boolean
	 */
	protected function _validator_mobilephone($mobilephone) {
		if (!$this->_uda_member->validator_mobilephone($mobilephone, 0)) {
			$this->_error_message($this->_uda_member->errcode.':'.$this->_uda_member->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 验证email可用性
	 * @param string $email
	 * @return boolean
	 */
	protected function _validator_email($email) {
		if (!$this->_uda_member->validator_email($email, 0)) {
			$this->_error_message($this->_uda_member->errcode.':'.$this->_uda_member->errmsg);
			return false;
		}

		return true;
	}

	/**
	 * 验证真实姓名合法性
	 * @return boolean
	 */
	protected function _validator_realname($realname) {
		if (!$this->_uda_member->validator_realname($realname)) {
			$this->_error_message($this->_uda_member->errcode.':'.$this->_uda_member->errmsg);
			return false;
		}

		return true;
	}

}

<?php
/**
 * pwd
 * 重置密码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_pwd extends voa_c_uc_api_base {

	/** 企业基本信息 */
	private $_enterprise = array();

	private $_serv_member2enterprise = null;

	public function execute() {

		// 可接受的参数
		$fields = array(
			// 企业号
			'enumber' => array('type' => 'string', 'required' => true),
			// 用户帐号
			'account' => array('type' => 'string', 'required' => true),
			// 短信验证码
			'smscode' => array('type' => 'string', 'required' => false),
			// 新密码
			'newpw' => array('type' => 'string', 'required' => false),
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		$this->_params['enumber'] = trim($this->_params['enumber']);
		$this->_params['account'] = trim($this->_params['account']);
		$this->_params['smscode'] = trim($this->_params['smscode']);
		$this->_params['newpw'] = trim($this->_params['newpw']);

		// 检查企业号
		$uda_enterprise = &uda::factory('voa_uda_uc_enterprise');
		if (!$uda_enterprise->check_enumber($this->_params['enumber'], $this->_enterprise)) {
			$this->errcode = $uda_enterprise->errcode;
			$this->errmsg = $uda_enterprise->errmsg;
			$this->result = array();
			return false;
		}

		$this->_serv_member2enterprise = &service::factory('voa_s_uc_member2enterprise');

		if (validator::is_email($this->_params['account'])) {
			// 使用 Email 找回

			return $this->_get_by_email();

		} elseif (validator::is_mobile($this->_params['account'])) {
			// 使用手机号找回

			return $this->_get_by_mobilephone();

		} else {
			// 未知的帐号类型
			$this->_set_errcode(voa_errcode_uc_pwd::PWD_ACCOUNT_UNKNOW);
			return false;
		}

		return true;
	}

	/**
	 * 通过 email 找回密码
	 * @return boolean
	 */
	private function _get_by_email() {

		// 找到当前 email 对应的帐号信息
		$member = $this->_serv_member2enterprise->fetch_by_email_ep_id($this->_params['account'], $this->_enterprise['ep_id']);
		if (empty($member)) {
			$this->_set_errcode(voa_errcode_uc_pwd::PWD_EMAIL_NOT_EXISTS, $this->_enterprise['ep_id'].''.$this->_params['account']);
			return false;
		}

		$uda_member2enterprise = &uda::factory('voa_uda_uc_member2enterprise_base');
		$pwdreset_url = '';
		$uda_member2enterprise->pwdreset_url($this->_enterprise['ep_enumber'], $this->_params['account'], $pwdreset_url);
		$expire = rgmdate(startup_env::get('timestamp') + $uda_member2enterprise->pwdreset_expire, 'Y-m-d H:i');

		// 发送密码重置邮件
		$uda_mailclound = &uda::factory('voa_uda_uc_mailcloud_insert');
		$uda_mailclound->send_pwdreset_mail($member['mep_email'], '['.$this->_enterprise['ep_name'].']用户重置登录密码', array(
			'%ename%' => array($this->_enterprise['ep_name']),
			'%mobilephone%' => array(preg_replace('/^(\d{3})([0-9]{4})(\d{4})$/', '\1****\3', $member['mep_mobilephone'])),
			'%reseturl%' => array($pwdreset_url),
			'%expire%' => array($expire)
		));

		return true;
	}

	/**
	 * 通过手机号找回密码
	 * @return boolean
	 */
	private function _get_by_mobilephone() {

		$smscode = $this->_params['smscode'];
		$password = $this->_params['newpw'];

		if (!validator::is_md5($password)) {
			// 密码格式不正确，非md5值
			$this->_set_errcode(voa_errcode_uc_pwd::PWD_PASSWORD_FORMAT_ERROR, $password);
			return false;
		}

		// 验证码有效期
		$set_expire_second = config::get('voa.smscode_send_expire');

		// 当前手机号
		$mobilephone = $this->_params['account'];

		// 验证短信验证码合法性
		$uda_smscode_get = &uda::factory('voa_uda_uc_smscode_get');
		if (!$uda_smscode_get->validator($mobilephone, $smscode, $set_expire_second)) {
			$this->errcode = $uda_smscode_get->errno;
			$this->errmsg = $uda_smscode_get->error;
			$this->result = array();
			return false;
		}

		// 连接站点更改密码
		$uda_base = &uda::factory('voa_uda_uc_base');
		$classname = 'member';
		$method = 'pwdmodify';
		$args = array(
			'password' => $password,
			'account' => $this->_params['account']
		);
		$host_ip = '';
		$oa_result = array();
		if (!$uda_base->rpc_call($this->_enterprise['ep_domain'], $classname, $method, $args, $host_ip, $oa_result)) {
			$this->errcode = $uda_base->errcode;
			$this->errmsg = $uda_base->errmsg;
			return false;
		} else {
			$this->result = $oa_result;
		}

		return true;
	}

}

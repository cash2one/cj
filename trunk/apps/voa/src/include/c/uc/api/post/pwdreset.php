<?php
/**
 * pwdreset
 * 重置密码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_pwdreset extends voa_c_uc_api_base {

	/** 企业基本信息 */
	private $_enterprise = array();

	private $_serv_member2enterprise = null;

	public function execute() {

		// 可接受的参数
		$fields = array(
			// 企业号
			'key' => array('type' => 'string', 'required' => true),
			// 用户帐号
			'hash' => array('type' => 'string', 'required' => true),
			// 新密码
			'newpw' => array('type' => 'string', 'required' => false),
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		// 验证key和hash
		$uda_member2enterprise_base = &uda::factory('voa_uda_uc_member2enterprise_base');
		$account_data = array();
		if (!$uda_member2enterprise_base->pwdreset_validator($this->_params['key'], $this->_params['hash'], $account_data)) {
			$this->errcode = $uda_member2enterprise_base->errcode;
			$this->errmsg = $uda_member2enterprise_base->errmsg;
			$this->result = array();
			return false;
		}

		// 检查企业号
		$uda_enterprise = &uda::factory('voa_uda_uc_enterprise');
		if (!$uda_enterprise->check_enumber($account_data['enumber'], $this->_enterprise)) {
			$this->errcode = $uda_enterprise->errcode;
			$this->errmsg = $uda_enterprise->errmsg;
			$this->result = array();
			return false;
		}

		$password = $this->_params['newpw'];
		$account = $account_data['account'];

		if (!validator::is_md5($password)) {
			// 密码格式不正确，非md5值
			$this->_set_errcode(voa_errcode_uc_pwd::PWD_PASSWORD_FORMAT_ERROR, $password);
			return false;
		}

		// 连接站点更改密码
		$uda_base = &uda::factory('voa_uda_uc_base');
		$classname = 'member';
		$method = 'pwdmodify';
		$args = array(
			'password' => $password,
			'account' => $account
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

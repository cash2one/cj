<?php
/**
 * voa_c_uc_api_post_mobileverify
 * 获取手机号短信验证码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_mobileverify extends voa_c_uc_api_base {

	public function execute() {

		// 可接受的参数
		$fields = array(
			// 注册人手机号
			'mobilephone' => array('type' => 'string', 'required' => true),
			// IP 信息加密字符串，使用在极其特殊的情况，比如利用官网代理进行的发送（因为直接链接会计算服务器的IP）
			'ipinfo' => array('type' => 'string', 'required' => false),
			// 短信类型. oaresetpwd\register\pwdreset
			'action' => array('type' => 'string', 'required' => false)
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		if (!$this->_params['mobilephone'] || !validator::is_mobile($this->_params['mobilephone'])) {
			return $this->_set_errcode(voa_errcode_uc_system::UC_MOBILE_ERROR, $this->_params['mobilephone']);
		}

		if (!in_array($this->_params['action'], array('register', 'pwdreset', 'oaresetpwd'))) {
			$this->_params['action'] = 'register';
		}

		if ($this->_params['action'] == 'register') {
			$serv_enterprise = &service::factory('voa_s_uc_enterprise');
			if ($serv_enterprise->count_by_field_not_in_ep_id('ep_adminmobilephone', $this->_params['mobilephone'], 0) > 0) {
				return $this->_set_errcode(voa_errcode_uc_system::UC_MOBILE_EXISTS, $this->_params['mobilephone']);
			}
		}

		// 短信验证码信息文字
		$msg = '验证码：%seccode%，请在%expire%之内完成操作。（如非本人操作，请忽略本短信）';
		// 验证码有效期
		$set_expire_second = config::get('voa.smscode_send_expire');
		// 发送手机验证码短信
		$uda_smscode_insert = &uda::factory('voa_uda_uc_smscode_insert');
		if (!$uda_smscode_insert->send($this->_params['mobilephone'], $msg, $set_expire_second, $this->_params['ipinfo'])) {
			$this->errcode = $uda_smscode_insert->errno;
			$this->errmsg = $uda_smscode_insert->error;
			return false;
		}

		$this->result = array();

		return;
	}

}

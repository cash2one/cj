<?php
/**
 * smscodeverify.php
 * 短信验证码校验接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_smscodeverify extends voa_c_uc_api_base {

	public function execute() {

		// 可接受的参数
		$fields = array(
			// 手机号
			'mobilephone' => array('type' => 'string', 'required' => true),
			// 输入的验证码
			'smscode' => array('type' => 'string', 'required' => true),
		);

		// 基本参数检查和过滤
		$this->_check_params($fields);

		// 验证码有效期
		$set_expire_second = config::get('voa.smscode_send_expire');

		// 验证短信验证码合法性
		$uda_smscode_get = &uda::factory('voa_uda_uc_smscode_get');
		if (!$uda_smscode_get->validator($this->_params['mobilephone'], $this->_params['smscode'], $set_expire_second)) {
			$this->errcode = $uda_smscode_get->errno;
			$this->errmsg = $uda_smscode_get->error;
			$this->result = array();
			return false;
		}

		// 验证有效，返回一组加密字符串
		$crypt_xxtea = new crypt_xxtea($this->_auth_key);
		$smsauth = $crypt_xxtea->encrypt($this->_params['mobilephone']);

		// 结果数据
		$this->result['mobilephone'] = $this->_params['mobilephone'];
		$this->result['smsauth'] = rbase64_encode($smsauth);

		return true;
	}

}

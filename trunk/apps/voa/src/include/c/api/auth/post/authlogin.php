<?php
/**
 * voa_c_api_auth_post_authlogin
 * 确认登录, 改变登录状态
 * Created by zhoutao.
 * Created Time: 2015/7/6  21:59
 */

class voa_c_api_auth_post_authlogin extends voa_c_api_auth_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute() {

		$postx = $this->request->postx();

		$singture = md5($postx['authcode'] . $postx['timestamp'] . config::get('voa.auth_key'));

		if ($singture != $postx['singture']) {
			// 验证失败，密钥错误
			$this->_errcode = '10002';
			$this->_errmsg = '验证失败，密钥错误';
			return true;
		}

		// 验证通过，更新状态
		$uda = &uda::factory('voa_uda_frontend_auth_update');
		$data = array(
			'state' => (int)2,
			'authcode' => (string)$postx['authcode']
		);
		$out = null;
		$uda->login_update($data, $out);

		$this->_result = '成功登录';
		return true;
	}

}

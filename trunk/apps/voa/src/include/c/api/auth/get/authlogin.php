<?php
/**
 * voa_c_api_auth_get_authlogin
 * 检查登录状态 接口
 * Created by zhoutao.
 * Created Time: 2015/7/5  9:36
 */

class voa_c_api_auth_get_authlogin extends voa_c_api_auth_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute () {

		$getx = $this->request->getx();

		$singture = md5($getx['authcode'] . $getx['timestamp'] . config::get('voa.auth_key'));

		if ($singture != $getx['singture']) {
			// 验证失败，密钥错误
			$this->_errcode = '10002';
			$this->_errmsg = '验证失败，密钥错误';
			return true;
		}

		// 验证通过，检查状态
		$uda = &uda::factory('voa_uda_frontend_auth_get');
		$data = array(
			'authcode' => (string)$getx['authcode']
		);
		$out = null;
		$uda->get_state($data, $out);

		// 已登录，返回cookie
		if ($out['state'] == 2) {
			// 根据authcode 获取m_uid
			$uda_m_uid = &uda::factory('voa_uda_frontend_auth_get');
			$m_uid = null;
			$uda_m_uid->get_m_uid($getx['authcode'], $m_uid);

			// cookie信息
			$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
			$result = null;
			$cookie_names = array(
				'uid_cookie_name' => $this->_uid_cookie_name,
				'lastlogin_cookie_name' => $this->_lastlogin_cookie_name,
				'auth_cookie_name' => $this->_auth_cookie_name
			);
			$uda_member_update->member_login($m_uid, '', $result, $cookie_names);
			$this->_result = $result;

			// 写入cookie
			$cookielife = 86400 * 7;
			foreach ($result['auth'] as $arr) {
				$this->session->set($arr['name'], $arr['value'], $cookielife);
			}

			return true;
		}
		// 仅扫描，没有确定登录
		if ($out['state'] == 1) {
			$this->_errcode = '10001';
			$this->_errmsg = '没有确定登录,请扫描完二维码后确定登录';
			return true;
		}
		// 登录错误
		if ($out['state'] == 0) {
			$this->_errcode = '10000';
			$this->_errmsg = '登录发生错误，请获取二维码并扫描登录';
			return true;
		}

		return true;
	}

}

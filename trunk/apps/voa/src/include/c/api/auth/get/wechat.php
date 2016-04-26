<?php
/**
 * wechat.php
 * 通过微信code获取用户信息，无绑定微信的则返回微信的unionid加密字符串
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_auth_get_wechat extends voa_c_api_auth_base {

	protected $_wechat = null;

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 微信的code
			'code' => array('type' => 'string', 'required' => true),
			// 设备类型
			'device' => array('type' => 'number', 'required' => false)
		);

		// 参数数值基本检查验证
		$this->_check_params($fields);

		if (!$this->_params['code']) {
			return $this->_set_errcode(voa_errcode_api_auth::WECHAT_CODE_NULL);
		}

		// 加载微信登录类
		// 模拟app移动应用来请求
		$wechat = new voa_wechat_login('app');

		// 获取微信token
		$token_info = $wechat->get_access_token($this->_params['code']);
		if (!$token_info) {
			$this->_errcode = $wechat->errcode;
			$this->_errmsg = $wechat->errmsg;
			return false;
		}
		$access_token = $token_info['access_token'];
		$openid = $token_info['openid'];

		// 获取微信用户信息
		$wechat_userinfo = $wechat->get_userinfo_for_unionid($access_token, $openid);
		if (!$wechat_userinfo) {
			$this->_errcode = $wechat->errcode;
			$this->_errmsg = $wechat->errmsg;
			return false;
		}

		// 微信用户unionid
		$unionid = $wechat_userinfo['unionid'];

		// 使用unionid获取企业微信用户信息
		$member = array();
		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');
		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		if (!$uda_member_get->member_by_unionid($unionid, $member)) {
			// 不存在关联此unionid的用户，则返回unionid加密

			// 加密unionid字符串
			$unionid_encode = '';
			if (!$uda_member_update->unionlogin_crypt($unionid, 'ENCODE', $unionid_encode)) {
				$this->_errcode = $uda_member_update->errcode;
				$this->_errmsg = $uda_member_update->errmsg;
				echo "test";
				return false;
			}

			// 返回给客户端的结果
			$this->_result = array(
				'auth' => array(),
				'data' => array(),
				'unionid' => $unionid_encode
			);

		} else {
			// 存在关联用户，则返回用户信息并登录

			$result = array();
			$cookie_names = array(
				'uid_cookie_name' => $this->_uid_cookie_name,
				'lastlogin_cookie_name' => $this->_lastlogin_cookie_name,
				'auth_cookie_name' => $this->_auth_cookie_name
			);
			if (!$uda_member_update->member_login($member['m_uid'], $cookie_names, $this->_params['device'], $result)) {
				$this->_errcode = $uda_member_update->errcode;
				$this->_errmsg = $uda_member_update->errmsg;
				$this->_result = array();
				return false;
			}

			// 返回给客户端的数据
			$this->_result = $result;
			$this->_result['unionid'] = '';

			// 写入cookie
			$cookielife = 86400 * 7;
			foreach ($result['auth'] as $arr) {
				$this->session->set($arr['name'], $arr['value'], $cookielife);
			}

			return true;
		}

	}

}

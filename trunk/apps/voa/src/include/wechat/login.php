<?php
/**
 * voa_wechat_login
 * 微信登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_wechat_login extends voa_wechat_base {

	public function __construct($app_type = 'site') {
		parent::__construct($app_type);
	}

	/**
	 * 返回登录的二维码扫描页用于获取CODE
	 * 返回二维码页的URL，供前端进行跳转
	 * @param string $state
	 * @param string $redirect_uri
	 * @param string $scope
	 * @return string
	 */
	public function get_code_from_qrcode_url($redirect_uri, $scope = 'snsapi_login') {

		$params = array();
		$params['redirect_uri'] = $redirect_uri;
		$params['response_type'] = 'code';
		$params['scope'] = $scope;
		$params['state'] = $this->_state(false);
		$qrcode_connect_url = $this->_api_url(self::GET_CODE_URL, $params);

		return $qrcode_connect_url;
	}

	/**
	 * 自页面url通过_GET接收CODE数据
	 * @return false=失败,null=用户未授权,string=code
	 */
	public function get_code() {
		if (!isset($_GET['state']) || !is_scalar($_GET['state'])) {
			// 没有传递state
			return $this->_set_errcode(voa_errcode_wechat_login::WECHAT_LOGIN_STATE_NULL);
		}
		if (!$this->_state($_GET['state'])) {
			// state 校验失败
			return false;
		}
		if (!isset($_GET['code']) || !is_scalar($_GET['code'])) {
			// 未传递code，可能用户未授权
			return null;
		}

		return trim($_GET['code']);
	}

	/**
	 * 获取token值
	 * @param string $code
	 * @return false|array
	 */
	public function get_access_token($code, $grant_type = 'authorization_code') {

		$params = array();
		$params['code'] = $code;
		$params['appid'] = $this->_appid;
		$params['secret'] = $this->_appsecret;
		$params['grant_type'] = $grant_type;
		$url = $this->_api_url(parent::GET_ACCESS_TOKEN_URL, $params);

		$result = array();
		if (!voa_h_func::get_json_by_post_and_header($result, $url, $params, array(), 'GET')) {
			return $this->_set_errcode(voa_errcode_wechat_login::WECHAT_LOGIN_GET_ACCESS_TOKEN_FAILED);
		}

		if (isset($result['errcode']) && $result['errcode'] != 0) {
			// 请求微信发生错误
			return $this->_set_errcode(voa_errcode_wechat_login::WECHAT_LOGIN_GET_ACCESS_TOKEN_ERROR, $result['errcode']);
		}

		return array(
			'access_token' => $result['access_token'],
			'expires_in' => $result['expires_in'],
			'openid' => $result['openid'],
			'scope' => $result['scope']
		);

	}

	/**
	 * 使用微信unionid机制获取微信用户信息
	 * @param string $access_token
	 * @param string $openid
	 * @return array
	 */
	public function get_userinfo_for_unionid($access_token = '', $openid = '') {

		$params = array();
		$params['access_token'] = $access_token;
		$params['openid'] = $openid;
		$url = $this->_api_url(parent::GET_UNIOINID_URL, $params);

		$result = array();
		if (!voa_h_func::get_json_by_post_and_header($result, $url, $params, array(), 'GET')) {
			return $this->_set_errcode(voa_errcode_wechat_login::WECHAT_LOGIN_GET_UNINID_URL_FAILED);
		}

		if (isset($result['errcode']) && $result['errcode'] != 0) {
			// 请求微信发生错误
			return $this->_set_errcode(voa_errcode_wechat_login::WECHAT_LOGIN_GET_UNIONID_URL_ERROR, $result['errcode']);
		}

		return array(
			'openid' => $result['openid'],
			'nickname' => $result['nickname'],
			'sex' => $result['sex'],
			'province' => $result['province'],
			'city' => $result['city'],
			'country' => $result['country'],
			'headimgurl' => $result['headimgurl'],
			'privilege' => $result['privilege'],
			'unionid' => $result['unionid']
		);
	}

}

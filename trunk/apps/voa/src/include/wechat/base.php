<?php
/**
 * voa_wechat_base
 * 微信开放平台基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_wechat_base {

	/** 获取临时授权 CODE 的URL（显示二维码） */
	const GET_CODE_URL = 'https://open.weixin.qq.com/connect/qrconnect?appid=%APPID%&redirect_uri=%REDIRECT_URI%&response_type=%response_type%&scope=%SCOPE%&state=%STATE%#wechat_redirect';

	/** 获取微信授权关系 token */
	const GET_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%APPID%&secret=%SECRET%&code=%CODE%&grant_type=%grant_type%';

	/** 获取微信用户信息 UnionID 机制，获取用户的unionid */
	const GET_UNIOINID_URL = 'https://api.weixin.qq.com/sns/userinfo?access_token=%ACCESS_TOKEN%&openid=%OPENID%';

	/** 微信开放平台 AppID，应用唯一标识 */
	protected $_appid = '';
	/** 微信开放平台 AppSecret */
	protected $_appsecret = '';
	/** 接口调用凭证 */
	protected $_access_token = '';

	public $errcode = '';
	public $errmsg = '';

	public function __construct($app_type = 'site') {
		$this->_appid = config::get('voa.wechat.'.$app_type.'_app_id');
		$this->_appsecret = config::get('voa.wechat.'.$app_type.'_app_secret');
	}

	/**
	 * 构造连接微信的URL
	 * @param string $url
	 * @param array $params url需要的参数值
	 * array(参数名=>值, ... ...)
	 * 具体参数名参见各个url的定义，不区分顺序和大小写
	 * @return string
	 */
	protected function _api_url($url, $params = array()) {
		$params['appid'] = $this->_appid;
		if (empty($params)) {
			// 没有参数则返回url原型
			return $url;
		}

		$keys = array_keys($params);
		foreach ($keys as &$v) {
			$v = '%'.$v.'%';
		}

		return str_ireplace($keys, array_values($params), $url);
	}

	/**
	 * 赋值 错误代码 和 错误消息
	 * @param string $str errcode::CY_OK
	 * @param mixed $params1 ... 变量值
	 * @uses _set_errcode(errcode:CY_TEST, 'aa', 'bb', 'cc');
	 * @return void
	 */
	protected function _set_errcode($str) {

		call_user_func_array("voa_h_func::set_errmsg", func_get_args());

		$this->errcode = voa_h_func::$errcode;
		$this->errmsg = voa_h_func::$errmsg;

		return $this->errcode ? false : true;
	}

	/**
	 * 生成/检查 用于本地校验的 state
	 * 简单加密用于微信推荐避免csrf攻击
	 * @param string $state false=生成state 否则验证state
	 * @return string|boolean
	 */
	protected function _state($state = false) {
		$key = md5('Afl@&*1afs%$^aa//a4');
		$timestamp = startup_env::get('timestamp');
		if ($state === false) {
			// 生成state
			return $timestamp.'_'.md5(md5($timestamp)."\t".$key);
		} else {
			// 验证state
			list($t, $s) = explode('_', $state);
			if ($timestamp - $t > 900) {
				return $this->_set_errcode(voa_errcode_wechat_base::WECHAT_BASE_STATE_TIMEOUT);
			}
			if ($s != md5(md5($t)."\t".$key)) {
				return $this->_set_errcode(voa_errcode_wechat_base::WECHAT_BASE_STATE_ERROR);
			}

			return true;
		}
	}

}

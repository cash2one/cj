<?php
/**
 * 微信接口基类
 * $Author$
 * $Id$
 */

class voa_weixin_base {
	// 获取公众号 access_token 的接口地址
	const TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
	// 获取网页授权 access_token 的接口地址
	const WEB_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
	// 刷新网页授权 access_token 的接口地址
	const REFRESH_WEB_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s';
	// 获取用户信息的接口地址
	const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=%s';
	// 关注者列表接口地址
	const SUBSCRIBE_URL = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s';
	// 授权链接
	const OAUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';
	// 获取短链接
	const SHORT_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=%s';
	// 发送模板消息
	const MSG_TEMPLATE = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s';
	// 当前公众号 open id
	protected $_open_id;
	// 接口配置中的 token
	protected $_token;
	// 公众帐号的 appid
	protected $_app_id;
	// 公众帐号的 appsecret
	protected $_app_secret;
	// access token
	protected $_access_token;
	// 原始 xml 信息(来自微信的)
	protected $_xml_from_wx;
	// 有效期, 时间戳超过该值, 则 access token 无效
	protected $_token_expires;
	// access token 错误码
	protected $_access_token_errcode = array();
	/**
	 * web access token 数据对象
	 * @param vo_weixin_web_token $web_token
	 */
	public $web_token;
	/**
	 * 用户信息数据结构
	 * @param vo_weixin_userinfo $userinfo
	 */
	public $userinfo;

	public function __construct() {

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_app_id = isset($sets['mp_appid']) ? $sets['mp_appid'] : '';
		$this->_app_secret = isset($sets['mp_appsecret']) ? $sets['mp_appsecret'] : '';
		$this->_token = isset($sets['mp_token']) ? $sets['mp_token'] : '';
		$this->_open_id = $this->_app_id;
		$this->_access_token_errcode = config::get('voa.weixin.access_token_errcode');
		$this->web_token = new stdClass();
	}

	// 检测来自微信请求的URL是否有效
	public function check_signature() {

		$c = controller_request::get_instance();
		$signature = $c->get("signature");
		$timestamp = $c->get("timestamp");
		$nonce = $c->get("nonce");

		$tmpArr = array($this->_token, (string)$timestamp, (string)$nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature){
			return true;
		} else {
			logger::error("check signature error:{$signature}\t{$timestamp}\t{$nonce}");
			return false;
		}
	}

	/**
	 * 获取短链接
	 * @param string $url 普通URL链接
	 */
	public function get_short_url($url) {

		// 获取 token
		if (!$this->get_access_token()) {
			return false;
		}

		// 获取 json 数据
		$post = array('action' => 'long2short', 'long_url' => $url);
		$post = rjson_encode($post);
		$data = array();
		if (!self::post($data, sprintf(self::SHORT_URL, $this->_access_token), $post)) {
			return false;
		}

		return empty($data['short_url']) ? '' : $data['short_url'];
	}

	/**
	 * 发送模板消息
	 * @param string $openid 接收者的openid
	 * @param string $tplid 模板消息id
	 * @param array $data 模板消息内容
	 * @param string $url 点击消息时, 转向的地址
	 * @return boolean
	 */
	public function send_tpl_msg($openid, $tplid, $data, $url) {

		// 获取 token
		if (!$this->get_access_token()) {
			return false;
		}

		// 模板消息参数
		$post = array('touser' => $openid, 'template_id' => $tplid, 'topcolor' => '#FF0000', 'data' => array());
		if (!empty($url)) {
			$post['url'] = $url;
		}

		// 重新拼凑具体的消息内容
		foreach ($data as $_k => $_v) {
			if (is_array($_v)) {
				$post['data'][$_k] = array(
					'value' => empty($_v['value']) ? '' : $_v['value'],
					'color' => empty($_v['color']) ? '#173177' : $_v['color']
				);
			} else {
				$post['data'][$_k] = array('value' => $_v, 'color' => '#173177');
			}
		}

		// 调用发送接口
		$post = rjson_encode($post);
		$data = array();
		if (!self::post($data, sprintf(self::MSG_TEMPLATE, $this->_access_token), $post)) {
			return false;
		}

		return 0 == $data['errcode'] ? true : false;
	}

	/**
	 * 获取 token
	 * @param boolean $force 强制重新获取 token
	 */
	public function get_access_token($force = false) {

		// 先从缓存中读取 token
		$sets = voa_h_cache::get_instance()->get('wxmp', 'oa');
		if (!$force && isset($sets['token_expires']) && $sets['token_expires'] >= startup_env::get('timestamp')) {
			$this->_access_token = $sets['access_token'];
			$this->_token_expires = $sets['token_expires'];
			return $this->_access_token;
		}

		$url = sprintf(self::TOKEN_URL, $this->_app_id, $this->_app_secret);
		// 获取 json 数据
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		// 如果返回了错误
		if (!isset($data['access_token'])) {
			logger::error('url:'.$url."\taccess token error:".http_build_query($data));
			return false;
		}

		$this->_access_token = $data['access_token'];
		$this->_token_expires = startup_env::get('timestamp') + ($data['expires_in'] * 0.8);
		// token 入库
		$serv = &service::factory('voa_s_oa_wxmp_setting', array('pluginid' => 0));
		$serv->update('access_token', array(
			'value' => $this->_access_token
		));

		$serv->update('token_expires', array(
			'value' => $this->_token_expires
		));

		// 更新 token 临时缓存
		voa_h_cache::get_instance()->get('wxmp', 'oa', true);
		return $this->_access_token;
	}

	// 获取网页授权的 token
	public function get_web_access_token() {

		$c = controller_request::get_instance();
		// 获取 access token 必须的参数
		$code = $c->get('code');
		if (empty($code)) {
			return false;
		}

		$url = sprintf(self::WEB_TOKEN_URL, $this->_app_id, $this->_app_secret, $c->get('code'));
		return $this->parse_web_access_token($url);
	}

	// 刷新网页授权的 token
	public function refresh_web_access_token($refresh_token) {

		$url = sprintf(self::REFRESH_WEB_TOKEN_URL, $this->_app_id, $refresh_token);
		return $this->parse_web_access_token($url);
	}

	// 根据 $url 获取对应的 web access token
	public function parse_web_access_token($url) {

		// 获取 json 数据
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		// 如果返回的数据错误, 则
		if (!isset($data['access_token'])) {
			logger::error('url:'.$url."\tweb token error:".http_build_query($data));
			return false;
		}

		// web token 数据初始化
		$this->web_token = vo::factory('voa_vo_wx_webtoken', $data);
		return true;
	}

	// 接收从微信过来的消息
	public function recv_msg() {

		// 获取 xml 信息
		$this->_xml_from_wx = (string)file_get_contents("php://input");
		//logger::error("raw data:".$this->_xml_from_wx);
		// 解析
		$xml = simplexml_load_string($this->_xml_from_wx);
		if (FALSE === $xml) {
			logger::error("xml error:".$this->_xml_from_wx);
			return false;
		}

		// 把键/值都转成字串
		$res = array();
		foreach ($xml as $k => $v) {
			$res[(string)$k] = (string)$v;
		}

		return $res;
	}

	// 获取指定用户的信息
	public function get_user_info($openid, $lang = 'zh_CN') {

		if (!$this->get_access_token()) {
			return false;
		}

		// 获取 json 数据
		$url = sprintf(self::USER_INFO_URL, $this->_access_token, $openid, $lang);
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		// 如果返回了错误
		if (!isset($data['openid'])) {
			logger::error('url:'.$url.'\tget user info error:'.http_build_query($data));
			return false;
		}

		$this->userinfo = vo::factory('voa_vo_wx_userinfo', $data);
		return true;
	}

	/**
	 * 获取关注者(openid)列表
	 * @param string $openid 如果有值, 则该用户排在第一位
	 */
	public function get_subscribe_list($openid = '') {

		// 获取 token
		if (!$this->get_access_token()) {
			return false;
		}

		// 获取 json 数据
		$url = sprintf(self::SUBSCRIBE_URL, $this->_access_token, $openid);
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		return $data;
	}

	/**
	 * 获取授权链接
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function _oauth_url($url, $scope = 'snsapi_base', $state = '') {

		return sprintf(self::OAUTH_URL, $this->_app_id, urlencode($url), $scope, $state);
	}

	/**
	 * 从指定 url 获取 json 数据
	 * @param array $data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	public function post(&$data, $url, $post = '', $retry = true) {

		// 获取 json 数据
		if (!voa_h_func::get_json_by_post($data, $url, $post)) {
			return false;
		}

		// 如果返回了错误
		if (isset($data['errcode']) && 0 != $data['errcode']) {
			logger::error('url:'.$url."\t error:".http_build_query($data));
			// 如果未重试并且是 access token 错误, 则重新尝试
			return $this->repost($data, $url, $post, $retry);
		}

		return true;
	}

	/**
	 * 从指定 url 重新获取 json 数据
	 * @param array $data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	protected function repost(&$data, $url, $post = '', $retry = false) {

		if (empty($this->_access_token_errcode) || !$retry
				|| !in_array($data['errcode'], $this->_access_token_errcode)) {
			return false;
		}

		$token = $this->_access_token;
		// 强制重新获取 access token
		$this->get_access_token(true);
		if ($this->_access_token == $token) {
			return false;
		}

		$data = array();
		return $this->post($data, $url, $post, false);
	}
}

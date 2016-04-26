<?php
/**
 * 企业号基础接口, 非套件
 * Wxqy.class.php
 * $author$
 */

namespace Com;
use Think\Log;

abstract class Wxqy {

	// 通过 code 获取用户信息
	const USER_INFO_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=%s&code=%s&agentid=%s';
	// access token 获取URL
	const ACCESS_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s';
	// 授权链接
	const OAUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';
	// 微信JS接口票据获取，jsapi-ticket
	const JSAPI_TICKET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=%s';

	// 企业号 openid
	protected $_open_id;
	// 接口配置中的 token
	protected $_token;
	// 企业号的 appid
	protected $_corp_id;
	// 企业号的 appsecret
	protected $_corp_secret;

	// access token
	protected $_access_token;
	// access token 的有效时长
	protected $_expires_in = 7200;
	// 有效期, 时间戳超过该值, 则 access token 无效
	protected $_token_expires;
	// access token 错误码
	protected $_access_token_errcode = array(
		42001, // access_token 超时
		40029, // 不合法的 oauth_code
		40001, // 获取 access_token 时 Secret 错误，或者 access_token 无效
		40014 // 不合法的access_token
	);

	// 原始 xml 信息(来自微信的)
	protected $_xml_from_wx;
	// 用户信息
	public $userinfo = array();
	// 设备ID
	protected $_wx_deviceid = '';
	// 当前用户的微信openid
	protected $_wx_openid = '';

	// 微信设置
	protected $_wx_sets = array();

	// 是否临时操作
	private $_is_tmp = false;
	protected $_tmp_corp_id = '';
	protected $_tmp_corp_secret = '';

	public function __construct() {

		// do nothing.
	}

	// 验证签名
	public function check_signature() {

		$signature = new \Com\Signature($this);
		return $signature->check_signature();
	}

	/**
	 * 获取 token
	 *
	 * @param boolean $force 强制重新获取 token
	 */
	public function get_access_token($force = false) {

		// 先从缓存中读取 token
		if (! $force && ! empty($this->_wx_sets['token_expires']) && $this->_wx_sets['token_expires'] >= NOW_TIME && ! empty($this->_wx_sets['access_token'])) {
			$this->_access_token = $this->_wx_sets['access_token'];
			$this->_token_expires = $this->_wx_sets['token_expires'];
			return true;
		}

		$url = sprintf(self::ACCESS_TOKEN_URL, $this->_corp_id, $this->_corp_secret);
		// 获取 json 数据
		$data = array();
		if (! $this->post($data, $url)) {
			return false;
		}

		// 如果返回了错误
		if (! isset($data['access_token']) || (isset($data['errcode']) && 0 != $data['errcode'])) {
			Log::record('url:' . $url . "\taccess token error:" . http_build_query($data));
			return false;
		}

		$expires_in = $data['expires_in'] ? $data['expires_in'] : $this->_expires_in;
		$this->_access_token = $data['access_token'];
		$this->_token_expires = NOW_TIME + $expires_in;

		// 如果非临时操作, 则更新 access token 信息
		if (!$this->_is_tmp) {
			$this->_update_access_token();
		}

		return true;
	}

	// 更新 access token
	protected function _update_access_token() {

		return true;
	}

	/**
	 * 获取指定用户的信息
	 *
	 * @param string $code code值
	 * @param int $agentid 应用ID
	 * @return boolean
	 */
	public function get_user_info($code, $agentid) {

		// 如果已经获取用户信息
		if ($this->userinfo) {
			return true;
		}

		// 接口 url
		$url = self::USER_INFO_URL;
		if (!$this->create_token_url($url, $code, $agentid)) {
			return false;
		}

		// 获取 json 数据
		$data = array();
		if (! $this->post($data, $url)) {
			return false;
		}

		// 手机标识, 设备ID
		$this->_wx_deviceid = (string)$data['DeviceId'];
		// 非企业成员只返回OpenId
		if (isset($data['OpenId'])) {
			$this->_wx_openid = (string)$data['OpenId'];
		}
		// 如果返回了错误
		if (! isset($data['UserId'])) {
			Log::record('url:' . $url . '\tget user info error:' . http_build_query($data));
			return false;
		}

		// 数组下标转成小写
		foreach ($data as $key => $val) {
			$key = convert_camel_underscore((string)$key);
			$this->userinfo[$key] = (string)$val;
		}

		return true;
	}

	/**
	 * 获取 jsapi-ticket
	 *
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	public function get_jsapi_ticket($force = false) {

		// 接口 URL
		$url = self::JSAPI_TICKET_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 获取json数据
		$data = array();
		if (! $this->post($data, $url)) {
			Log::record('get jsapi-ticket url: ' . $url);
			return false;
		}

		// 获取ticket出错
		if (! isset($data['ticket'])) {
			Log::record('get jsapi-ticket url: ' . $url . '|' . http_build_query($data));
			return false;
		}

		// 如果返回数据错误
		if (! isset($data['errcode']) || $data['errcode'] != 0) {
			Log::record('get jsapi-ticket url: ' . $url . '|error:' . http_build_query($data));
			return false;
		}

		// 过期时间
		$expires_in = $data['expires_in'] ? $data['expires_in'] : $this->_jsapi_expires_in;
		$this->_jsapi_ticket = $data['ticket'];
		$this->_jsapi_ticket_expire = NOW_TIME + $expires_in;
		return true;
	}

	/**
	 * 接收从微信过来的消息
	 *
	 * @return boolean|multitype:string
	 */
	public function recv_msg() {

		// 获取 xml 信息
		if (empty($this->_xml_from_wx)) {
			$this->_xml_from_wx = (string)file_get_contents("php://input");
		}

		Log::record("raw data:" . $this->_xml_from_wx);
		// 解析
		$xml = simplexml_load_string($this->_xml_from_wx);
		if (FALSE === $xml) {
			Log::record("xml error:" . $this->_xml_from_wx);
			return false;
		}

		// 把键/值都转成字串
		$res = array();
		foreach ($xml as $k => $v) {
			$res[(string)$k] = (string)$v;
		}

		return $res;
	}

	/**
	 * 从指定 url 获取 json 数据
	 *
	 * @param array &$data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	public function post(&$data, $url, $post = '', $headers = array(), $method = 'POST', $retry = true) {

		// 如果是数组, 则转成 json 数组
		if (is_array($post)) {
			$post = rjson_encode($post);
		}

		// 获取 json 数据
		if (!rfopen($data, $url, $post, $headers, $method)) {
			return false;
		}

		// 如果返回了错误
		if (isset($data['errcode']) && 0 != $data['errcode']) {
			Log::record('url:' . $url . "\terror:" . http_build_query($data));
			// 如果未重试并且是 access token 错误, 则重新尝试
			return $this->repost($data, $url, $post, $retry);
		}

		return true;
	}

	/**
	 * 从指定 url 重新获取 json 数据
	 *
	 * @param array $data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	protected function repost(&$data, $url, $post = '', $retry = false) {

		if (empty($this->_access_token_errcode) || ! $retry || ! in_array($data['errcode'], $this->_access_token_errcode)) {
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

	/**
	 * 创建一个包含 access token 的请求 URL
	 * @param string $url_string URL 字串, 把 access token 参数放在第一位
	 * @return boolean
	 */
	public function create_token_url(&$url_string) {

		// 获取 access token
		if (!$this->get_access_token()) {
			return false;
		}

		// 获取参数数组
		$params = func_get_args();
		// 获取 format 字串
		$url_string = $params[0];
		// 切出所有 sprintf 参数
		$params = array_slice($params, 1);
		// 把 format 字串和 access token 也推入参数
		array_unshift($params, $url_string, $this->_access_token);
		// 调用 sprintf 方法
		$url_string = call_user_func_array('sprintf', $params);
		return true;
	}

	/**
	 * 获取授权链接
	 *
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function _oauth_url($url, $scope = 'snsapi_base', $state = '') {

		return sprintf(self::OAUTH_URL, $this->_corp_id, urlencode($url), $scope, $state);
	}

	/**
	 * 切到测试(临时)企业号
	 * @param string $corp_id 企业号 corp_id
	 * @param string $corp_secret 企业号 corp_secret
	 * @return boolean
	 */
	public function toggle_corp($corp_id = '', $corp_secret = '') {

		$refresh = false;
		// 企业 corpid, corpsecret 都为空时, 切换到主企业号
		if (empty($corp_id)) {
			// 把主企业号信息修回来
			if (!empty($this->_tmp_corp_id)) {
				$this->_corp_id = $this->_tmp_corp_id;
				$this->_corp_secret = $this->_tmp_corp_secret;
				$refresh = true;
			}

			// 清除临时记录
			$this->_tmp_corp_id = '';
			$this->_tmp_corp_secret = '';
			$this->_is_tmp = false;
		} else { // 切换到目标企业号
			// 如果待切换的和当前的一致, 则忽略
			if ($this->_corp_id == $corp_id) {
				return true;
			}

			// 如果没有临时企业信息
			if (empty($this->_tmp_corp_id)) {
				$this->_tmp_corp_id = $this->_corp_id;
				$this->_tmp_corp_secret = $this->_corp_secret;
			}

			// 修改企业信息
			$this->_corp_id = $corp_id;
			$this->_corp_secret = $corp_secret;
			$this->_is_tmp = true;
			$refresh = true;
		}

		// 如果需要刷新 access token
		if ($refresh) {
			return $this->get_access_token($this->_is_tmp);
		}

		return true;
	}

	// 获取 token 值
	public function get_token() {

		return $this->_token;
	}

	public function get_corp_id() {

		return $this->_corp_id;
	}

	public function set_xml_from_wx($xml) {

		$this->_xml_from_wx = $xml;
	}

	// 获取设备ID
	public function get_wx_deviceid() {

		return $this->_wx_deviceid;
	}

	// 获取用户Openid
	public function get_wx_openid() {

		return $this->_wx_openid;
	}
}
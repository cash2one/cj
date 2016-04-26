<?php
/**
 * 企业号套件基础接口
 * WxqySuite.class.php
 * $author$
 */

namespace Com;
use Think\Log;

abstract class WxqySuite {

	// 授权链接
	const AUTH_URL = 'https://qy.weixin.qq.com/cgi-bin/loginpage?suite_id=%s&pre_auth_code=%s&redirect_uri=%s&state=%s';
	// 获取预授权码
	const PRE_AUTH_CODE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=%s';
	// 获取授权码
	const PERMANENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=%s';
	// 获取套件令牌
	const SUITE_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';
	// 获取企业 access token
	const ACCESS_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=%s';
	// 设置授权配置
	const AUTH_CFG = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=%s';
	// 获取企业号应用
	const GET_AGENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_agent?suite_access_token=%s';
	// 设置企业号应用
	const SET_AGENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_agent?suite_access_token=%s';
	// 获取授权信息
	const AUTH_INFO_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=%s';

	// authcode
	protected $_pre_auth_code = '';
	// access token
	public $access_token = '';
	// access token 过期时间
	public $access_token_expires = 0;

	// 套件 access token
	protected $_suite_access_token = '';
	// 套件 access token 过期时间
	protected $_suite_access_token_expires = 0;

	// 原始 xml 信息(来自微信的)
	protected $_xml_from_wx;
	// echostr
	public $retstr = 'Success';

	public function __construct() {

		// do nothing.
	}

	// 验证签名
	public function check_signature() {

		$signature = new \Com\SignatureSuite($this);
		return $signature->check_signature();
	}

	/**
	 * 接收从微信过来的消息
	 * @param array $msgs 消息
	 * @return boolean
	 */
	public function recv_msg(&$msgs, $to_underscore = false) {

		// 获取 xml 信息
		if (empty($this->_xml_from_wx)) {
			$this->_xml_from_wx = (string)file_get_contents("php://input");
		}

		Log::record("raw data:".$this->_xml_from_wx);
		// 解析
		$xml = simplexml_load_string($this->_xml_from_wx);
		if (FALSE === $xml) {
			Log::record("xml error:".$this->_xml_from_wx);
			return false;
		}

		// 把键/值都转成字串
		$msgs = array();
		$this->xml2array($msgs, $xml, $to_underscore);

		return true;
	}

	/**
	 * 把 xml 转成数组
	 * @param array $data 数组
	 * @param object $xml xml对象
	 * @return boolean
	 */
	public function xml2array(&$data, $xml, $to_underscore = false) {

		// 如果有子节点
		if (!$xml->children()) {
			$data = (string)$xml;
			return true;
		}

		// 遍历所有节点
		foreach ($xml->children() as $_element => $_node) {
			// 类型转换
			$_key = (string)$_element;
			// 如果需要转换成下划线格式
			if ($to_underscore) {
				$_key = convert_camel_underscore($_key);
			}

			// 如果子节点总数大于 1
			if (1 < count($xml->{$_element})) {
				$this->xml2array($data[$_key][], $_node, $to_underscore);
			} else {
				$this->xml2array($data[$_key], $_node, $to_underscore);
			}
		}

		return true;
	}

	/**
	 * 获取加密配置信息
	 * @param array 加密配置信息
	 * @param string 套件ID
	 * @return boolean
	 */
	public function get_sets(&$sets, $suiteid) {

		static $s_set;
		// 如果配置信息已经存在
		if (!empty($s_set)) {
			$sets = $s_set;
			return true;
		}

		// 读取套件配置
		$uc_suite = array();
		if (! $this->get_uc_suite($uc_suite, $suiteid)) {
			Log::record('error:' . var_export($_GET, true) . "\n" . $xml);
			return false;
		}

		// 解析接收消息
		$sets = array(
			'corp_id' => $uc_suite['su_suite_id'],
			'token' => $uc_suite['su_token'],
			'aes_key' => $uc_suite['su_suite_aeskey']
		);

		$s_set = $sets;
		return true;
	}

	/**
	 * 创建一个包含 access token 的请求 URL
	 * @param string $url_string URL 字串, 把 access token 参数放在第一位
	 * @return boolean
	 */
	public function create_token_url(&$url_string) {

		// 获取参数数组
		$params = func_get_args();
		// 获取 format 字串
		$url_string = $params[0];
		// 切出所有 sprintf 参数
		$params = array_slice($params, 1);
		// 把 format 字串和 access token 也推入参数
		array_unshift($params, $url_string, $this->_suite_access_token);
		// 调用 sprintf 方法
		$url_string = call_user_func_array('sprintf', $params);
		return true;
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

		// 转成 json 字串
		if (is_array($post)) {
			$post = rjson_encode($post);
		}

		// 获取 json 数据
		if (! rfopen($data, $url, $post, $headers, $method)) {
			return false;
		}

		// 如果返回了错误
		if (isset($data['errcode']) && 0 != $data['errcode']) {
			Log::record('url:' . $url . '\tpost:' . $post . "\terror:" . http_build_query($data));
			// 如果未重试并且是 access token 错误, 则重新尝试
			return false;
		}

		return true;
	}

	/**
	 * 获取 access token
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_access_token($suiteid) {

		// 取 oa suite
		$oa_suite = array();
		if (!$this->_get_oa_suite($oa_suite, $suiteid)) {
			return false;
		}

		// 如果 access token 未过期
		if (!empty($oa_suite['access_token']) && NOW_TIME < $oa_suite['expires']) {
			$this->access_token = $oa_suite['access_token'];
			$this->access_token_expires = $oa_suite['expires'];
			return true;
		}

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 请求参数
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $oa_suite['auth_corpid'],
			'permanent_code' => $oa_suite['permanent_code']
		);
		// 接口URL
		$url = self::ACCESS_TOKEN_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 读取接口
		$data = array();
		if (!$this->post($data, $url, $post)) {
			return false;
		}

		// access token, expires
		$this->access_token = $data['access_token'];
		$this->access_token_expires = NOW_TIME + $data['expires_in'];

		// 更新 access token 和 expires
		$oa_suite = array(
			'access_token' => $this->access_token,
			'expires' => $this->access_token_expires
		);
		$this->_update_oa_suite($oa_suite, $suiteid);
		return true;
	}

	/**
	 * 获取套件令牌
	 * @param string $suiteid 套件令牌
	 * @return boolean
	 */
	public function get_suite_token($suiteid) {

		// 读取套件信息
		$uc_suite = array();
		if (!$this->get_uc_suite($uc_suite, $suiteid)) {
			return false;
		}

		// 取 access token
		if (!empty($uc_suite['su_suite_access_token']) && NOW_TIME < $uc_suite['su_access_token_expires']) {
			$this->_suite_access_token = $uc_suite['su_suite_access_token'];
			$this->_suite_access_token_expires = $uc_suite['su_access_token_expires'];
			return true;
		}

		// 读取套件令牌
		$data = array();
		$post = array(
			'suite_id' => $uc_suite['su_suite_id'],
			'suite_secret' => $uc_suite['su_suite_secret'],
			'suite_ticket' => $uc_suite['su_ticket']
		);
		if (!$this->post($data, self::SUITE_TOKEN_URL, $post, array(), 'POST')) {
			return false;
		}

		$this->_suite_access_token = $data['suite_access_token'];
		$this->_suite_access_token_expires = NOW_TIME + $data['expires_in'];

		// 更新令牌
		$updatedata = array(
			'su_suite_access_token' => $this->_suite_access_token,
			'su_access_token_expires' => $this->_suite_access_token_expires
		);
		$this->_update_uc_suite($updatedata, $suiteid);
		// 更新套件信息
		$uc_suite['su_suite_access_token'] = $this->_suite_access_token;
		$uc_suite['su_access_token_expires'] = $this->_suite_access_token_expires;
		$this->get_uc_suite($uc_suite, $suiteid);

		return true;
	}

	/**
	 * 获取永久授权码
	 * @param array $data 授权信息
	 * @param string $auth_code 临时授权码
	 * @param string $suite_id 套件id
	 * @return boolean
	 */
	public function get_permanent_code(&$data, $auth_code, $suiteid, $update = true) {

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 获取永久授权码
		$post = array(
			'suite_id' => $suiteid,
			'auth_code' => $auth_code
		);
		// 接口 API URL
		$url = self::PERMANENT_URL;
		if (!self::create_token_url($url)) {
			return false;
		}

		// 请求接口
		if (!$this->post($data, $url, $post)) {
			return false;
		}

		// 更新授权方 auth_corpid, permanent_code, access_token, expires 相关信息
		if ($update) {
			$oa_suite = array(
				'auth_corpid' => $data['auth_corp_info']['corpid'],
				'permanent_code' => $data['permanent_code'],
				'access_token' => $data['access_token'],
				'expires' => NOW_TIME + $data['expires_in'],
				'authinfo' => serialize($data)
			);
			$this->_update_oa_suite($oa_suite, $suiteid);
		}

		return true;
	}

	/**
	 * 获取预授权码
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_pre_auth_code($suiteid) {

		// 如果 pre auth code 存在
		if (!empty($this->_pre_auth_code)) {
			return true;
		}

		// 读取套件信息
		$uc_suite = array();
		if (!$this->get_uc_suite($uc_suite, $suiteid)) {
			return false;
		}

		// 如果预授权码有效
		if (!empty($uc_suite['su_pre_auth_code']) && NOW_TIME < $uc_suite['su_auth_code_expires']) {
			$this->_pre_auth_code = $uc_suite['su_pre_auth_code'];
			return true;
		}

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		$url = self::PRE_AUTH_CODE_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 获取预授权码
		$data = array();
		$post = array('suite_id' => $suiteid);
		if (! $this->post($data, $url, $post)) {
			return false;
		}

		$this->_pre_auth_code = $data['pre_auth_code'];
		// 更新预授权码
		$suite = array(
			'su_pre_auth_code' => $data['pre_auth_code'],
			'su_auth_code_expires' => NOW_TIME + $data['expires_in']
		);
		$this->_update_uc_suite($suite, $suiteid);

		return true;
	}

	/**
	 * 设置授权配置
	 * @param string $suiteid 套件id
	 * @param array $appids 套件中应用id
	 * @return boolean
	 */
	public function set_auth_session($suiteid, $appids) {

		// 获取 access token
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 获取预授权码
		if (!$this->get_pre_auth_code($suiteid)) {
			return false;
		}

		// 生成接口API
		$url = self::AUTH_CFG;
		if (!$this->create_token_url($url)) {
			return false;
		}

		$appids = !is_array($appids) ? explode(',', $appids) : $appids;
		$pdata = array(
			'pre_auth_code' => $this->_pre_auth_code,
			'session_info' => array(
				'appid' => $appids
			)
		);
		$data = array();
		if (!$this->post($data, $url, $pdata)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取授权链接
	 * @param string $suiteid 套件id
	 * @param string $url 授权回调url
	 * @param string $state 自定义参数
	 * @return boolean
	 */
	public function get_oauth_url($suiteid, $url, $state = '') {

		// 获取预授权码
		if (!$this->get_pre_auth_code($suiteid)) {
			return false;
		}

		return sprintf(self::AUTH_URL, $suiteid, $this->_pre_auth_code, $url, $state);
	}

	/**
	 * 获取授权详情
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_auth_info($suiteid) {

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 取 oa suite
		$oa_suite = array();
		if (!$this->_get_oa_suite($oa_suite, $suiteid)) {
			return false;
		}

		// 接口API URL
		$url = self::AUTH_INFO_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 设置
		$data = array();
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $oa_suite['auth_corpid'],
			'permanent_code' => $oa_suite['permanent_code']
		);
		if (!$this->post($data, $url, $post)) {
			// 如果错误不是 suite+auth+not+exist%2C+may+be+no+agent+is+authorized
			if (48004 != $data['errcode']) {
				return false;
			}

			$data = array();
		}

		// 更新授权方 auth_corpid, permanent_code, access_token, expires 相关信息
		$oa_suite = array(
			'authinfo' => serialize($data)
		);
		$this->_update_oa_suite($oa_suite, $suiteid);

		return true;
	}

	/**
	 * 获取指定应用信息
	 * @param string $agent 应用信息
	 * @param string $suiteid 套件id
	 * @param int $agentid 应用id
	 * @return boolean
	 */
	public function get_agent(&$agent, $suiteid, $agentid) {

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 取 oa suite
		$oa_suite = array();
		if (!$this->_get_oa_suite($oa_suite, $suiteid)) {
			return false;
		}

		// 接口 API URL
		$url = self::GET_AGENT_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 获取应用
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $oa_suite['auth_corpid'], // 授权方corpid
			'permanent_code' => $oa_suite['permanent_code'], // 永久授权码
			'agentid' => $agentid // 应用id
		);
		if (!$this->post($agent, $url, $post)) {
			return false;
		}

		return true;
	}

	/**
	 * 设置应用相关信息
	 * @param array $agent 应用数据
	 *  + agentid 应用id
	 *  + report_location_flag 是否上报地理位置, 1: 上报, 0: 不上报
	 *  + logo_mediaid 企业应用头像的 mediaid，通过多媒体接口上传图片获得 mediaid
	 *  + name 名称
	 *  + description 描述
	 *  + redirect_domain 可信域名
	 * @param string $suiteid 套件id
	 */
	public function set_agent($agent, $suiteid) {

		// 获取令牌
		if (!$this->get_suite_token($suiteid)) {
			return false;
		}

		// 取 oa suite
		$oa_suite = array();
		if (!$this->_get_oa_suite($oa_suite, $suiteid)) {
			return false;
		}

		// 接口 API URL
		$url = self::SET_AGENT_URL;
		if (!$this->create_token_url($url)) {
			return false;
		}

		// 设置
		$data = array();
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $oa_suite['auth_corpid'],
			'permanent_code' => $oa_suite['permanent_code'],
			'agent' => $agent
		);
		if (!$this->post($data, $url, $post)) {
			return false;
		}

		return true;
	}

	/**
	 * 读取 OA suite 记录
	 * @param array $sutie 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	protected function _get_oa_suite(&$suite, $suiteid, $force = false) {

		return false;
	}

	/**
	 * 更新套件信息
	 * @param array $data 套件信息
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_oa_suite($data, $suiteid) {

		return false;
	}

	/**
	 * 根据 suite_id 读取 suite 记录
	 *
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	public function get_uc_suite(&$suite, $suiteid, $force = false) {

		return false;
	}

	/**
	 * 更新套件信息
	 * @param array $suite 待更新套件数据
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_uc_suite($suite, $suiteid) {

		return false;
	}

	public function set_xml_from_wx($xml) {

		$this->_xml_from_wx = $xml;
	}

}
<?php
/**
 * 微信公众号开放平台接口基类
 * $Author$
 * $Id$
 */

abstract class voa_weixinopen_abstract {
	// 授权 URL
	const AUTH_URL = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s&pre_auth_code=%s&redirect_uri=%s';
	// 第三方平台 token
	const COMPONENT_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
	// 预授权代码
	const PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=%s';
	// 获取授权信息
	const QUERY_AUTH = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=%s';
	// 获取授权方的 access token
	const REFRESH_AUTH_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=%s';
	// 获取授权方信息
	const AUTH_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=%s';
	// 获取授权方选项信息
	const GET_AUTH_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token=%s';
	// 设置授权方选项信息
	const SET_AUTH_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token=%s';
	// 普通用户授权
	const OAUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s&component_appid=%s#wechat_redirect';
	// 获取网页授权 access_token 的接口地址
	const WEB_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=%s&code=%s&grant_type=authorization_code&component_appid=%s&component_access_token=%s';
	// 原始 xml 信息(来自微信的)
	protected $_xml_from_wx;
	// uc 服务信息
	protected $_uc_component = array();
	// oa 服务信息
	protected $_oa_component = array();
	// uc token
	protected $_token;
	// uc appid
	protected $_appid;
	// uc aes key
	protected $_encoding_aes_key;
	/**
	 * web access token 数据对象
	 * @param vo_weixin_web_token $web_token
	 */
	public $web_token;

	public function __construct() {

	}

	public function get_access_token() {

		return $this->_oa_component['access_token'];
	}

	public function get_expires() {

		return $this->_oa_component['expires'];
	}

	/**
	 * 获取网页授权的 token
	 * @param string $appid appid
	 * @return boolean
	 */
	public function get_web_access_token($appid) {

		$c = controller_request::get_instance();
		// 获取 access token 必须的参数
		$code = $c->get('code');
		if (empty($code)) {
			return false;
		}

		if (!$this->get_component_token($appid)) {
			return false;
		}

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$url = sprintf(self::WEB_TOKEN_URL, $sets['mp_appid'], $c->get('code'), $appid, $this->_uc_component['access_token']);
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

	/**
	 * 设置授权选项信息
	 * @param string $appid 第三方服务appid
	 * @param string $optname 选项名称
	 * @param string $optvalue 选项值, 0: 关闭; 1: 开启; 2: 5s上报(地理位置专用)
	 * @return boolean
	 */
	public function set_auth_option($appid, $optname, $optvalue = 1) {

		// 读取 oa 的服务信息
		if (!$this->_get_oa_component($appid)) {
			return false;
		}

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		$url = sprintf(self::SET_AUTH_OPTION, $this->_uc_component['access_token']);
		$data = array(
			'component_appid' => $appid,
			'authorizer_appid' => $this->_oa_component['auth_appid'],
			'option_name' => $optname,
			'option_value' => $optvalue
		);

		// 获取授权信息
		$result = array();
		if (!$this->post($result, $url, $data)) {
			logger::error('post error(url:'.$url.', data:'.var_export($data, true).').');
			return false;
		}

		// 记录授权选项信息

		return true;
	}

	/**
	 * 获取授权选项信息
	 * @param string $appid 第三方服务appid
	 * @param string $optname 选项名称
	 * @return boolean
	 */
	public function get_auth_option($appid, $optname) {

		// 读取 oa 的服务信息
		if (!$this->_get_oa_component($appid)) {
			return false;
		}

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		$url = sprintf(self::GET_AUTH_OPTION, $this->_uc_component['access_token']);
		$data = array(
			'component_appid' => $appid,
			'authorizer_appid' => $this->_oa_component['auth_appid'],
			'option_name' => $optname
		);

		// 获取授权信息
		$result = array();
		if (!$this->post($result, $url, $data)) {
			logger::error('post error(url:'.$url.', data:'.var_export($data, true).').');
			return false;
		}

		// 记录授权选项信息

		return true;
	}

	/**
	 * 获取授权方信息
	 * @param string $appid 第三方服务appid
	 * @return boolean
	 */
	public function get_auth_info($appid) {

		// 读取 oa 的服务信息
		if (!$this->_get_oa_component($appid)) {
			return false;
		}

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		$url = sprintf(self::AUTH_INFO, $this->_uc_component['access_token']);
		$data = array(
			'component_appid' => $appid,
			'authorizer_appid' => $this->_oa_component['auth_appid']
		);

		// 获取授权信息
		$result = array();
		if (!$this->post($result, $url, $data)) {
			logger::error('post error(url:'.$url.', data:'.var_export($data, true).').');
			return false;
		}

		$serv = &service::factory('voa_s_oa_weopen');
		$serv->update($this->_oa_component['woid'], array(
			'autherdetail' => $result
		));

		return true;
	}

	/**
	 * 获取授权方的 access token
	 * @param string $appid 第三方服务appid
	 * @return boolean
	 */
	public function get_auth_access_token($appid) {

		// 读取 oa 的服务信息
		if (!$this->_get_oa_component($appid)) {
			return false;
		}

		// 判断授权是否过期
		if (!empty($this->_oa_component['access_token'])
				&& $this->_oa_component['expires'] > startup_env::get('timestamp')) {
			return $this->_oa_component['access_token'];
		}

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		$url = sprintf(self::REFRESH_AUTH_ACCESS_TOKEN, $this->_uc_component['access_token']);
		$data = array(
			'component_appid' => $this->_uc_component['appid'],
			'authorizer_appid' => $this->_oa_component['auth_appid'],
			'authorizer_refresh_token' => $this->_oa_component['refresh_token']
		);

		// 获取授权信息
		$result = array();
		if (!$this->post($result, $url, $data)) {
			logger::error('post error(url:'.$url.', data:'.var_export($data, true).').');
			return false;
		}

		// 更新授权信息
		$expires = $result['expires_in'] + startup_env::get('timestamp');
		$updata = array(
			'access_token' => $result['authorizer_access_token'],
			'expires' => $expires,
			'refresh_token' => $result['authorizer_refresh_token']
		);
		$this->_update_oa_component($appid, $updata);

		$this->_oa_component['access_token'] = $result['authorizer_access_token'];
		$this->_oa_component['expires'] = $expires;
		$this->_oa_component['refresh_token'] = $result['authorizer_refresh_token'];

		return $result['authorizer_access_token'];
	}

	/**
	 * 获取授权信息(授权之后调用)
	 * @param array $data 授权信息
	 * @param string $auth_code 临时授权码
	 * @param string $appid 服务appid
	 * @return boolean
	 */
	public function get_auth(&$data, $auth_code, $appid) {

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		$url = sprintf(self::QUERY_AUTH, $this->_uc_component['access_token']);
		$pdata = array('component_appid' => $appid, 'authorization_code' => $auth_code);

		// 获取授权信息
		$result = array();
		if (!$this->post($result, $url, $pdata)) {
			logger::error('post error(url:'.$url.', data:'.var_export($pdata, true).').');
			return false;
		}

		// 保持授权信息到 oa
		$serv = &service::factory('voa_s_oa_weopen');
		$expires = $result['authorization_info']['expires_in'] + startup_env::get('timestamp');
		$data = array(
			'auth_appid' => $result['authorization_info']['authorizer_appid'],
			'access_token' => $result['authorization_info']['authorizer_access_token'],
			'refresh_token' => $result['authorization_info']['authorizer_refresh_token'],
			'expires' => $expires,
			'code' => $auth_code,
			'authinfo' => serialize($result)
		);
		$this->_update_oa_component($appid, $data);

		return true;
	}

	/**
	 * 获取预授权码
	 * @param string $appid 服务appid
	 * @return boolean
	 */
	public function get_pre_auth_code($appid) {

		// 获取 access token
		if (!$this->get_component_token($appid)) {
			return false;
		}

		// 如果预授权码有效
		if (!empty($this->_uc_component['pre_auth_code'])
				&& startup_env::get('timestamp') < $this->_uc_component['auth_code_expires']) {
			return true;
		}

		// post 数据
		$data = array('component_appid' => $this->_uc_component['appid']);
		$url = sprintf(self::PRE_AUTH_CODE, $this->_uc_component['access_token']);

		// 获取 pre auth code
		$result = array();
		if (!$this->post($result, $url, $data)) {
			logger::error('post error(url:'.$url.', data:'.var_export($data, true).').');
			return false;
		}

		// token 信息入库
		$serv = &service::factory('voa_s_uc_weopen');
		$expires = $result['expires_in'] + startup_env::get('timestamp');
		$serv->update($this->_uc_component['woid'], array(
			'pre_auth_code' => $result['pre_auth_code'],
			'auth_code_expires' => $expires
		));

		$this->_uc_component['pre_auth_code'] = $result['pre_auth_code'];
		$this->_uc_component['auth_code_expires'] = $expires;

		return true;
	}

	/**
	 * 获取 access token
	 * @param $appid 服务appid
	 */
	public function get_component_token($appid) {

		// 获取服务信息
		if (!$this->_get_uc_component($appid)) {
			logger::error('component is not exists(appid:'.$appid.').');
			return false;
		}

		// token 有效
		if (!empty($this->_uc_component['access_token'])
				&& startup_env::get('timestamp') < $this->_uc_component['token_expires']) {
			return true;
		}

		// 请求的 post 数据
		$data = array(
			"component_appid" => $appid,
			"component_appsecret" => $this->_uc_component['appsecret'],
			"component_verify_ticket" => $this->_uc_component['ticket']
		);

		// 获取 token
		$result = array();
		if (!$this->post($result, self::COMPONENT_TOKEN, $data)) {
			logger::error('post error(url:'.self::COMPONENT_TOKEN.', data:'.var_export($data, true).').');
			return false;
		}

		// token 信息入库
		$expires = $result['expires_in'] + startup_env::get('timestamp');
		$updata = array(
			'access_token' => $result['component_access_token'],
			'token_expires' => $expires
		);
		$this->_update_uc_component($appid, $updata);

		$this->_uc_component['access_token'] = $result['component_access_token'];
		$this->_uc_component['token_expires'] = $expires;

		return true;
	}

	// 根据 appid 获取服务信息
	protected function _get_uc_component($appid) {

		if (!empty($this->_uc_component)) {
			return true;
		}

		$serv = &service::factory('voa_s_uc_weopen');
		$this->_uc_component = $serv->get_by_conds(array('appid' => $appid));
		if (empty($this->_uc_component)) {
			return false;
		}

		return true;
	}

	protected function _update_uc_component($appid, $data) {

		$this->_get_uc_component($appid);
		$serv = &service::factory('voa_s_uc_weopen');
		if (empty($this->_uc_component)) {
			return false;
		}

		$serv->update($this->_uc_component['woid'], $data);

		return true;
	}

	protected function _get_oa_component($appid) {

		if (!empty($this->_oa_component)) {
			return true;
		}

		$serv = &service::factory('voa_s_oa_weopen');
		$this->_oa_component = $serv->get_by_conds(array('appid' => $appid));
		if (empty($this->_oa_component)) {
			return false;
		}

		return true;
	}

	protected function _update_oa_component($appid, $data) {

		$this->_get_oa_component($appid);
		$serv = &service::factory('voa_s_oa_weopen');
		if (empty($this->_oa_component)) {
			return false;
		}

		$serv->update($this->_oa_component['woid'], $data);

		return true;
	}

	/**
	 * 从指定 url 获取 json 数据
	 * @param array &$data 结果
	 * @param string $url url地址
	 * @param mixed $post post数据
	 * @param bool $retry 是否需要重新获取
	 */
	public function post(&$data, $url, $post = '', $retry = true) {

		// 转成 json 字串
		if (is_array($post)) {
			$post = rjson_encode($post);
		}

		// 获取 json 数据
		if (!voa_h_func::get_json_by_post($data, $url, $post)) {
			return false;
		}

		// 如果返回了错误
		if (isset($data['errcode']) && 0 != $data['errcode']) {
			logger::error('url:'.$url."\terror:".http_build_query($data)."\tpost:".$post);
			// 如果未重试并且是 access token 错误, 则重新尝试
			return false;
		}

		return true;
	}

	public function init($weopen) {

		$this->_token = $weopen['token'];
		$this->_appid = $weopen['appid'];
		$this->_encoding_aes_key = $weopen['aeskey'];

		return true;
	}

	// 检测来自微信请求的URL是否有效
	public function check_signature() {

		$request = controller_request::get_instance();
		$msg_sign = $request->get('msg_signature');
		$ts = $request->get('timestamp');
		$nonce = $request->get('nonce');
		$signature = $request->get("signature");

		$xml = (string)file_get_contents("php://input");
		$xml_tree = new DOMDocument();
		$xml_tree->loadXML($xml);
		$array_e = $xml_tree->getElementsByTagName('Encrypt');
		$array_id = $xml_tree->getElementsByTagName('AppId');

		logger::error($xml);
		// 如果没有加密字串
		if (0 == $array_e->length || 0 == $array_id->length) {
			$this->_xml_from_wx = $xml;
			logger::error('weopen xml error.');
			return true;
		}

		$request = controller_request::get_instance();
		$encrypt = $array_e->item(0)->nodeValue;
		$appid = $array_id->item(0)->nodeValue;

		// 取服务信息
		$serv = &service::factory('voa_s_uc_weopen');
		$weopen = $serv->get_by_conds(array('appid' => $appid));
		if (empty($weopen)) {
			logger::error('appid is not exist(appid: '.$appid.').');
			return false;
		}

		logger::error(var_export($weopen, true));
		$this->init($weopen);

		// 验证前面
		$tmp_arr = array($this->_token, (string)$ts, (string)$nonce);
		sort($tmp_arr, SORT_STRING);
		$tmp_sig = implode($tmp_arr);
		$tmp_sig = sha1($tmp_sig);
		if ($tmp_sig != $signature) {
			logger::error("check signature error:{$tmp_sig}\t{$timestamp}\t{$nonce}");
			return false;
		}

		$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
		$from_xml = sprintf($format, $encrypt);

		logger::error($this->_token."\n".$this->_encoding_aes_key."\n".$this->_appid."\n".$msg_sign."\n".$ts."\n".$nonce."\n".$from_xml);
		// 解密
		$pc = new WXBizMsgCrypt($this->_token, $this->_encoding_aes_key, $this->_appid);
		$errcode = $pc->decryptMsg($msg_sign, $ts, $nonce, $from_xml, $this->_xml_from_wx);
		if (0 == $errcode) {
			return true;
		} else {
			return false;
		}
	}

	// 接收从微信过来的消息
	public function recv_msg() {

		// 获取 xml 信息
		if (empty($this->_xml_from_wx)) {
			$this->_xml_from_wx = (string)file_get_contents("php://input");
		}

		logger::error("raw data:".$this->_xml_from_wx);
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

	/**
	 * 获取授权链接
	 * @param string $appid 第三方服务appid
	 * @param string $url 授权回调url
	 * @return boolean
	 */
	public function get_oauth_url($appid, $url) {

		// 获取预授权码
		if (!$this->get_pre_auth_code($appid)) {
			return false;
		}

		return sprintf(self::AUTH_URL, $appid, $this->_uc_component['pre_auth_code'], $url);
	}

	/**
	 * 获取授权链接
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function _oauth_url($url, $appid, $scope = 'snsapi_base', $state = '') {

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		return sprintf(self::OAUTH_URL, $sets['mp_appid'], urlencode($url), $scope, $state, $appid);
	}

	// 把驼峰转成以下划线分隔, 如:MsgType => msg_type
	public function convert_key($key) {

		$key{0} = rstrtolower($key{0});
		$key = preg_replace("/([A-Z]+)/s", "_\\1", $key);
		return rstrtolower($key);
	}

}

<?php
/**
 * 微信企业网关接口基类
 * $Author$
 * $Id$
 */

class voa_wxqysuite_base {
	// 获取套件令牌
	const SUITE_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';
	// 获取预授权码
	const PRE_AUTH_CODE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=%s';
	// 获取授权码
	const PERMANENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=%s';
	// 授权链接
	const AUTH_URL = 'https://qy.weixin.qq.com/cgi-bin/loginpage?suite_id=%s&pre_auth_code=%s&redirect_uri=%s&state=%s';
	// 获取企业号应用
	const GET_AGENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_agent?suite_access_token=%s';
	// 设置企业号应用
	const SET_AGENT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_agent?suite_access_token=%s';
	// 获取企业 access token
	const ACCESS_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=%s';
	// 获取授权信息
	const AUTH_INFO_URL = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=%s';
	// 设置授权配置
	const AUTH_CFG = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=%s';
	// 套件id
	protected $_suite_id = '';
	// 套件秘钥
	protected $_suite_secret = '';
	// 套件 aes key
	protected $_suite_aes_key = '';
	// 原始 xml 信息(来自微信的)
	protected $_xml_from_wx;
	// echostr
	public $retstr = 'success';
	// uc 套件信息
	protected $_uc_suite = array();
	// oa 套件信息
	protected $_oa_suite = array();
	// access token
	public $access_token = '';
	public $access_token_expires = 0;

	public function __construct() {

	}

	// 重定向
	protected function _redirect() {

		$c = controller_request::get_instance();
		$gets = $c->getx();

		$xml = (string)file_get_contents("php://input");

		// 取id
		$corpid = $c->get('corpid');
		$serv_ep = &service::factory('voa_s_cyadmin_enterprise_profile');
		if (!$eps = $serv_ep->fetch_by_conditions(array('ep_wxcorpid' => $corpid), 0, 10)) {
			logger::error(var_export($_GET, true)."\n".$xml);
			exit('');
		}

		$ep = array_pop($eps);
		logger::error(var_export($_GET, true)."\n".$xml);
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$ep['ep_domain'].'/qywx.php?'.http_build_query($gets);

		// 获取 json 数据
		$data = array();
		$snoopy = new snoopy();
		$result = $snoopy->submit($url, $xml);
		// 如果读取错误
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: '.$url.'|'.$result.'|'.$snoopy->status);
			exit('');
		}

		// 解析 json
		echo $snoopy->results;
		exit('');
	}

	// 检测来自微信请求的URL是否有效
	public function check_signature() {

		$c = controller_request::get_instance();
		$signature = $c->get("msg_signature");
		$timestamp = $c->get("timestamp");
		$nonce = $c->get("nonce");
		$echostr = $c->get("echostr", '');

		// 判断是否为消息
		$corpid = $c->get('corpid');
		if (!empty($corpid)) {
			$this->_redirect();
			exit;
		}

		if (empty($echostr)) { // 非鉴权请求
			$xml = (string)file_get_contents("php://input");
			$xml_tree = new DOMDocument();
			$xml_tree->loadXML($xml);
			$array_e = $xml_tree->getElementsByTagName('Encrypt');
			$encrypt = $array_e->item(0)->nodeValue;
			$suite_e = $xml_tree->getElementsByTagName('ToUserName');
			$suiteid = $suite_e->item(0)->nodeValue;
		} else {
			$encrypt = $echostr;
			$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
			$xml = sprintf($format, $encrypt);
		}

		// 读取套件配置]
		if (!$this->_get_uc_suite($suiteid)) {
			logger::error('errnoA:'.$errno."\n".var_export($_GET, true)."\n".$xml);
			return false;
		}

		// 解析接收消息
		$sets = array(
			'corp_id' => $this->_uc_suite['su_suite_id'],
			'token' => $this->_uc_suite['su_token'],
			'aes_key' => $this->_uc_suite['su_suite_aeskey']
		);
		list($errno, $content) = qywx_callback::instance($sets)->from_tx($xml, $signature, $nonce, $timestamp);
		if (0 == $errno) { // 接收成功
			if (empty($echostr)) {
				$this->_xml_from_wx = $content;
			} else {
				$this->retstr = $content;
			}

			return true;
		} else {
			logger::error('errnoB:'.$errno."\n".var_export($_GET, true)."\n".$xml);
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
	 * 获取套件令牌
	 * @param string $suite_access_token 套件令牌
	 * @param string $suiteid 套件令牌
	 * @return boolean
	 */
	public function get_suite_token(&$suite_access_token, $suiteid) {

		$suite = voa_h_cache::get_instance()->get($suiteid, 'uc.vchangyi.com' == $_SERVER['HTTP_HOST'] ? 'uc' : 'ucenter');
		// 如果缓存中的套件token已过期, 则重新获取
		if (empty($suite) || empty($suite['su_suite_access_token']) || startup_env::get('timestamp') + 300 > $suite['su_access_token_expires']) {
			$suite = array();
			$url = config::get('voa.uc_url') . 'OaRpc/Rpc/Suite';
			if (!voa_h_rpc::query($suite, $url, 'get_suite_token', $suiteid)) {
				logger::error('suiteid:'.$suiteid.var_export($suite, true));
				return false;
			}
		}

		$suite_access_token = $suite['su_suite_access_token'];
		return true;
		// 读取套件信息
		/**if (!$this->_get_uc_suite($suiteid)) {
			logger::error("suiteid error get_suite_token.A");
			return false;
		}

		// 取 access token
		if (!empty($this->_uc_suite['su_suite_access_token']) && startup_env::get('timestamp') < $this->_uc_suite['su_access_token_expires']) {
			$suite_access_token = $this->_uc_suite['su_suite_access_token'];
			logger::error("suite access tokenA:{$suite_access_token}");
			return true;
		}

		// 读取套件令牌
		$data = array();
		$post = array(
			'suite_id' => $this->_uc_suite['su_suite_id'],
			'suite_secret' => $this->_uc_suite['su_suite_secret'],
			'suite_ticket' => $this->_uc_suite['su_ticket']
		);
		if (!$this->post($data, self::SUITE_TOKEN_URL, $post)) {
			logger::error("suiteid error get_suite_token.C");
			return false;
		}

		// 更新令牌
		$updatedata = array(
			'su_suite_access_token' => $data['suite_access_token'],
			'su_access_token_expires' => startup_env::get('timestamp') + ($data['expires_in'] * 0.8)
		);
		$this->_update_uc_suite($updatedata, $suiteid);
		$suite_access_token = $data['suite_access_token'];
		logger::error("suite access tokenB:{$suite_access_token}");

		return true;*/
	}

	/**
	 * 获取预授权码
	 * @param string $auth_code 授权码
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_pre_auth_code(&$auth_code, $suiteid) {

		// 读取套件信息
		if (!$this->_get_uc_suite($suiteid)) {
			logger::error("suiteid error get_pre_auth_code.A");
			return false;
		}

		// 如果预授权码有效
		if (!empty($this->_uc_suite['su_pre_auth_code']) && startup_env::get('timestamp') < $this->_uc_suite['su_auth_code_expires']) {
			$auth_code = $this->_uc_suite['su_pre_auth_code'];
			logger::error("pre auth codeA: {$auth_code}");
			return true;
		}

		// 取 access token
		if (!empty($this->_uc_suite['su_pre_auth_code']) && startup_env::get('timestamp') < $this->_uc_suite['su_auth_code_expires']) {
			$auth_code = $this->_uc_suite['su_pre_auth_code'];
			logger::error("pre auth codeB: {$auth_code}");
			return true;
		}

		// 获取令牌
		$access_token = '';
		if (!$this->get_suite_token($access_token, $suiteid)) {
			logger::error("suiteid error get_pre_auth_code.B");
			return false;
		}

		// 获取预授权码
		$url = sprintf(self::PRE_AUTH_CODE_URL, $access_token);
		$data = array();
		$post = array(
			'suite_id' => $suiteid
		);
		if (!$this->post($data, $url, $post)) {
			logger::error("suiteid error get_pre_auth_code.C");
			return false;
		}

		// 更新预授权码
		$suite = array(
			'su_pre_auth_code' => $data['pre_auth_code'],
			'su_auth_code_expires' => startup_env::get('timestamp') + ($data['expires_in'] * 0.8)
		);
		$this->_update_uc_suite($suite, $suiteid);
		$auth_code = $data['pre_auth_code'];
		logger::error("pre auth codeB: {$auth_code}");

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
		$suite_access_token = '';
		if (!$this->get_suite_token($suite_access_token, $suiteid)) {
			return false;
		}

		// 获取预授权码
		$authcode = '';
		if (!$this->get_pre_auth_code($authcode, $suiteid)) {
			return false;
		}

		$url = sprintf(self::AUTH_CFG, $suite_access_token);
		$appids = !is_array($appids) ? explode(',', $appids) : $appids;
		$pdata = array(
			'pre_auth_code' => $authcode,
			'session_info' => array(
				'appid' => $appids
			)
		);

		if (!$this->post($data, $url, $pdata)) {
			logger::error("{$url}.".var_export($pdata, true));
			return false;
		}

		logger::error("auth session:".var_export($data, true));

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
		$authcode = '';
		if (!$this->get_pre_auth_code($authcode, $suiteid)) {
			return false;
		}

		return sprintf(self::AUTH_URL, $suiteid, $authcode, $url, $state);
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
		$access_token = '';
		if (!$this->get_suite_token($access_token, $suiteid)) {
			logger::error("permanent code errorA.");
			return false;
		}

		// 获取永久授权码
		$post = array(
			'suite_id' => $suiteid,
			'auth_code' => $auth_code
		);
		$url = sprintf(self::PERMANENT_URL, $access_token);
		if (!$this->post($data, $url, $post)) {
			logger::error("permanent code error. url:".$url.';post:'.var_export($post, true).";data:".var_export($data, true));
			return false;
		}

		// 更新授权方 auth_corpid, permanent_code, access_token, expires 相关信息
		if ($update) {
			$oa_suite = array(
				'auth_corpid' => $data['auth_corp_info']['corpid'],
				'permanent_code' => $data['permanent_code'],
				'access_token' => $data['access_token'],
				'expires' => startup_env::get('timestamp') + ($data['expires_in'] * 0.8),
				'authinfo' => serialize($data)
			);
			$this->_update_oa_suite($oa_suite, $suiteid);
		}

		return true;
	}

	/**
	 * 获取指定应用信息
	 * @param string $suiteid 套件id
	 * @param int $agentid 应用id
	 * @return boolean
	 */
	public function get_agent($suiteid, $agentid) {

		// 获取令牌
		$access_token = '';
		if (!$this->get_suite_token($access_token, $suiteid)) {
			return false;
		}

		// 取 oa suite
		if (!$this->_get_oa_suite($suiteid)) {
			return false;
		}

		// 获取应用
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $this->_oa_suite['auth_corpid'], // 授权方corpid
			'permanent_code' => $this->_oa_suite['permanent_code'], // 永久授权码
			'agentid' => $agentid // 应用id
		);
		if (!$this->post($data, sprintf(self::GET_AGENT_URL, $access_token), $post)) {
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
		$access_token = '';
		if (!$this->get_suite_token($access_token, $suiteid)) {
			return false;
		}

		// 取 oa suite
		if (!$this->_get_oa_suite($suiteid)) {
			return false;
		}

		// 设置
		$data = array();
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $this->_oa_suite['auth_corpid'],
			'permanent_code' => $this->_oa_suite['permanent_code'],
			'agent' => $agent
		);
		if (!$this->post($data, sprintf(self::SET_AGENT_URL, $access_token), $post)) {
			return false;
		}

		// 如果更新成功
		if (0 == $data['errcode']) {
			return true;
		}

		return false;
	}

	/**
	 * 获取 access token
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_access_token($suiteid) {

		// 取 oa suite
		if (!$this->_get_oa_suite($suiteid)) {
			return false;
		}

		// 如果 access token 未过期
		if (!empty($this->_oa_suite['access_token']) && startup_env::get('timestamp') < $this->_oa_suite['expires']) {
			$this->access_token = $this->_oa_suite['access_token'];
			$this->access_token_expires = $this->_oa_suite['expires'];
			return true;
		}

		// 获取令牌
		$suite_access_token = '';
		if (!$this->get_suite_token($suite_access_token, $suiteid)) {
			return false;
		}

		// 设置
		$data = array();
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $this->_oa_suite['auth_corpid'],
			'permanent_code' => $this->_oa_suite['permanent_code']
		);
		if (!$this->post($data, sprintf(self::ACCESS_TOKEN_URL, $suite_access_token), $post)) {
			// 如果 $sutie_access_token 过期, 则重置
			if (42009 == $data['errcode']) {
				$updatedata = array(
					'su_suite_access_token' => '',
					'su_access_token_expires' => 0
				);
				$this->_update_uc_suite($updatedata, $suiteid);
			}

			return false;
		}

		// 更新 access token 和 expires
		$oa_suite = array(
			'access_token' => $data['access_token'],
			'expires' => $data['expires_in'] + startup_env::get('timestamp')
		);
		$this->_update_oa_suite($oa_suite, $suiteid);
		// access token, expires
		$this->access_token = $data['access_token'];
		$this->access_token_expires = $data['expires_in'] + startup_env::get('timestamp');

		return true;
	}

	/**
	 * 获取授权
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_auth_info($suiteid) {

		// 获取令牌
		$access_token = '';
		if (!$this->get_suite_token($access_token, $suiteid)) {
			return false;
		}

		// 取 oa suite
		if (!$this->_get_oa_suite($suiteid)) {
			return false;
		}

		// 设置
		$data = array();
		$post = array(
			'suite_id' => $suiteid,
			'auth_corpid' => $this->_oa_suite['auth_corpid'],
			'permanent_code' => $this->_oa_suite['permanent_code']
		);
		if (!$this->post($data, sprintf(self::AUTH_INFO_URL, $access_token), $post)) {
			// 如果错误不是 suite+auth+not+exist%2C+may+be+no+agent+is+authorized
			if (48004 != $data['errcode']) {
				return false;
			}

			$data = array();
		}

		// 更新授权方 auth_corpid, permanent_code, access_token, expires 相关信息
		$oa_suite = array(
			//'auth_corpid' => $data['auth_corp_info']['corpid'],
			//'permanent_code' => $data['permanent_code'],
			//'access_token' => $data['access_token'],
			//'expires' => startup_env::get('timestamp') + ($data['expires_in'] * 0.8),
			'authinfo' => serialize($data)
		);
		$this->_update_oa_suite($oa_suite, $suiteid);

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

	/**
	 * 读取 OA suite 记录
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	protected function _get_oa_suite($suiteid) {

		// 初始化 suite
		$serv_suite = &service::factory('voa_s_oa_suite');
		// 如果未读到
		if (!$this->_oa_suite = $serv_suite->fetch_by_suiteid($suiteid)) {
			logger::error('query error:'.$suiteid);
			return false;
		}

		return true;
	}

	protected function _update_oa_suite($data, $suiteid) {

		logger::error("suiteid:{$suiteid}");
		// 获取 suite
		$this->_get_oa_suite($suiteid);
		// 编辑 suite
		$serv_suite = &service::factory('voa_s_oa_suite');
		if ($this->_oa_suite) {
			$serv_suite->update($data, "`suiteid`='{$suiteid}'");
		} else {
			$serv_p = &service::factory('voa_s_oa_common_plugin');
			$serv_p->update(array('cp_agentid' => '', 'cp_suiteid' => '', 'cp_available' => 0), "cp_available<255 AND cp_suiteid=''");

			$data['suiteid'] = $suiteid;
			$serv_suite->insert($data);
		}

		return true;
	}

	/**
	 * 根据 suite_id 读取 suite 记录
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	protected function _get_uc_suite($suiteid) {

		// 通过 rpc, 获取套件信息
		$suite = array();
		$client = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Suite');
		if (!$suite = $client->get_by_suiteid($suiteid)) {
			logger::error('suiteid:'.$suiteid."\n".var_export($_GET, true));
			return false;
		}

		if (voa_h_rpc::is_error($suite)) {
			return false;
		}

		$this->_uc_suite = $suite;
		// 初始化 suite
		/**$serv_suite = &service::factory('voa_s_uc_suite');
		// 如果未读到记录
		if (!$this->_uc_suite = $serv_suite->fetch_by_suiteid($suiteid)) {
			logger::error('errno:'.$errno."\n".$suiteid."\n".var_export($_GET, true)."\n".$xml);
			return false;
		}*/

		return true;
	}

	protected function _update_uc_suite($data, $suiteid) {

		// 通过 rpc, 获取套件信息
		$client = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Suite');
		$result = null;
		if (!$result = $client->update_by_suiteid($data, $suiteid, false)) {
			return false;
		}

		if (voa_h_rpc::is_error($result)) {
			return false;
		}

		// 读取 suite
		return $this->_get_uc_suite($suiteid);
		// 更新 suite
		/**$serv_suite = &service::factory('voa_s_uc_suite');
		if ($this->_uc_suite) {
			$serv_suite->update($data, "`su_suite_id`='{$suiteid}'");
		} else {
			$data['su_suite_id'] = $suiteid;
			$serv_suite->insert($data);
		}*/
	}

	// 把驼峰转成以下划线分隔, 如:MsgType => msg_type
	public function convert_key($key) {

		$key{0} = rstrtolower($key{0});
		$key = preg_replace("/([A-Z]+)/s", "_\\1", $key);
		return rstrtolower($key);
	}

}

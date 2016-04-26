<?php
/**
 * 微信企业网关接口基类
 * $Author$
 * $Id$
 */

class voa_wxqy_base {
	/** access token 获取URL */
	const ACCESS_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s';
	/** 获取用户信息 */
	const USER_INFO_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=%s&code=%s&agentid=%s';
	/** 授权链接 */
	const OAUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';
	/** 应用代理菜单：创建菜单的接口 URL */
	const MENU_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=%s&agentid=%s';
	/** - 应用代理菜单：删除菜单的接口 URl*/
	const MENU_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=%s&agentid=%s';
	/** - 应用代理菜单：获取菜单列表的接口 URL*/
	const MENU_GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=%s&agentid=%s';
	/** 通讯录：创建部门 */
	const DEPARTMENT_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=%s';
	/** 通讯录：更新部门 */
	const DEPARTMENT_UPDATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=%s';
	/** 通讯录：删除部门 */
	const DEPARTMENT_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=%s&id=%s';
	/** 通讯录：获取部门列表 */
	const DEPARTMENT_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=%s';
	/** 通讯录: 部门下成员列表 */
	const DEPARTMENT_SIMPLE_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%d&fetch_child=1&status=0';
	/** 通讯录：创建成员 */
	const USER_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=%s';
	/** 通讯录：更新成员 */
	const USER_UPDATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=%s';
	/** 通讯录：删除成员 */
	const USER_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=%s&userid=%s';
	/** 通讯录：获取成员 */
	const USER_GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=%s&userid=%s';
    /** 通讯录：邀请成员关注 */
    const USER_INVITE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/invite/send?access_token=%s';
	/** 微信JS接口票据获取，jsapi-ticket */
	const JSAPI_TICKET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=%s';
	// 根据 userid 读取 openid
	const CONVERT_TO_OPENID_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/convert_openid?access_token=%s&userid=%s&agentid=%d';
	// 读取指定部门的用户列表
	const USER_LIST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=%s&department_id=%d&fetch_child=1&status=0';
	/** 当前公众号 open id */
	protected $_open_id;
	/** 接口配置中的 token */
	protected $_token;
	/** 公众帐号的 appid */
	protected $_corp_id;
	/** 公众帐号的 appsecret */
	protected $_corp_secret;
	/** access token */
	protected $_access_token;
	/** access token 的有效时长 */
	protected $_expires_in = 7200;
	/** 原始 xml 信息(来自微信的) */
	protected $_xml_from_wx;
	/** 有效期, 时间戳超过该值, 则 access token 无效 */
	protected $_token_expires;
	/** access token 错误码 */
	protected $_access_token_errcode = array(42001, 40029, 40001, 40014);
	/** 用户信息 */
	public $userinfo = array();

	/** js api ticket 的默认有效时长 */
	protected $_jsapi_expires_in = 7200;
	/** js api ticket */
	protected $_jsapi_ticket = '';
	/** js api ticket 失效时间 */
	protected $_jsapi_ticket_expire = 0;

	/** 当前token所在的套件信息 */
	protected $_oa_suite = array();

	/**
	 * api错误码对应表
	 * @var array|mixed
	 */
	protected $_api_errcodes = array();

	/** 错误编码 */
	public $errcode = 0;
	/** 错误信息 */
	public $errmsg = '';
	// echostr
	public $retstr = 'success';
	// uc 套件信息
	public $uc_suite = array();

	public function __construct() {

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_corp_id = isset($sets['corp_id']) ? $sets['corp_id'] : '';
		$this->_corp_secret = isset($sets['corp_secret']) ? $sets['corp_secret'] : '';
		$this->_token = isset($sets['token']) ? $sets['token'] : '';
		$this->_open_id = $this->_corp_id;
		$this->_expires_in = config::get('voa.wxqy.expires_in');
		$this->_access_token_errcode = config::get('voa.wxqy.access_token_errcode');
		$this->_api_errcodes = config::get('voa.wxqy.api_error');
	}

	/**
	 * 第一版验证
	 * @return boolean
	 */
	public function check_signature_v1() {
		$c = controller_request::get_instance();
		$signature = $c->get("signature");
		$timestamp = $c->get("timestamp");
		$nonce = $c->get("nonce");

		$tmpArr = array($this->_token, (string)$timestamp, (string)$nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			$this->retstr = $c->get('echostr') ? $c->get('echostr') : 'ok';
			return true;
		} else {
			logger::error("check signature error:{$signature}\t{$timestamp}\t{$nonce}");
			return false;
		}
	}

	/**
	 * 第二版验证
	 */
	public function check_signature_v2() {

		$c = controller_request::get_instance();
		// 初始化 suite
		$serv_suite = &service::factory('voa_s_oa_suite');
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$pluginid = $c->get('pluginid');
		$suiteid = '';
		foreach ($plugins as $_p) {
			if ($_p['cp_pluginid'] == $pluginid) {
				$suiteid = $_p['cp_suiteid'];
				break;
			}
		}
		// 如果未读到
		$oa_suite = $serv_suite->fetch_by_suiteid($suiteid);

		$signature = $c->get("msg_signature");
		$timestamp = $c->get("timestamp");
		$nonce = $c->get("nonce");
		$echostr = $c->get("echostr", '');
		if (empty($echostr)) { // 非鉴权请求
			$xml = (string)file_get_contents("php://input");
			$xml_tree = new DOMDocument();
			$xml_tree->loadXML($xml);
			$array_e = $xml_tree->getElementsByTagName('Encrypt');
			$encrypt = $array_e->item(0)->nodeValue;
		} else {
			$encrypt = $echostr;
			$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
			$xml = sprintf($format, $encrypt);
		}

		// 解析接收消息
		if ($oa_suite) {
			// 读取套件配置]
			if (!$this->get_uc_suite($suiteid)) {
				logger::error('errnoA:'.$errno."\n".var_export($_GET, true)."\n".$xml);
				return false;
			}

			// 解析接收消息
			$sets = array(
				'corp_id' => $this->_corp_id,
				'token' => $this->uc_suite['su_token'],
				'aes_key' => $this->uc_suite['su_suite_aeskey']
			);
		} else {
			$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		}

		list($errno, $content) = qywx_callback::instance($sets)->from_tx($xml, $signature, $nonce, $timestamp);
		if (0 == $errno) { // 接收成功
			if (empty($echostr)) {
				$this->_xml_from_wx = $content;
			} else {
				$this->retstr = $content;
			}

			return true;
		} else {
			logger::error('errnoB:'.$errno."\n".var_export($_GET, true)."\n".$xml."\n".var_export($sets, true));
			return false;
		}
	}

	/** 检测来自微信请求的URL是否有效 */
	public function check_signature() {
		$c = controller_request::get_instance();
		$msg = $c->get('msg_signature', '');
		if (empty($msg)) {
			return $this->check_signature_v1();
		} else {
			return $this->check_signature_v2();
		}
	}

	/**
	 * 获取 token
	 * @param boolean $force 强制重新获取 token
	 */
	public function get_access_token($force = false) {

		$c = controller_request::get_instance();
		$serv_suite = &service::factory('voa_s_oa_suite');
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$pluginid = startup_env::get('pluginid');
		if (empty($pluginid)) {
			$c = controller_request::get_instance();
			$pluginid = $c->get('pluginid');
		}

		$suiteid = $c->get('suiteid');
		foreach ($plugins as $_p) {
			if ($_p['cp_pluginid'] == $pluginid) {
				if (!empty($_p['cp_suiteid'])) {
					$suiteid = $_p['cp_suiteid'];
				}

				break;
			}
		}
		// 如果未读到
		$oa_suite = $serv_suite->fetch_by_suiteid($suiteid);
		if (empty($oa_suite) && empty($pluginid)) {
			$suites = $serv_suite->fetch_all();
			$noagent = config::get(startup_env::get('app_name').'.suite.noagent');
			foreach ($suites as $_suite) {
				$ais = is_array($_suite['authinfo']) ? $_suite['authinfo'] : unserialize($_suite['authinfo']);
				if (empty($ais) || in_array($_suite['suiteid'], $noagent)) {
					continue;
				}

				$oa_suite = $_suite;
				$is_all = false;
				foreach ($ais['auth_info']['department'] as $_v) {
					if (0 == $_v['parentid']) {
						$is_all = true;
						break;
					}
				}

				if ($is_all) {
					break;
				}
			}
		}

		// 如果应用信息不存在
		if (!empty($oa_suite)) {

			// 当前套件信息
			$this->_oa_suite = $oa_suite;
			if (empty($this->_corp_id) && !empty($this->_corp_id) && $this->_corp_id != $this->_oa_suite['auth_corpid']) {
				$this->_corp_id = $this->_oa_suite['auth_corpid'];
			}
			// 如果 access_token 未过期
			if (!empty($oa_suite['access_token']) && startup_env::get('timestamp') < $oa_suite['expires']) {
				$this->_access_token = $oa_suite['access_token'];
				$this->_access_token_expires = $oa_suite['expires'];
				return true;
			}

			$serv = voa_wxqysuite_service::instance();
			$serv->get_access_token($oa_suite['suiteid']);
			$this->_access_token = $serv->access_token;
			$this->_token_expires = $serv->access_token_expires;
			return true;
		}
		/** 先从缓存中读取 token */
		$sets = voa_h_cache::get_instance()->get('weixin', 'oa');
		if (!$force && !empty($sets['token_expires']) && $sets['token_expires'] >= startup_env::get('timestamp') && !empty($sets['access_token'])) {
			$this->_access_token = $sets['access_token'];
			$this->_token_expires = $sets['token_expires'];
			return true;
		}

		$url = sprintf(self::ACCESS_TOKEN_URL, $this->_corp_id, $this->_corp_secret);
		/** 获取 json 数据 */
		$data = array();
		if (!self::post($data, $url, '', ($force ? false : true))) {
			return false;
		}

		/** 如果返回了错误 */
		if (!isset($data['access_token']) || (isset($data['errcode']) && 0 != $data['errcode'])) {
			logger::error('url:'.$url."\taccess token error:".http_build_query($data));
			return false;
		}

		$expires_in = $data['expires_in'] ? $data['expires_in'] : $this->_expires_in;
		$this->_access_token = $data['access_token'];
		$this->_token_expires = startup_env::get('timestamp') + ($expires_in * 0.75);
		/** token 入库 */
		$serv = &service::factory('voa_s_oa_weixin_setting', array('pluginid' => 0));
		$serv->update(array(
			'ws_value' => $this->_access_token
		), array('ws_key' => 'access_token'));

		$serv->update(array(
			'ws_value' => $this->_token_expires
		), array('ws_key' => 'token_expires'));

		/** 更新 token 临时缓存 */
		voa_h_cache::get_instance()->remove('weixin', 'oa');

		return true;
	}

	/**
	 * 把 userid 转成 openid
	 * @param string $openid 用户的 openid
	 * @param string $userid 用户的 userid
	 * @param number $agentid 应用id
	 * @return boolean
	 */
	public function convert_to_openid(&$openid, $userid, $agentid = 0) {

		// 先获取 access token
		$this->get_access_token();
		// 生成获取 openid 的 url
		$url = sprintf(self::CONVERT_TO_OPENID_URL, $this->_access_token, $userid, $agentid);
		// 接口调用
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		$openid = $data['openid'];
		return true;
	}

	/** 获取指定用户的信息 */
	public function get_user_info($token, $code) {
		if ($this->userinfo) {
			return true;
		}

		/** 获取 json 数据 */
		$url = sprintf(self::USER_INFO_URL, $token, $code, startup_env::get('agentid'));
		$data = array();
		if (!self::post($data, $url)) {
			return false;
		}

		/** 如果返回了错误 */
		if (!isset($data['UserId'])) {
			logger::error('url:'.$url.'\tget user info error:'.http_build_query($data));
			return false;
		}

		/** 数组下标转成小写 */
		foreach ($data as $key => $val) {
			$key = $this->convert_key((string)$key);
			$this->userinfo[$key] = (string)$val;
		}

		return true;
	}

	/** 接收从微信过来的消息 */
	public function recv_msg() {

		/** 获取 xml 信息 */
		if (empty($this->_xml_from_wx)) {
			$this->_xml_from_wx = (string)file_get_contents("php://input");
		}

		logger::error("raw data:".$this->_xml_from_wx);
		/** 解析 */
		$xml = simplexml_load_string($this->_xml_from_wx);
		if (FALSE === $xml) {
			logger::error("xml error:".$this->_xml_from_wx);
			return false;
		}

		/** 把键/值都转成字串 */
		$res = array();
		foreach ($xml as $k => $v) {
			$res[(string)$k] = (string)$v;
		}

		return $res;
	}

	/**
	 * 根据 suite_id 读取 suite 记录
	 * @param string $suiteid 套件id
	 * @return boolean
	 */
	public function get_uc_suite($suiteid) {

		$this->uc_suite = voa_h_cache::get_instance()->get($suiteid, 'ucenter');
		// 如果缓存中的套件token已过期, 则重新获取
		if (empty($this->uc_suite) || empty($this->uc_suite['su_suite_access_token'])) {
			// 通过 rpc, 获取套件信息
			$url = config::get('voa.uc_url') . 'OaRpc/Rpc/Suite';
			if (!voa_h_rpc::query($this->uc_suite, $url, 'get_suite_token', $suiteid)) {
				logger::error('suiteid:'.$suiteid."\n".var_export($_GET, true));
				return false;
			}
		}

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
		/** 获取 json 数据 */
		if (!voa_h_func::get_json_by_post($data, $url, $post)) {
			return false;
		}

		/** 如果返回了错误 */
		if (isset($data['errcode']) && 0 != $data['errcode']) {
			logger::error('url:'.$url."\taccess token error:".http_build_query($data));
			/** 如果未重试并且是 access token 错误, 则重新尝试 */
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
		/** 强制重新获取 access token */
		$this->get_access_token(true);
		if ($this->_access_token == $token) {
			return false;
		}

		$data = array();
		return $this->post($data, $url, $post, false);
	}

	/**
	 * 获取授权链接
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function _oauth_url($url, $scope = 'snsapi_base', $state = '') {
		return sprintf(self::OAUTH_URL, $this->_corp_id, urlencode($url), $scope, $state);
	}

	/** 把驼峰转成以下划线分隔, 如:MsgType => msg_type */
	public function convert_key($key) {
		$key{0} = rstrtolower($key{0});
		$key = preg_replace("/([A-Z]+)/s", "_\\1", $key);
		return rstrtolower($key);
	}

	/**
	 * 验证微信要求的字符长度
	 * @param string $string 待验证的字符串
	 * @param array $rule 验证规则 array(unit, min, max)
	 * @param string $error_msg <strong style="color:red">(引用结果)</strong>验证错误信息
	 * @uses $rule = array(unit, min, max)<br />
	 * unit 长度单位，byte使用字节长，count使用字符数<br />
	 * min 最小长度<br />
	 * max 最大长度
	 * @return boolean
	 */
	protected function _validator_length($string, $rule, &$error_msg = '') {
		list($unit_type, $min, $max) = $rule;
		if (stripos($unit_type, 'byte') !== false) {
			// 使用字节长验证
			if (!validator::is_len_in_range($string, $min, $max)) {
				$error_msg = '长度应该介于 '.$min.'到'.$max.' 字节之间';
				return false;
			}
		} else {
			// 使用字符数验证
			if (!validator::is_string_count_in_range($string, $min, $max, 'utf-8')) {
				$error_msg = '长度应该介于 '.$min.'到'.$max.' 个字符之间';
				return false;
			}
		}

		return true;
	}

	/**
	 * 获取 jsapi-ticket
	 * @param boolean $force
	 * @return boolean
	 */
	public function get_jsapi_ticket($force = false) {

		// 读取最新的access token
		$this->get_access_token();

		if (!empty($this->_oa_suite)) {
			// 存在应用套件信息，则按套件来读取token

			// 检查套件jsapi-ticket是否过期
			if (!$force && !empty($this->_oa_suite['jsapi_ticket'])
					&& !empty($this->_oa_suite['jsapi_ticket_expire'])
						&& $this->_oa_suite['jsapi_ticket_expire'] >= startup_env::get('timestamp')) {
				$this->_jsapi_ticket = $this->_oa_suite['jsapi_ticket'];
				$this->_jsapi_ticket_expire = $this->_oa_suite['jsapi_ticket_expire'];
				return true;
			}

			$_access_token_expire = $this->_oa_suite['jsapi_ticket_expire'];

		} else {
			// 无套件

			// 自缓存读取 jsapi-ticket
			$sets = voa_h_cache::get_instance()->get('weixin', 'oa');
			if (!$force && !empty($sets['jsapi_ticket']) && !empty($sets['jsapi_ticket_expire'])
					&& $sets['jsapi_ticket_expire'] >= startup_env::get('timestamp')) {
				$this->_jsapi_ticket = $sets['jsapi_ticket'];
				$this->_jsapi_ticket_expire = $sets['jsapi_ticket_expire'];
				return true;
			}
			$_access_token_expire = $sets['token_expires'];
		}

		// 获取js api票据接口url
		$url = sprintf(self::JSAPI_TICKET_URL, $this->_access_token);
		// 获取json数据
		$data = array();
		if (!self::post($data, $url, '', ($force ? false : true))) {
			logger::error('get jsapi-ticket url: '.$url);
			return false;
		}
		// 获取ticket出错
		if (!isset($data['ticket'])) {
			logger::error('get jsapi-ticket url: '.$url.'|'.http_build_query($data));
			return false;
		}
		if (!isset($data['errcode']) || $data['errcode'] != 0) {
			logger::error('get jsapi-ticket url: '.$url.'|error:'.http_build_query($data));
			return false;
		}

		// 过期时间
		$expires_in = $data['expires_in'] ? $data['expires_in'] : $this->_jsapi_expires_in;
		$expires_in = startup_env::get('timestamp') + ($expires_in * 0.75);
		if ($expires_in > $_access_token_expire) {
			$expires_in = $_access_token_expire;
		}
		$this->_jsapi_ticket = $data['ticket'];
		$this->_jsapi_ticket_expire = $expires_in;

		// ticket 入库
		if (empty($this->_oa_suite)) {
			// 无套件方式
			$serv = &service::factory('voa_s_oa_weixin_setting', array('pluginid' => 0));
			$serv->update_setting(array(
				'jsapi_ticket' => $this->_jsapi_ticket,
				'jsapi_ticket_expire' => $this->_jsapi_ticket_expire
			));

			// 更新 weixin 临时缓存
			voa_h_cache::get_instance()->remove('weixin', 'oa');
		} else {
			// 套件方式

			$serv_suite = &service::factory('voa_s_oa_suite');
			$serv_suite->update(array(
				'jsapi_ticket' => $this->_jsapi_ticket,
				'jsapi_ticket_expire' => $this->_jsapi_ticket_expire
			), "`suiteid`='{$this->_oa_suite['suiteid']}'");
		}

	}

}

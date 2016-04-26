<?php
/**
 * controller_request
 *
 * $Author$
 * $Id$
 */
namespace Common\Common;
class Controller_request {

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * _params
	 * 附加的get参数
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * get_instance
	 *
	 * @return void
	 */
	public static function get_instance($params = array()) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($params);
		}
		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @paramarray $params
	 * @return void
	 */
	public function __construct($params = array()) {

		$this->_params = array_merge($_GET, $_POST, $params);
	}

	/**
	 * set_params
	 *
	 * @paramarray $params
	 * @return void
	 */
	public function set_params($params) {

		$this->_params = array_merge($this->_params, $params);
	}

	/**
	 * get
	 * 获取请求的GET参数
	 *
	 * @paramstring $key
	 * @parammixed $default key不存在时的默认值
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if (array_key_exists($key, $this->_params)) {
			return $this->_params[$key];
		}

		return $default;
	}

	/**
	 * getx
	 * 根据指定多个KEY返回相关数组
	 *
	 * @params string 要获取的keys，如果为空则返回所有GET参数
	 * @return array
	 */
	public function getx() {

		$keys = func_get_args();
		if (isset($keys[0]) && is_array($keys[0])) {
			$keys = $keys[0];
		}

		$result = array();

		if (!$keys) {
			return $this->_params;
		}

		foreach ($keys as $key) {
			if (array_key_exists($key, $this->_params)) {
				$result[$key] = $this->_params[$key];
			}
		}

		return $result;
	}

	/**
	 * post
	 * 获取请求的POST参数
	 *
	 * @param string $key
	 * @param mixed$default key不存在时的默认值
	 * @return mixed
	 */
	public function post($key, $default = null) {

		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}

		return $default;
	}

	/**
	 * postx
	 * 根据指定多个KEY返回相关数组
	 *
	 * @params string 要获取的keys，如果为空则返回所有POST参数
	 * @return array
	 */
	public function postx() {

		$keys = func_get_args();
		if (isset($keys[0]) && is_array($keys[0])) {
			$keys = $keys[0];
		}

		$result = array();

		if (!$keys) {
			return $_POST;
		}

		foreach ($keys as $key) {
			if (array_key_exists($key, $_POST)) {
				$result[$key] = $_POST[$key];
			}
		}

		return $result;
	}

	/**
	 * cookie
	 * 获取COOKIE值
	 *
	 * @param stirng $key
	 * @param mixed$default
	 * @return mixed
	 */
	public function cookie($key, $default = null) {
		if (array_key_exists($key, $_COOKIE)) {
			return $_COOKIE[$key];
		}

		return $default;
	}

	/**
	 * cookiex
	 * 据指定多个KEY返回相关数组
	 *
	 * @return array
	 */
	public function cookiex() {
		$keys = func_get_args();
		if (is_array($keys[0])) {
			$keys = $keys[0];
		}

		$result = array();

		if (!$keys) {
			return $_COOKIE;
		}

		foreach ($keys as $key) {
			if (array_key_exists($key, $_COOKIE)) {
				$result[$key] = $_COOKIE[$key];
			}
		}

		return $result;
	}

	/**
	 * server
	 *
	 * @param stirng $key
	 * @param mixed$default
	 * @return mixed
	 */
	public function server($key, $default = null) {
		if (array_key_exists($key, $_SERVER)) {
			return $_SERVER[$key];
		}
		return $default;
	}

	/**
	 * serverx
	 * 根据指定多个KEY返回相关数组
	 *
	 * @params string 要获取的keys，如果为空则返回所有$_SERVER参数
	 * @return array
	 */
	public function serverx() {
		$result = array();
		$keys = func_get_args();
		foreach ($keys as $key) {
			$result[$key] = $this->server($key);
		}
		return $result;
	}

	/**
	 * env
	 * 获取$_ENV环境变量
	 *
	 * @param string $key
	 * @param mixed$default
	 * @return mixed
	 */
	public function env($key, $default = null) {
		if (array_key_exists($key, $_ENV)) {
			return $_ENV[$key];
		}
		return $default;
	}

	/**
	 * envx
	 * 根据指定多个KEY返回相关数组
	 *
	 * @params string 要获取的keys，如果为空则返回所有$_ENV参数
	 * @return array
	 */
	public function evnx() {
		$result = array();
		$keys = func_get_args();
		foreach ($keys as $key) {
			$result[$key] = $this->env($key);
		}
		return $result;
	}

	/**
	 * get_method
	 * 获取HTTP请求方式
	 *
	 * @return string
	 */
	public function get_method() {
		return $this->server('REQUEST_METHOD');
	}

	/**
	 * is_post
	 * 判断是否为POST请求
	 *
	 * @return boolean
	 */
	public function is_post() {
		if ("POST" == $this->get_method()) {
			return true;
		}
		return false;
	}

	/**
	 * get_client_ip
	 * 获取客户端IP
	 *
	 * @return string
	 */
	public function get_client_ip() {

		$ip = FALSE;

		/** LVS接入时，通过QVIA获取真实IP */
		$qvia = $this->server('HTTP_QVIA');

		if ($qvia) {

			$ip = long2ip(hexdec(substr($qvia, 0, 8)));
			$_SERVER['REMOTE_ADDR'] = $ip;
			return $ip;
		}

		/** 直接IP */
		if ($this->server('HTTP_CLIENT_IP')) {
			$ip = $this->server('HTTP_CLIENT_IP');
		}

		/** nginx代理直接HTTP_X_REAL_IP */
		if ($this->server('HTTP_X_REAL_IP')) {
			$_SERVER['REMOTE_ADDR'] = $this->server('HTTP_X_REAL_IP');
		}

		/** 代理 */
		if ($this->server('HTTP_X_FORWARDED_FOR')) {

			$ips = explode (", ", $this->server('HTTP_X_FORWARDED_FOR'));
			if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }

			for ($i = 0; $i < count($ips); $i++) {
				if (!preg_match('/^(?:10|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./', $ips[$i])) {
					if (version_compare(phpversion(), "5.0.0", ">=")) {
						if (ip2long($ips[$i]) != false) {
							$ip = $ips[$i];
							break;
						}
					} else {
						if (ip2long($ips[$i]) != -1) {
							$ip = $ips[$i];
							break;
						}
					}
				}
			}
		}

		if (!$ip) {
			return $this->server('REMOTE_ADDR');
		}

		return $ip;
	}

	/**
	 * file
	 * 根据key获取$_FILES
	 *
	 * @paramstring $key
	 * @parammixed $default
	 * @return mixed
	 */
	public function file($key, $default = null) {
		if (array_key_exists($key, $_FILES)) {
			return $_FILES[$key];
		}
		return $default;
	}

	/**
	 * filex
	 * 根据多个KEY返回相关文件结果
	 *
	 * @return array
	 */
	public function filex() {
		$result = array();
		$keys = func_get_args();
		if (!$keys) {
			return $_FILES;
		}
		foreach ($keys as $key) {
			$result[$key] = $this->file($key);
		}
		return $result;
	}

	/**
	 * header
	 * 获取header
	 *
	 * @paramstring $key
	 * @parammixed $default
	 * @return mixed
	 */
	public function header($key, $default = null) {
		if (!$key) {
			return false;
		}

		$sKey = 'HTTP_'.strtoupper(str_replace('-', '_', $key));
		if (isset($_SERVER[$sKey])) {
			return $_SERVER[$sKey];
		}

		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (isset($headers[$key])) {
				return $headers[$key];
			}
		}

		return $default;
	}

	/**
	 * headerx
	 * 获取多个header值
	 *
	 * @return void
	 */
	public function headerx() {
		$result = array();
		$keys = func_get_args();
		if (!$keys) {
			return apache_request_headers();
		}
		foreach ($keys as $key) {
			$result[$key] = $this->header($key);
		}
		return $result;
	}

	/**
	 * is_xml_http_request
	 * 判断是否为ajax请求
	 *
	 * 目前支持判断如下js类库发起的ajax请求：
	 *+ Prototype 及 Prototype 系的类库（例如Scriptaculous）
	 *+ YUI
	 *+ jQuery
	 *+ MochiKit
	 *
	 * @return boolean
	 */
	public function is_xml_http_request() {
		return $this->header('X_Requested_With') == 'XMLHttpRequest';
	}

	/**
	 * xml_http_request_type
	 * 获取ajax请求类型
	 *
	 * 目前仅对jQuery有效
	 *
	 * @return void
	 */
	public function xml_http_request_type() {

		if ($this->is_xml_http_request()) {
			switch ($this->header('Accept')) {
				case 'application/json, text/javascript, */*':
					return 'json';
				case 'text/javascript, application/javascript, */*':
					return 'javascript';
				case 'text/html, */*':
					return 'html';
				case 'application/xml, text/xml, */*':
					return 'xml';
				case 'text/plain, */*':
					return 'text';
			}
		}

		return false;
	}

	/**
	 * is_robot
	 * 验证是不是搜索引擎
	 *
	 * @paramstring $user_agent
	 * @return boolean
	 */
	public function is_robot($user_agent = null) {

		$common_user_agents = array(
			'Alexa' => 'ia_archiver (+http://www.alexa.com/site/help/webmasters; crawler@alexa.com)',
			'Alibaba' => 'Yahoo! Slurp China',
			'Baidu' => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
			'Bing' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
			'Google' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
			'Msn' => 'msnbot-media/1.1 (+http://search.msn.com/msnbot.htm)',
			'Sitebot' => 'Mozilla/5.0 (compatible; SiteBot/0.1; +http://www.sitebot.org/robot/)',
			'Sogou' => 'Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)',
			'SogouVideo' => 'Sogou Video /3.0(+http://www.sogou.com/docs/help/webmasters.htm#07)',
			'Soso' => 'Sosospider+(+http://help.soso.com/webspider.htm)',
			'Yahoo' => 'Mozilla/5.0 (compatible; Yahoo! Slurp China; http://misc.yahoo.com.cn/help.html)',
			'Yandex' => 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
			'Yodao' => 'Mozilla/5.0 (compatible; YodaoBot/1.0; http://www.yodao.com/help/webmaster/spider/; )',
			'Youdao' => 'Mozilla/5.0 (compatible; YoudaoBot-rts/1.0; http://www.youdao.com/help/webmaster/spider/; )',
		);

		if (!$user_agent) {
			$user_agent = $this->server('HTTP_USER_AGENT');
		}

		if (!$user_agent) {
			return false;
		}

		foreach ($common_user_agents as $k => $v) {
			if ($user_agent == $v) {
				return $k;
			}
		}

		return false;
	}
}

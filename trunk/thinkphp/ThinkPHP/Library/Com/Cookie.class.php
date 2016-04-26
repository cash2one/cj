<?php
/**
 * Cookie.class.php
 * Cookie 操作
 * $Author$
 * $Id$
 */

namespace Com;

class Cookie {

	// Cookie 秘钥
	protected $_cookie_secret = null;
	// Cookie 域名
	protected $_domain = null;
	// Cookie 过期时长
	protected $_expire = null;
	// Cookie 目录
	protected $_path = '/';
	// Cookie 数据
	protected $_cookie_data = array();
	// 原生 Cookie 数据
	protected $_datax = array();
	// 待输出的 Cookie 数据
	protected $_writecookie_data = array();
	// 校验字串
	private $__auth = null;
	// Cookie 数据
	private $__data = array();
	// 实例
	private static $__instance = '';

	/**
	 * 获取当前实例
	 * @param string $domain 域名
	 * @param number $expire 有效时长
	 * @param string $cookie_secret 加密秘钥
	 */
	public static function &instance($domain = '', $expire = 3600, $cookie_secret = '') {

		if (! Cookie::$__instance) {
			Cookie::$__instance = new Cookie($domain, $expire, $cookie_secret);
		}

		return Cookie::$__instance;
	}

	/**
	 * 写入到COOKIE中的最终数据
	 * @param string $domain 域名
	 * @param number $expire 有效时长
	 * @param string $cookie_secret 加密秘钥
	 */
	private function __construct($domain = '', $expire = 3600, $cookie_secret = '') {

		// 域名为空时, 取当前域名
		if (! $domain) {
			$domain = cfg('COOKIE_DOMAIN');
			if (empty($domain)) {
				$domain = I('server.HTTP_HOST');
				if (-1 < ($pos = stripos($domain, ':'))) {
					$domain = substr($domain, 0, $pos);
				}
			}
		}

		// 有效时长
		if (! $expire) {
			$expire = 3600;
		}

		// 加密秘钥
		if (! $cookie_secret) {
			$cookie_secret = cfg('COOKIE_SECRET');
		}

		$this->_domain = $domain;
		$this->_expire = $expire;
		$this->_cookie_secret = $cookie_secret;
		$this->_datax = array();
		// 加密数据
		$this->_cookie_data = array(
			'WBS_AUTH' => isset($_COOKIE['WBS_AUTH']) ? $_COOKIE['WBS_AUTH'] : '',
			'WBS_DATA' => isset($_COOKIE['WBS_DATA']) ? $_COOKIE['WBS_DATA'] : '',
			'WBS_SESSIONID' => isset($_COOKIE['WBS_SESSIONID']) ? $_COOKIE['WBS_SESSIONID'] : ''
		);
		$this->__auth = $this->_cookie_data['WBS_AUTH'];
		$content = $this->_cookie_data['WBS_DATA'];
		$session_id = $this->_cookie_data['WBS_SESSIONID'];
		// 如果存在校验字串和 Cookie 数据
		if ($this->__auth && $content) {
			// 解密
			$data = $this->__cipher($content, 'DECODE', $this->_cookie_secret);
			// 验证数据正确性
			if ($this->__validate_auth($data, $this->_cookie_secret . $session_id, $this->__auth)) {
				parse_str($data, $this->__data);
			}
		}

		// 新用户
		if (! $this->_cookie_data || ! array_key_exists('WBS_SESSIONID', $_COOKIE)) {
			$token = md5(uniqid());
			$this->_cookie_data['WBS_SESSIONID'] = $token;
		}

		// 写 Cookie 头信息
		$this->_write();
	}

	/**
	 * 获取 Cookie
	 * @param string $key Cookie 名称
	 * @return Ambigous <>|NULL
	 */
	public function get($key) {

		$data = $this->__data;
		// 如果键值存在, 则返回对应的数据
		if (array_key_exists($key, $data)) {
			return $data[$key];
		}

		return null;
	}

	/**
	 * 设置 Cookie
	 * @param string $key 键名
	 * @param string $value 值
	 * @param string $expire 有效时长
	 * @param string $path Cookie 路径
	 * @param string $domain 域名
	 * @return boolean
	 */
	public function set($key, $value, $expire = null, $path = null, $domain = null) {

		// 如果没有 SESSIONID 和键名, 则返回 false
		if (! is_string($key) || ! array_key_exists('WBS_SESSIONID', $this->_cookie_data)) {
			return false;
		}

		// 有效时长
		if ($expire !== null) {
			$this->_expire = $expire;
		}

		// 路径
		if ($path !== null) {
			$this->_path = $path;
		}

		// 域名
		if ($domain !== null) {
			$this->_domain = $domain;
		}

		$this->__data[$key] = $value;
		return $this->_write($this->__data);
	}

	/**
	 * 取原生 Cookie
	 * @param string $key 键值
	 * @param string $default 默认值
	 * @return unknown|string
	 */
	public function getx($key, $default = null) {

		// 如果键值存在
		if ($key && array_key_exists($key, $_COOKIE)) {
			return $_COOKIE[$key];
		}

		return $default;
	}

	/**
	 * 设置原生 Cookie
	 * @param string $key 键值
	 * @param string $value 值
	 * @param number $expire 时长
	 * @param string $path 路径
	 * @param string $domain 域名
	 * @return boolean
	 */
	public function setx($key, $value = null, $expire = 0, $path = '', $domain = '') {

		// 键值不存在
		if (! $key) {
			return false;
		}

		// 域名
		if (! $domain) {
			$domain = $this->_domain;
		}

		// 路径
		if (! $path) {
			$path = $this->_path;
		}

		// 时长
		if (! $expire) {
			$expire = $this->_expire;
		}

		// 存入数据
		$this->_datax[$key] = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path,
			'domain' => $domain
		);
		$this->_write();
		return true;
	}

	/**
	 * 移除键值
	 * @param string $key 键值
	 * @return boolean
	 */
	public function remove($key) {

		// 键值为空
		if (! is_string($key)) {
			return false;
		}

		$data = $this->__data;
		if (array_key_exists($key, $this->__data)) { // 如果自定义键值存在
			unset($this->__data[$key]);
			$this->_write();
		} elseif (array_key_exists($key, $_COOKIE)) { // 普通 Cookie 的键值存在
			$this->setx($key, null, - 3600, $this->_path, $this->_domain);
		}

		return true;
	}

	// 清除 Cookie
	public function destroy() {

		$expired = strtotime('-100 day');
		$write_data['WBS_SESSIONID'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->_path,
			'domain' => $this->_domain
		);
		$write_data['WBS_AUTH'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->_path,
			'domain' => $this->_domain
		);
		$write_data['WBS_DATA'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->_path,
			'domain' => $this->_domain
		);
		$this->_cookie_data['WBS_AUTH'] = '';
		$this->_cookie_data['WBS_SESSIONID'] = '';
		$this->_cookie_data['WBS_DATA'] = '';
		$this->__data = array();
		$this->_writecookie_data = $write_data;
	}

	/**
	 * 写 Cookie
	 * @param string $data Cookie 数据
	 * @return boolean
	 */
	protected function _write($data = null) {

		$write_data = array();
		// 读取 SESSIONID
		$session_id = $this->_cookie_data['WBS_SESSIONID'];
		// 合并数据
		if ($data && is_array($data)) {
			$this->__data = array_merge($this->__data, $data);
		}

		$content = http_build_query($this->__data);
		// 验证字串
		$this->_cookie_data['WBS_AUTH'] = $this->__generate_auth($content, $this->_cookie_secret . $session_id);
		// 加密
		$this->_cookie_data['WBS_DATA'] = $this->__cipher($content, 'ENCODE', $this->_cookie_secret);
		$cookie_expired = time() + $this->_expire;
		// SESSIONID
		$write_data['WBS_SESSIONID'] = array(
			'value' => $session_id,
			'expired' => $cookie_expired,
			'path' => $this->_path,
			'domain' => $this->_domain
		);

		// 校验字串
		if ($this->_cookie_data['WBS_AUTH']) {
			$write_data['WBS_AUTH'] = array(
				'value' => $this->_cookie_data['WBS_AUTH'],
				'expired' => $cookie_expired,
				'path' => $this->_path,
				'domain' => $this->_domain
			);
		}

		// Cookie 数据
		if ($this->_cookie_data['WBS_DATA']) {
			$write_data['WBS_DATA'] = array(
				'value' => $this->_cookie_data['WBS_DATA'],
				'expired' => $cookie_expired,
				'path' => $this->_path,
				'domain' => $this->_domain
			);
		}

		// 处理原始数据
		if ($this->_datax) {
			// 遍历原始数据数组
			foreach ($this->_datax as $key => $value) {
				$write_data[$key] = array(
					'value' => $value['value'],
					'expired' => time() + $value['expire'],
					'path' => $value['path'],
					'domain' => $value['domain']
				);
			}
		}

		$this->_writecookie_data = $write_data;
		return true;
	}

	// 获取 cookie 数据
	public function get_cookie_data() {

		return $this->_writecookie_data;
	}

	/**
	 * 清除/刷新缓存时调用
	 * @param string $buffer 缓存字串
	 * @return string
	 */
	public function send($buffer = null) {

		// Cookie 数据
		$write_data = $this->_writecookie_data;
		if ($write_data && is_array($write_data)) {
			$i = 1;
			// 遍历待写的 Cookie
			foreach ($write_data as $name => $item) {
				if ($i == 1) {
					$this->__set_cookie($name, $item['value'], $item['expired'], $item['path'], $item['domain'], true);
				} else {
					$this->__set_cookie($name, $item['value'], $item['expired'], $item['path'], $item['domain'], false);
				}

				$i ++;
			}
		}

		return $buffer;
	}

	/**
	 * 设置 Cookie
	 * @param string $name Cookie 名称
	 * @param string $value Cookie 值
	 * @param number $expired 有效时长
	 * @param string $path Cookie 路径
	 * @param string $domain Cookie 域名
	 * @param string $replace 是否替换类似 Cookie
	 * @return boolean
	 */
	private function __set_cookie($name, $value = '', $expired = 0, $path = '/', $domain = '', $replace = false) {

		$cook_str = 'Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value) . '; expires=' . gmdate('D, d-M-Y H:i:s \G\M\T', $expired) . '; path=' . $path . '; domain=' . $domain;
		header($cook_str, $replace);
		//setcookie($name, $value, $expired, $path, $domain);
		return true;
	}

	/**
	 * 生成校验字串
	 * @param array $data 数据
	 * @param string $cookie_secret 干扰字串
	 * @return string
	 */
	private function __generate_auth($data, $cookie_secret) {

		return md5($data . $cookie_secret);
	}

	/**
	 * 验证校验字串的正确性
	 * @param array $data 数据
	 * @param string $cookie_secret 干扰字串
	 * @param string $auth_key 校验字串
	 * @return boolean
	 */
	private function __validate_auth($data, $cookie_secret, $auth_key) {

		return $auth_key == $this->__generate_auth($data, $cookie_secret);
	}

	/**
	 * 加/解密
	 * @param string $string 待加/解密字串
	 * @param string $operation 操作(ENCODE/DECODE)
	 * @param string $key 秘钥
	 * @return string
	 */
	private function __cipher($string, $operation, $key = '') {

		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		// 生成随机串
		for($i = 0; $i <= 255; $i ++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}

		// 根据随机串重新生成密码对照表
		for($j = $i = 0; $i < 256; $i ++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		// 加/解密
		for($a = $j = $i = 0; $i < $string_length; $i ++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		// 如果是解密操作
		if ($operation == 'DECODE') {
			// 剔除干扰字串
			if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}
	}

}

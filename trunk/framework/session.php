<?php
/**
 * session
 *
 * $Author$
 * $Id$
 */

class session {

	protected $private_key = null;  /** 验证串加密公钥 */

	protected $domain = null;

	protected $expire = null;

	protected $path = '/';

	protected $cookie_data = array();

	private $_auth = null;

	private $_data = array();

	protected $_datax = array();

	private static $_instance = '';

	protected $writecookie_data = array();  /** 写入到COOKIE中的最终数据 */

	private function __construct($domain = '', $expire = 3600, $private_key = 'vchangyi') {
		if (!$domain) {
			$domain = $_SERVER['HTTP_HOST'];
		}

		if (!$expire) {
			$expire = 3600;
		}

		if (!$private_key) {
			$private_key = 'wbs_secret_key_#!@K#JK@!#';
		}

		$this->domain = $domain;
		$this->expire = $expire;
		$this->private_key = $private_key;
		$this->_datax = array();
		$this->cookie_data = array(
			'WBS_AUTH' => isset($_COOKIE['WBS_AUTH']) ? $_COOKIE['WBS_AUTH'] : '',
			'WBS_DATA' => isset($_COOKIE['WBS_DATA']) ? $_COOKIE['WBS_DATA'] : '',
			'WBS_SESSIONID' => isset($_COOKIE['WBS_SESSIONID']) ? $_COOKIE['WBS_SESSIONID'] : ''
		);

		$this->_auth = $this->cookie_data['WBS_AUTH'];
		$content = $this->cookie_data['WBS_DATA'];
		$session_id = $this->cookie_data['WBS_SESSIONID'];

		if ($this->_auth && $content) {
			$data = $this->_cipher($content, 'DECODE', $this->private_key);
			if ($this->_validate_auth_key($data, $this->private_key.$session_id, $this->_auth)) {
				parse_str($data, $this->_data);
			}
		}

		// 清理旧cookie;
		setcookie('WBS_AUTH', '', -1, '/', $_SERVER['HTTP_HOST']);
		setcookie('WBS_DATA', '', -1, '/', $_SERVER['HTTP_HOST']);
		setcookie('WBS_SESSIONID', '', -1, '/', $_SERVER['HTTP_HOST']);

		/** 新用户 */
		if (!$this->cookie_data || !array_key_exists('WBS_SESSIONID', $_COOKIE)) {
			$token = md5(uniqid());
			$this->cookie_data['WBS_SESSIONID'] = $token;
		}
		$this->write();
	}

	public static function &get_instance($domain = '', $expire = 3600, $private_key = 'vchangyi') {
		if (!session::$_instance) {
			session::$_instance = new session($domain, $expire, $private_key);
		}
		return session::$_instance;
	}

	public function get($key) {
		$data = $this->_data;
		if (array_key_exists($key, $data)) {
			return $data[$key];
		}
		return null;
	}

	public function set($key, $value, $expire = null, $path = null, $domain = null) {
		if (!is_string($key) || !array_key_exists('WBS_SESSIONID', $this->cookie_data)) {
			return false;
		}

		if ($expire !== null) {
			$this->expire = $expire;
		}
		if ($path !== null) {
			$this->path = $path;
		}
		if ($domain !== null) {
			$this->domain = $domain;
		}

		$this->_data[$key] = $value;
		return $this->write($this->_data);
	}

	public function getx($key, $default = null) {
		if ($key && array_key_exists($key, $_COOKIE)) {
			return $_COOKIE[$key];
		}
		return $default;
	}

	public function setx($key, $value = null, $expire = 0, $path = '', $domain = '') {
		if (!$key) {
			return false;
		}

		if (!$domain) {
			$domain = $this->domain;
		}

		if (!$path) {
			$path = $this->path;
		}

		if (!$expire) {
			$expire = $this->expire;
		}

		$this->_datax[$key] = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path,
			'domain' => $domain
		);

		$this->write();
		return true;
	}

	public function remove($key) {
		if (!is_string($key)) {
			return false;
		}

		$data = $this->_data;

		if (array_key_exists($key, $this->_data)) {
			unset($this->_data[$key]);
			$this->write();
		} elseif (array_key_exists($key, $_COOKIE))  {
			$this->setx($key, null, -3600, $this->path, $this->domain);
		}
		return true;
	}

	public function destroy() {
		$expired = strtotime('-100 day');
		$write_data['WBS_SESSIONID'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->path,
			'domain' => $this->domain
		);
		$write_data['WBS_AUTH'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->path,
			'domain' => $this->domain
		);
		$write_data['WBS_DATA'] = array(
			'value' => '',
			'expired' => $expired,
			'path' => $this->path,
			'domain' => $this->domain
		);

		$this->cookie_data['WBS_AUTH'] = '';
		$this->cookie_data['WBS_SESSIONID'] = '';
		$this->cookie_data['WBS_DATA'] = '';
		$this->_data = array();

		$this->writecookie_data = $write_data;
	}

	protected function write($data = null) {
		$write_data = array();
		$session_id = $this->cookie_data['WBS_SESSIONID'];

		if ($data && is_array($data)) {
			$this->_data = array_merge($this->_data, $data);
		}

		$content = http_build_query($this->_data);
		$this->cookie_data['WBS_AUTH'] = $this->_generate_auth_key($content, $this->private_key.$session_id);
		$this->cookie_data['WBS_DATA'] = $this->_cipher($content, 'ENCODE', $this->private_key);
		$cookie_expired = time() + $this->expire;

		$write_data['WBS_SESSIONID'] = array(
			'value' => $session_id,
			'expired' => $cookie_expired,
			'path' => $this->path,
			'domain' => $this->domain
		);
		if ($this->cookie_data['WBS_AUTH']) {
			$write_data['WBS_AUTH'] = array(
				'value' => $this->cookie_data['WBS_AUTH'],
				'expired' => $cookie_expired,
				'path' => $this->path,
				'domain' => $this->domain
			);
		}

		if ($this->cookie_data['WBS_DATA']) {
			$write_data['WBS_DATA'] = array(
				'value' => $this->cookie_data['WBS_DATA'],
				'expired' => $cookie_expired,
				'path' => $this->path,
				'domain' => $this->domain
			);
		}

		/** 处理原始数据 */
		if ($this->_datax) {
			foreach ($this->_datax as $key => $value) {
				$write_data[$key] = array(
					'value' => $value['value'],
					'expired' => time() + $value['expire'],
					'path' => $value['path'],
					'domain' => $value['domain']
				);
			}
		}

		$this->writecookie_data = $write_data;

		return true;
	}

	public function send($buffer = null) {
		$write_data = $this->writecookie_data;

		if ($write_data && is_array($write_data)) {
			$i = 1;
			foreach ($write_data as $name => $item) {
				if ($i == 1) {
					$this->_setCookie($name, $item['value'], $item['expired'], $item['path'], $item['domain'], true);
				} else {
					$this->_setCookie($name, $item['value'], $item['expired'], $item['path'], $item['domain'], false);
				}
				$i ++;
			}
		}

		return $buffer;
	}

	private function _setCookie($name, $value = '', $expired = 0, $path = '/', $domain = '', $replace = false) {
		$cook_str = 'Set-Cookie: '.rawurlencode($name).'='.rawurlencode($value)
			. '; expires='.gmdate('D, d-M-Y H:i:s \G\M\T', $expired)
			. '; path='.$path
			. '; domain='.$domain;

		header($cook_str, $replace);
		//setcookie($name, $value, $expired, $path, $domain);
	}

	private function _generate_auth_key($data, $private_key) {
		return md5($data.$private_key);
	}

	private function _validate_auth_key($data, $private_key, $authKey) {
		return $authKey == $this->_generate_auth_key($data, $private_key);
	}

	private function _cipher($string, $operation, $key = '') {
		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if ($operation == 'DECODE') {
			if (substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}
	}

}

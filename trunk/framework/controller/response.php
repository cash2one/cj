<?php
/**
 * controller_response
 *
 * $Author$
 * $Id$
 */

class controller_response {

	/**
	 * 响应内容
	 *
	 * @var array
	 */
	protected $_body = array();

	/**
	 * 使用数组形式设置的header
	 *
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * 使用字符串直接设置的header数组
	 *
	 * @var array
	 */
	protected $_headers_raw = array();

	/**
	 * 响应的HTTP代码
	 *
	 * @var int
	 */
	protected $_http_response_code = 200;

	/**
	 * _is_redirect
	 * 标志当前是否为跳转
	 *
	 * @var boolean
	 */
	protected $_is_redirect = false;

	/**
	 * 如果headers已经发送，是否抛出异常
	 *
	 * @see can_send_headers()
	 * @var boolean
	 */
	public $headers_sent_throws_exception = true;

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * _response_sent
	 * 响应是否已发送
	 *
	 * @var boolean
	 */
	protected $_response_sent = false;

	/**
	 * __construct
	 *
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * __clone
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * get_instance
	 *
	 * @return void
	 */
	public static function get_instance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 格式化header name
	 *
	 * Normalizes a header name to X-Capitalized-Names
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function _normalize_header($name) {

		$filtered = str_replace(array('-', '_'), ' ', (string) $name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}

	/**
	 * set_header
	 * 设置头，例如set_header('Content-type', 'text/html');
	 *
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace
	 * @return object controller_response
	 */
	public function set_header($name, $value, $replace = false) {

		$this->can_send_headers(true);
		$name = $this->_normalize_header($name);
		$value = (string) $value;

		if ($replace) {
			foreach ($this->_headers as $key => $header) {
				if ($name == $header['name']) {
					unset($this->_headers[$key]);
				}
			}
		}

		$this->_headers[] = array(
			'name' => $name,
			'value' => $value,
			'replace' => $replace
		);

		return $this;
	}

	/**
	 * set_redirect
	 * 设置跳转URL
	 *
	 * @param string $url
	 * @param int $code
	 * @return object controller_response
	 */
	public function set_redirect($url, $code = 302) {

		$this->can_send_headers(true);
		$this->set_header('Location', $url, true)->set_http_response_code($code);

		return $this;
	}

	/**
	 * set_cookie
	 * 设置cookie
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  integer $expired
	 * @param  string $path
	 * @param  string $domain
	 * @return void
	 */
	public function set_cookie($name, $value, $expired = 0, $path = '/', $domain = null) {

		setCookie($name, $value, $expired, $path, $domain);

		return $this;
	}

	/**
	 * is_redirect
	 * 判断是否为跳转
	 *
	 * @return boolean
	 */
	public function is_redirect() {

		return $this->_is_redirect;
	}

	/**
	 * get_headers
	 * 获得headers
	 *
	 * @return array
	 */
	public function get_headers() {

		return $this->_headers;
	}

	/**
	 * clear_headers
	 * 清空headers
	 *
	 * @return controller_response
	 */
	public function clear_headers() {

		$this->_headers = array();
		return $this;
	}

	/**
	 * clear_header
	 * 清空某个header
	 *
	 * @param  string $name
	 * @return controller_response
	 */
	public function clear_header($name) {

		if (! count($this->_headers)) {
			return $this;
		}

		foreach ($this->_headers as $index => $header) {
			if ($name == $header['name']) {
				unset($this->_headers[$index]);
			}
		}

		return $this;
	}

	/**
	 * Set raw HTTP header
	 * 设置一个源字符串数据的header例如：set_header("Content-Type: text/html")
	 *
	 * @param string $value
	 * @return controller_response
	 */
	public function set_raw_header($value) {

		$this->can_send_headers(true);
		if ('Location' == substr($value, 0, 8)) {
			$this->_is_redirect = true;
		}
		$this->_headers_raw[] = (string) $value;
		return $this;
	}

	/**
	 * 获取使用源数据设置的header
	 *
	 * @return array
	 */
	public function get_raw_headers() {

		return $this->_headers_raw;
	}

	/**
	 * 清空所有使用源数据设置的header
	 *
	 * @return controller_response
	 */
	public function clear_raw_headers() {

		$this->_headers_raw = array();
		return $this;
	}

	/**
	 * 清空某个使用源数据设置的header
	 *
	 * @param  string $header_raw
	 * @return controller_response
	 */
	public function clear_raw_header($header_raw) {

		if (! count($this->_headers_raw)) {
			return $this;
		}

		$key = array_search($header_raw, $this->_headers_raw);
		unset($this->_headers_raw[$key]);

		return $this;
	}

	/**
	 * 清空所有header
	 *
	 * @return controller_response
	 */
	public function clear_all_headers() {

		return $this->clear_headers()->clear_raw_headers();
	}

	/**
	 * 设置HTTP code
	 *
	 * @param int $code
	 * @return controller_response
	 */
	public function set_http_response_code($code) {

		if (!is_int($code) || (100 > $code) || (599 < $code)) {
			throw new controller_exception('Invalid HTTP response code');
		}

		if ((300 <= $code) && (307 >= $code)) {
			$this->_is_redirect = true;
		} else {
			$this->_is_redirect = false;
		}

		$this->_http_response_code = $code;
		return $this;
	}

	/**
	 * 获取响应的HTTP code
	 *
	 * @return int
	 */
	public function get_http_response_code() {

		return $this->_http_response_code;
	}

	/**
	 * 判断是否可以发送header
	 *
	 * @param boolean $throw 如果header已经发送，是否抛出异常
	 * @return boolean
	 * @throws controller_exception
	 */
	public function can_send_headers($throw = false) {

		$ok = headers_sent($file, $line);
		if ($ok && $throw && $this->headers_sent_throws_exception) {
			throw new controller_exception('Cannot send headers; headers already sent in '.$file.', line '.$line);
		}

		return !$ok;
	}

	/**
	 * send_headers
	 * 发送headers
	 *
	 * @return controller_response
	 */
	public function send_headers() {

		/** Only check if we can send headers if we have headers to send */
		if (count($this->_headers_raw) || count($this->_headers) || (200 != $this->_http_response_code)) {
			$this->can_send_headers(true);
		} elseif (200 == $this->_http_response_code) {
			/** Haven't changed the response code, and we have no headers */
			return $this;
		}

		$http_code_sent = false;

		foreach ($this->_headers_raw as $header) {
			if (!$http_code_sent && $this->_http_response_code) {
				header($header, true, $this->_http_response_code);
				$http_code_sent = true;
			} else {
				header($header);
			}
		}

		foreach ($this->_headers as $header) {
			if (!$http_code_sent && $this->_http_response_code) {
				header($header['name'].': '.$header['value'], $header['replace'], $this->_http_response_code);
				$http_code_sent = true;
			} else {
				header($header['name'].': '.$header['value'], $header['replace']);
			}
		}

		if (!$http_code_sent) {
			header('HTTP/1.1 '.$this->_http_response_code);
			$http_code_sent = true;
		}

		return $this;
	}

	/**
	 * set_body
	 * 设置响应体的内容
	 *
	 * If $name is not passed, or is not a string, resets the entire body and
	 * sets the 'default' key to $content.
	 *
	 * If $name is a string, sets the named segment in the body array to
	 * $content.
	 *
	 * @param string $content
	 * @param null|string $name
	 * @return controller_response
	 */
	public function set_body($content, $name = null) {

		if ((null === $name) || !is_string($name)) {
			$this->_body = array('default' => (string) $content);
		} else {
			$this->_body[$name] = (string) $content;
		}

		return $this;
	}

	/**
	 * Append content to the body content
	 * 附加响应体内容
	 *
	 * @param string $content
	 * @param null|string $name
	 * @return controller_response
	 */
	public function append_body($content, $name = null) {

		if ((null === $name) || !is_string($name)) {
			if (isset($this->_body['default'])) {
				$this->_body['default'] .= (string) $content;
			} else {
				return $this->append('default', $content);
			}
		} elseif (isset($this->_body[$name])) {
			$this->_body[$name] .= (string) $content;
		} else {
			return $this->append($name, $content);
		}

		return $this;
	}

	/**
	 * 清空响应体
	 *
	 * With no arguments, clears the entire body array. Given a $name, clears
	 * just that named segment; if no segment matching $name exists, returns
	 * false to indicate an error.
	 *
	 * @param  string $name Named segment to clear
	 * @return boolean
	 */
	public function clear_body($name = null) {

		if (null !== $name) {
			$name = (string) $name;
			if (isset($this->_body[$name])) {
				unset($this->_body[$name]);
				return true;
			}

			return false;
		}

		$this->_body = array();
		return true;
	}

	/**
	 * 获取响应内容
	 *
	 * If $spec is false, returns the concatenated values of the body content
	 * array. If $spec is boolean true, returns the body content array. If
	 * $spec is a string and matches a named segment, returns the contents of
	 * that segment; otherwise, returns null.
	 *
	 * @param boolean $spec
	 * @return string|array|null
	 */
	public function get_body($spec = false) {

		if (false === $spec) {
			ob_start();
			$this->output_body();
			return ob_get_clean();
		} elseif (true === $spec) {
			return $this->_body;
		} elseif (is_string($spec) && isset($this->_body[$spec])) {
			return $this->_body[$spec];
		}

		return null;
	}

	/**
	 * 在最后附加响应体
	 *
	 * If segment already exists, replaces with $content and places at end of
	 * array.
	 *
	 * @param string $name
	 * @param string $content
	 * @return controller_response
	 */
	public function append($name, $content) {

		if (!is_string($name)) {
			throw new controller_exception('Invalid body segment key ("'.gettype($name).'")');
		}

		if (isset($this->_body[$name])) {
			unset($this->_body[$name]);
		}
		$this->_body[$name] = (string) $content;
		return $this;
	}

	/**
	 * 在最前附加响应体
	 *
	 * If segment already exists, replaces with $content and places at top of
	 * array.
	 *
	 * @param string $name
	 * @param string $content
	 * @return void
	 */
	public function prepend($name, $content) {

		if (!is_string($name)) {
			throw new controller_exception('Invalid body segment key ("'.gettype($name).'")');
		}

		if (isset($this->_body[$name])) {
			unset($this->_body[$name]);
		}

		$new = array($name => (string) $content);
		$this->_body = $new + $this->_body;

		return $this;
	}

	/**
	 * 向整个响应中插入数据
	 *
	 * @param  string $name
	 * @param  string $content
	 * @param  string $parent
	 * @param  boolean $before Whether to insert the new segment before or
	 * after the parent. Defaults to false (after)
	 * @return controller_response
	 */
	public function insert($name, $content, $parent = null, $before = false) {

		if (!is_string($name)) {
			throw new controller_exception('Invalid body segment key ("'.gettype($name).'")');
		}

		if ((null !== $parent) && !is_string($parent)) {
			throw new controller_exception('Invalid body segment parent key ("'.gettype($parent).'")');
		}

		if (isset($this->_body[$name])) {
			unset($this->_body[$name]);
		}

		if ((null === $parent) || !isset($this->_body[$parent])) {
			return $this->append($name, $content);
		}

		$ins = array($name => (string) $content);
		$keys = array_keys($this->_body);
		$loc = array_search($parent, $keys);
		if (!$before) {
			/** Increment location if not inserting before */
			++$loc;
		}

		if (0 === $loc) {
			/** If location of key is 0, we're prepending */
			$this->_body = $ins + $this->_body;
		} elseif ($loc >= (count($this->_body))) {
			/** If location of key is maximal, we're appending */
			$this->_body = $this->_body + $ins;
		} else {
			/** Otherwise, insert at location specified */
			$pre = array_slice($this->_body, 0, $loc);
			$post = array_slice($this->_body, $loc);
			$this->_body = $pre + $ins + $post;
		}

		return $this;
	}

	/**
	 * 输出响应体内容
	 *
	 * @return void
	 */
	public function output_body() {

		$body = implode('', $this->_body);
		echo $body;
	}

	/**
	 * output_image
	 *
	 * @param  mixed $file
	 * @return void
	 */
	public function output_image($file) {

		if ($this->_response_sent) {
			return false;
		}

		$this->send_headers();

		if (is_readable($file)) {
			readfile($file);
		}

		$this->_response_sent = true;
	}

	/**
	 * 输出响应
	 *
	 * @return void
	 */
	public function send_response() {

		if ($this->_response_sent) {
			return false;
		}

		$this->send_headers();
		$this->output_body();
		$this->_response_sent = true;
	}

	public function stop() {

		$this->send_response();
		exit;
	}

	/**
	 * 允许直接输出响应
	 *
	 * @return string
	 */
	public function __toString() {

		ob_start();
		$this->send_response();
		return ob_get_clean();
	}

}

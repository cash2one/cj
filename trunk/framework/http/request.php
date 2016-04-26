<?php
/**
 * http request
 *
 * $Author$
 * $Id$
 */

class http_request {
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_GET = 'GET';
	const METHOD_HEAD = 'HEAD';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_TRACE = 'TRACE';
	const METHOD_CONNECT = 'CONNECT';

	/** 请求的地址 */
	protected $_url;
	/** 请求方式 */
	protected $_method;
	/** http 版本 */
	protected $_version;
	/** post 请求参数 */
	protected $_body;
	/** 头部 */
	protected $_headers = array(
		'user-agent' => 'vcy/1.0',
	);
	/** post 请求参数 */
	protected $_post_params = array();
	/** 配置 */
	protected $_config = array(
		'connect_timeout' => 10,
		'timeout' => 15,
		'dnscache' => false,
		'proxy' => false,
		'http_proxy' => false,
		'maxredirs' => 0
	);

	public function __construct($url = null, $method = self::METHOD_GET, array $config = array()) {
		$this->_url = $url;
		$this->_method = $method;
		$this->set_config($config);
	}

	/**
	 * 配置设置
	 * @param string $key 键值
	 * @param string $value 值
	 */
	public function set_config($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k=>$v) {
				$this->set_config($k, $v);
			}
		} else {
			$this->_config[$key] = $value;
		}
	}

	public function get_config($name) {
		if (array_key_exists($name, $this->_config)) {
			return $this->_config[$name];
		}
		return null;
	}

	/**
	 * 设置 url
	 * @param string $url 请求的 url
	 */
	public function set_url($url) {
		$this->_url = $url;
	}

	public function get_url() {
		return $this->_url;
	}

	/**
	 * Sets request header(s)
	 *
	 * The first parameter may be either a full header string 'header: value' or
	 * header name. In the former case $value parameter is ignored, in the latter-
	 * the header's value will either be set to $value or the header will be
	 * removed if $value is null. The first parameter can also be an array of
	 * headers, in that case method will be called recursively.
	 *
	 * Note that headers are treated case insensitively as per RFC 2616.
	 *-
	 * <code>
	 * $req->set_header('Foo: Bar'); // sets the value of 'Foo' header to 'Bar'
	 * $req->set_header('FoO', 'Baz'); // sets the value of 'Foo' header to 'Baz'
	 * $req->set_header(array('foo' => 'Quux')); // sets the value of 'Foo' header to 'Quux'
	 * $req->set_header('FOO'); // removes 'Foo' header from request
	 * </code>
	 *
	 * @param	string|array header name, header string ('Header: value') or an array of headers
	 * @param	string|null	 header value, header will be removed if null
	 * @return   http_request
	 * @throws   http_request_exception
	 */
	public function set_header($name, $value = null) {
		if (is_array($name)) {
			foreach ($name as $k => $v) {
				if (is_string($k)) {
					$this->set_header($k, $v);
				} else {
					$this->set_header($v);
				}
			}
		} else {
			if (!$value && strpos($name, ':')) {
				list($name, $value) = array_map('trim', explode(':', $name, 2));
			}

			/** Header name should be a token: http://tools.ietf.org/html/rfc2616#section-4.2 */
			if (preg_match('![\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]!', $name)) {
				throw new http_request_exception("Invalid header name '{$name}'");
			}

			/** Header names are case insensitive anyway */
			$name = strtolower($name);
			if (!$value) {
				unset($this->_headers[$name]);
			} else {
				$this->_headers[$name] = $value;
			}
		}
		return $this;
	}


	/**
	 * Returns the request headers
	 *
	 * The array is of the form ('header name' => 'header value'), header names
	 * are lowercased
	 *
	 * @return   array
	 */
	public function get_headers() {
		return $this->_headers;
	}

	/**
	 * Appends a cookie to "Cookie:" header
	 *
	 * @param	string  cookie name
	 * @param	string  cookie value
	 * @return   http_request
	 * @throws   http_request_exception
	 */
	public function add_cookie($name, $value) {
		$cookie = $name.'='.$value;
		/** Disallowed characters: http://cgi.netscape.com/newsref/std/cookie_spec.html */
		if (preg_match('/[\s;]/', $cookie)) {
			throw new http_request_exception("Invalid cookie: '{$cookie}'");
		}

		if (array_key_exists('cookie', $this->_headers)) {
			$cookies = $this->_headers['cookie'].'; ';
		}

		$this->set_header('cookie', $cookies.$cookie);

		return $this;
	}


	/**
	 * Sets the request method
	 *
	 * @param	string
	 * @return   http_request
	 * @throws   http_request_exception if the method name is invalid
	 */
	public function set_method($method) {
		/** Method name should be a token: http://tools.ietf.org/html/rfc2616#section-5.1.1 */
		if (preg_match('![\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]!', $method)) {
			throw new http_request_exception("Invalid request method '{$method}'");
		}

		$this->_method = $method;
		return $this;
	}

	/**
	 * Returns the request method
	 *
	 * @return   string
	 */
	public function get_method() {
		return $this->_method;
	}

	/**
	 * Adds POST parameter(s) to the request.
	 *
	 * @param	string|array	parameter name or array ('name' => 'value')
	 * @param	mixed		   parameter value (can be an array)
	 * @return   http_request
	 */
	public function add_post_parameter($name, $value = null) {
		if (!is_array($name)) {
			$this->_post_params[$name] = $value;
		} else {
			foreach ($name as $k => $v) {
				$this->add_post_parameter($k, $v);
			}
		}

		if (!array_key_exists('content-type', $this->_headers)) {
			$this->set_header('content-type', 'application/x-www-form-urlencoded');
		}

		return $this;
	}

	function proxy_rewrite($prefix, $name, $suffix, &$hostname) {
		$hostname = $name;
		$name = $this->_config['proxy'];
		return $prefix.$name.$suffix;
	}

	function host_resolve($prefix, $name, $suffix, &$hostname) {
		$hostname = $name;
		if ($this->_config['dnscache']) {
			$key = 'host_'.$name;
			$host = apc_fetch($key);
			if ($host) {
				return $prefix.$host.$suffix;
			}
		}

		$host = gethostbyname($name);
		if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $host)) {
			if ($this->_config['dnscache']) {
				apc_store($key, $host, 3600);
			}

			return $prefix.$host.$suffix;
		}

		return $prefix.$name.$suffix;
	}

	/**
	 * set_version
	 * 设置http版本
	 *
	 * @param  mixed $version
	 * @return void
	 */
	public function set_version($version) {

		switch ($version) {
			case '1.0':
				$this->_version = '1.0';
				break;
			case '1.1':
			default:
				$this->_version = '1.1';
		}
	}

	public function send() {

		$ch = curl_init();
		switch ($this->_version) {
			case '1.1':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				break;
			case '1.0':
			default:
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				break;
		}

		if ($this->_config['proxy']) {
			$url = preg_replace('/(https?:\/\/)([\w\.\-]+)(\/?)/e', '$this->proxy_rewrite("\1", "\2", "\3", $hostname)', $this->_url);
			$this->_headers['host'] = $hostname;
		} else {
			if ($this->_config['dnscache']) {
				$url = preg_replace('/(https?:\/\/)([\w\.\-]+)(\/?)/e', '$this->host_resolve("\1", "\2", "\3", $hostname)', $this->_url);
				$this->_headers['host'] = $hostname;
			} else {
				$url = $this->_url;
			}
		}

		/** 新增http proxy的支持 */
		if ($this->_config['http_proxy']) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($ch, CURLOPT_PROXY, $this->_config['http_proxy']);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		/** set headers not having special keys */
		$headers_fmt = array();
		foreach ($this->_headers as $name => $value) {
			$canonical_name = implode('-', array_map('ucfirst', explode('-', $name)));
			$headers_fmt[] = $canonical_name.': '.$value;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_fmt);
		if (array_key_exists('cookie', $this->_headers)) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->_headers['cookie']);
		}

		if (array_key_exists('user-agent', $this->_headers)) {
			curl_setopt($ch, CURLOPT_USERAGENT, $this->_headers['user-agent']);
		}

		if (array_key_exists('referer', $this->_headers)) {
			curl_setopt($ch, CURLOPT_REFERER, $this->_headers['referer']);
		}

		if (array_key_exists('accept-encoding', $this->_headers)) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->_headers['accept-encoding']);
		}

		switch ($this->_method) {
			case self::METHOD_POST:
			case self::METHOD_PUT:
				curl_setopt($ch, CURLOPT_POST, true);
				if (!$this->_body) {
					$this->_body = http_build_query($this->_post_params, '', '&');
				}

				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_body);
				break;
		}

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->get_config('connect_timeout'));
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->get_config('timeout'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		/** fix: dnscache's problem with https:// */
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if ($this->_config['maxredirs']) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->_config['maxredirs']);
		}

		$result = curl_exec($ch);
		if ($result === false) {
			throw new http_request_exception(
				'Error sending request: #'.curl_errno($ch).' '.
				curl_error($ch).' at '."$url -I 'Host: {$this->_headers[host]}'"
			);
		}

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		/** 如果使用了http代理，则要去掉前两行 */
		if ($this->_config['http_proxy']) {
			$result = substr($result, strpos($result, "\r\n\r\n") + 4);
		}

		$info = curl_getinfo($ch);
		if ($info['redirect_count'] > 0) {
			for ($i = 0; $i < $info['redirect_count']; $i++) {
				$idx = strpos($result, "\r\n\r\n");
				$result = substr($result, $idx + 4);
			}
		}

		curl_close($ch);
		return new http_response($code, $result);
	}

	/**
	 * set_body
	 * 设置raw body的值
	 *
	 * @param string $body
	 * @return void
	 */
	public function set_body($body) {
		$this->_body = $body;
	}
}
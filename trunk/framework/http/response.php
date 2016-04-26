<?php
/**
 * http_response
 * $Author$
 * $Id$
 */

class http_response {
	/** 响应的 http 代码 */
    protected $_code;
    /** 头部参数 */
    protected $_headers = array();
    /** 响应的 cookie */
    protected $_cookies = array();
    /** 响应内容 */
    protected $_body;
    /** 最后一个头信息参数 */
    protected $_last_header;

    public function __construct($code, $resp) {
        $this->_code = $code;

        if ($idx = strpos($resp, "\r\n\r\n")) {
            if (substr($resp, 0, $idx) == 'HTTP/1.1 100 Continue') {
                $resp = substr($resp, $idx + 4);
                $idx = strpos($resp, "\r\n\r\n");
            }

            $this->_parse_headers(substr($resp, 0, $idx));
            $this->_body = substr($resp, $idx + 4);
        } else {
            $this->_parse_headers($resp);
        }
    }

    protected function _parse_headers($headers) {
        $header_lines = explode("\r\n", $headers);
        array_shift($header_lines);
        foreach ($header_lines as $header_line) {
            $this->_parse_header_line($header_line);
        }

        if (array_key_exists('set-cookie', $this->_headers)) {
            if (is_array($this->_headers['set-cookie'])) {
                $cookies = $this->_headers['set-cookie'];
            } else {
                $cookies = array($this->_headers['set-cookie']);
            }

            foreach ($cookies as $cookie_str) {
                $this->_parse_cookie($cookie_str);
            }

            unset($this->_headers['set-cookie']);
        }

        foreach (array_keys($this->_headers) as $k) {
            if (is_array($this->_headers[$k])) {
                $this->_headers[$k] = implode(', ', $this->_headers[$k]);
            }
        }
    }

    /**
     * Parses the line from HTTP response filling $headers array
     *
     * The method should be called after reading the line from socket or receiving-
     * it into cURL callback. Passing an empty string here indicates the end of
     * response headers and triggers additional processing, so be sure to pass an
     * empty string in the end.
     *
     * @param    string  Line from HTTP response
     */
    protected function _parse_header_line($header_line) {
        $header_line = trim($header_line, "\r\n");

        /** string of the form header-name: header value */
        if (preg_match('!^([^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+):(.+)$!', $header_line, $m)) {
            $name = strtolower($m[1]);
            $value = trim($m[2]);
            if (empty($this->_headers[$name])) {
                $this->_headers[$name] = $value;
            } else {
                if (!is_array($this->_headers[$name])) {
                    $this->_headers[$name] = array($this->_headers[$name]);
                }

                $this->_headers[$name][] = $value;
            }

            $this->_last_header = $name;

        } elseif (preg_match('!^\s+(.+)$!', $header_line, $m) && $this->_last_header) {
            if (!is_array($this->_headers[$this->_last_header])) {
                $this->_headers[$this->_last_header] .= ' '.trim($m[1]);
            } else {
                $key = count($this->_headers[$this->_last_header]) - 1;
                $this->_headers[$this->_last_header][$key] .= ' '.trim($m[1]);
            }
        }
    }

    /**
     * Parses a Set-Cookie header to fill $cookies array
     *
     * @param    string    value of Set-Cookie header
     * @link     http://cgi.netscape.com/newsref/std/cookie_spec.html
     */
    protected function _parse_cookie($cookie_str) {

        $cookie = array(
            'expires' => null,
            'domain' => null,
            'path' => null,
            'secure' => false
		);

        /** Only a name=value pair */
        if (!strpos($cookie_str, ';')) {
            $pos = strpos($cookie_str, '=');
            $cookie['name'] = trim(substr($cookie_str, 0, $pos));
            $cookie['value'] = trim(substr($cookie_str, $pos + 1));

            /** Some optional parameters are supplied */
        } else {
            $elements = explode(';', $cookie_str);
            $pos = strpos($elements[0], '=');
            $cookie['name'] = trim(substr($elements[0], 0, $pos));
            $cookie['value'] = trim(substr($elements[0], $pos + 1));

            for ($i = 1; $i < count($elements); $i++) {
                if (false === strpos($elements[$i], '=')) {
                    $el_name = trim($elements[$i]);
                    $el_value = null;
                } else {
                    list ($el_name, $el_value) = array_map('trim', explode('=', $elements[$i]));
                }

                $el_name = strtolower($el_name);
                if ('secure' == $el_name) {
                    $cookie['secure'] = true;
                } elseif ('expires' == $el_name) {
                    $cookie['expires'] = str_replace('"', '', $el_value);
                } elseif ('path' == $el_name || 'domain' == $el_name) {
                    $cookie[$el_name] = urldecode($el_value);
                } else {
                    $cookie[$el_name] = $el_value;
                }
            }
        }

        $this->_cookies[] = $cookie;
    }

    public function get_header($name) {
        $name = strtolower($name);
        if (array_key_exists($name, $this->_headers)) {
            return $this->_headers[$name];
        }
        return null;
    }

    public function get_version() {

    }

    public function get_headers() {
        return $this->_headers;
    }

    public function get_status() {
        return $this->_code;
    }

    public function get_cookies() {
        return $this->_cookies;
    }

    public function get_body() {
        return $this->_body;
    }
}

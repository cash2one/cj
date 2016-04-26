<?php
/**
 * controller_route_route
 * 匹配自定义路由
 *
 * $Author$
 * $Id$
 */

class controller_route_route extends controller_route_abstract {

	/**
	 * _regex_delimiter
	 * 正则表达式分界符
	 *
	 * @var string
	 */
	protected $_regex_delimiter = '#';

	/**
	 * _default_regex
	 * 默认正则
	 *
	 * @var mixed
	 */
	protected $_default_regex = null;

	/**
	 * 变量
	 *
	 * @var array
	 */
	protected $_variables = array();

	/**
	 * 需要匹配部分
	 *
	 * @var array
	 */
	protected $_parts = array();

	/**
	 * 默认路由
	 *
	 * @var array
	 */
	protected $_defaults = array();

	/**
	 * 变量对应的正则
	 *
	 * @var array
	 */
	protected $_requirements = array();

	/**
	 * 匹配完成的部分
	 *
	 * @var array
	 */
	protected $_values = array();

	/**
	 * 通配符匹配部分
	 *
	 * @var array
	 */
	protected $_wildcard_data = array();

	/**
	 * 非变量匹配次数统计
	 *
	 * @var integer
	 */
	protected $_static_count = 0;

	/**
	 * __construct
	 * 构造一个路由
	 *
	 * @param string $route 匹配表达式
	 * @param array $defaults 匹配默认值
	 * @param array $reqs 变量对应的正则
	 * @return void
	 */
	public function __construct($route, $defaults = array(), $reqs = array()) {

		$route = trim($route, $this->_url_delimiter);
		$this->_defaults = (array) $defaults;
		$this->_requirements = (array) $reqs;

		if ($route !== '') {
			foreach (explode($this->_url_delimiter, $route) as $pos => $part) {
				if (substr($part, 0, 1) == $this->_url_variable && substr($part, 1, 1) != $this->_url_variable) {

					/** 以:开头的 */
					$name = substr($part, 1);

					/** pattern放入_parts中 */
					if (isset($reqs[$name])) {
						$this->_parts[$pos] = $reqs[$name];
					} else {
						$this->_parts[$pos] = $this->_default_regex;
					}

					/** 变量放入_variables组中 */
					$this->_variables[$pos] = $name;
				} else {

					/** pattern都放在_parts中 */
					$this->_parts[$pos] = $part;

					if ($part !== '*') {
						$this->_static_count++;
					}
				}
			}
		}
	}

	/**
	 * match
	 * 验证匹配
	 *
	 * @param  string $path 真实请求过来的pathinfo
	 * @return array|boolean
	 */
	public function match($path) {

		$path_static_count = 0;
		$values = array();
		$matched_path = '';

		$path = trim($path, $this->_url_delimiter);

		if (!$path && !$this->_parts) {
			return $this->_defaults;
		}

		$path = explode($this->_url_delimiter, $path);

		foreach ($path as $pos => $path_part) {
			if (!array_key_exists($pos, $this->_parts)) {
				return false;
			}

			$matched_path .= $path_part.$this->_url_delimiter;

			/** 如果是通配符，则直接匹配所有剩余的部分，并退出 */
			if ($this->_parts[$pos] == '*') {
				$count = count($path);
				for($i = $pos; $i < $count; $i+=2) {
					$var = urldecode($path[$i]);
					if (!isset($this->_wildcard_data[$var]) && !isset($this->_defaults[$var]) && !isset($values[$var])) {
						if (isset($path[$i+1])) {
							$this->_wildcard_data[$var] = urldecode($path[$i+1]);
						} else {
							$this->_wildcard_data[$var] = null;
						}
					}
				}

				$matched_path = implode($this->_url_delimiter, $path);
				break;
			}

			if (isset($this->_variables[$pos])) {
				$name = $this->_variables[$pos];
			} else {
				$name = null;
			}

			$path_part = urldecode($path_part);

			$part = $this->_parts[$pos];

			/** 对于不是变量的，直接匹配即可 */
			if ($name === null && $part != $path_part) {
				return false;
			}

			/** 如果是变量，使用正则匹配 */
			if ($part !== null && !preg_match($this->_regex_delimiter.'^'.$part.'$'.$this->_regex_delimiter.'iu', $path_part)) {
				return false;
			}

			/** 将匹配上的变量放入$values数组 */
			if ($name !== null) {
				$values[$name] = $path_part;
			} else {
				/** 统计非变量个数 */
				$path_static_count++;
			}

		}

		/** 检查非变量匹配正确性 */
		if ($this->_static_count != $path_static_count) {
			return false;
		}

		$return = $values + $this->_wildcard_data + $this->_defaults;

		/** 检查一下，是不是所有的变量都匹配了 */
		foreach ($this->_variables as $var) {
			if (!array_key_exists($var, $return)) {
				return false;
			}
		}

		$this->_values = $values;

		return $return;
	}

	/**
	 * get_default
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_default($name) {

		if (isset($this->_defaults[$name])) {
			return $this->_defaults[$name];
		}
		return null;
	}

	/**
	 * get_defaults
	 *
	 * @return array
	 */
	public function get_defaults() {

		return $this->_defaults;
	}

}

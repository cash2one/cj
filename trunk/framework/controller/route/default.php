<?php
/**
 * controller_route_default
 * 匹配默认路由
 *
 * $Author$
 * $Id$
 */

class controller_route_default extends controller_route_abstract {

	/**
	 * _module_valid
	 * 是否存在module
	 *
	 * @var boolean
	 */
	protected $_module_valid = false;

	/**
	 * 默认路由
	 *
	 * @var array
	 */
	protected $_defaults = array();

	/**
	 * __construct
	 *
	 * @param  array $defaults
	 * @return void
	 */
	public function __construct($defaults = array()) {

		if (!$defaults['controller']) {
			$defaults['controller'] = 'index';
		}

		if (!$defaults['action']) {
			$defaults['action'] = 'index';
		}

		$this->_defaults = $defaults;
	}

	/**
	 * match
	 *
	 * @param  mixed $path
	 * @return void
	 */
	public function match($path) {

		$values = array();
		$params = array();

		$path = trim($path, $this->_url_delimiter);

		if ($path != '') {
			$path = explode($this->_url_delimiter, $path);

			/** 如果不是允许模块下的controller，直接跳转到默认controller */
			if ($this->_defaults['allow_modules']) {
				if (!in_array(strtolower($path[0]), array_map('strtolower', $this->_defaults['allow_modules']))) {
					return $this->_defaults;
				}
			}

			if ($this->_is_valid_module($path[0])) {
				$values[$this->_module_key] = array_shift($path);
				$this->_module_valid = true;
			}

			if (count($path) && !empty($path[0])) {
				$values[$this->_controller_key] = array_shift($path);
			}

			if (count($path) && !empty($path[0])) {
				$values[$this->_action_key] = array_shift($path);
			}

			/** 判断是否需要 handle */
			$startup = &startup::factory();
			$handle = $startup->get_option('handle');
			if ($handle && count($path) && !empty($path[0])) {
				$values[$this->_action_key] .= '_'.array_shift($path);
			}

			if (0 < ($num_segs = count($path))) {
				for ($i = 0; $i < $num_segs; $i = $i + 2) {
					$key = urldecode($path[$i]);
					if (isset($path[$i + 1])) {
						$val = urldecode($path[$i + 1]);
					} else {
						$val = null;
					}

					if (isset($params[$key])) {
						$params[$key] = array_merge((array) $params[$key], array($val));
					} else {
						$params[$key] = $val;
					}
				}
			}
		}

		$this->_values = $values + $params;

		return $this->_values + $this->_defaults;
	}

	/**
	 * _is_valid_module
	 * 检查是否为一个有效的模块
	 *
	 * @param  string $module
	 * @return void
	 */
	protected function _is_valid_module($module) {

		$module = $this->_camelize($module);

		$startup = &startup::factory();
		$childapp = $startup->get_option('childapp');

		//$path = APP_PATH.'/src/include/c/*';
		$path = APP_PATH.(empty($childapp) ? '/src/include/c/*' : '/src/*');
		$dirs = glob($path);
		foreach ($dirs as $dir) {
			if (is_dir($dir) && strtolower(basename($dir)) == strtolower($module)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * _camelize
	 * 转换为骆驼法
	 *
	 * @param  string $name
	 * @return void
	 */
	protected function _camelize($name) {

		$nameArr = explode('_', $name);
		$nameArr = array_map('ucwords', $nameArr);
		return join('', $nameArr);
	}

	/**
	 * get_default
	 *
	 * @param  string $name
	 * @return string
	 */
	public function get_default($name) {
		if (isset($this->_defaults[$name])) {
			return $this->_defaults[$name];
		}
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

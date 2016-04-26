<?php
/**
 * controller_route
 * 匹配路由
 *
 * $Author$
 * $Id$
 */

class controller_route {

	/**
	 * _rules
	 * 规则
	 *
	 * @var array
	 */
	protected $_rules = array();

	/**
	 * _routes
	 * 路由器对象集合
	 *
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * _use_default_routes
	 * 是否允许默认的路由规则，如果关闭，则会路由对自定义的规则
	 *
	 * @var boolean
	 */
	protected $_use_default_routes = true;

	/**
	 * _controller
	 *
	 * @var string
	 */
	protected $_controller = '';

	/**
	 * _action
	 *
	 * @var string
	 */
	protected $_action = '';

	/**
	 * _module
	 *
	 * @var string
	 */
	protected $_module = '';

	/**
	 * __construct
	 *
	 * @param  array $rules
	 * @return void
	 */
	public function __construct($rules = array()) {

		$this->_rules = $rules;

		if ($rules) {
			foreach ($rules as $rule) {
				if ($this->_is_valid_rule($rule)) {

					$path = array_shift($rule);
					$defaults = array_shift($rule);
					$reqs = array_shift($rule);

					array_push($this->_routes, new controller_route_route($path, $defaults, $reqs));
				}
			}
		}

		if ($this->_use_default_routes) {
			$this->add_default_routes();
		}
	}

	/**
	 * route
	 *
	 * @return void
	 */
	public function route() {

		$route_matched = false;
		$match = $this->_get_path_info();

		foreach ($this->_routes as $route) {

			$params = $route->match($match);
			if ($params) {
				$this->_set_request_params($params);
				$route_matched = true;
				break;
			}
		}

		if (!$route_matched) {
			throw new controller_route_exception('No route matched the request');
		}

		$this->_module = isset($params['module']) ? $params['module'] : '';
		$this->_controller = $params['controller'];
		$this->_action = $params['action'];

		startup_env::set('module', $this->_module);
		startup_env::set('controller', $this->_controller);
		startup_env::set('action', $this->_action);

		return $params;
	}

	/**
	 * get_module
	 *
	 * @return string
	 */
	public function get_module() {

		if (!$this->_controller) {
			$this->route();
		}

		return $this->_module;
	}

	/**
	 * get_controller
	 *
	 * @return string
	 */
	public function get_controller() {

		if (!$this->_controller) {
			$this->route();
		}

		return $this->_controller;
	}

	/**
	 * get_action
	 *
	 * @return string
	 */
	public function get_action() {

		if (!$this->_action) {
			$this->route();
		}

		return $this->_action;
	}

	/**
	 * _set_request_params
	 *
	 * @param  array $params
	 * @return void
	 */
	protected function _set_request_params($params) {

		$this->_params = $params;
	}


	/**
	 * get_request_params
	 * 获取路由参数的值
	 *
	 * 返回的数组包括：
	 * + key1
	 * + key2
	 * + ...
	 *
	 * @return array
	 */
	public function get_request_params() {

		if (!$this->get_controller()) {
			$this->route();
		}

		$params = $this->_params;
		if (array_key_exists('controller', $params)) {
			unset($params['module'], $params['controller'], $params['action'], $params['allow_modules']);
		}

		return $params;
	}

	/**
	 * _get_path_info
	 * 获取pathinfo
	 *
	 * @return string
	 */
	protected function _get_path_info() {

		if (empty($_SERVER['SCRIPT_URL'])) {
			if (FALSE === ($pos = stripos($_SERVER['REQUEST_URI'], '?'))) {
				return $_SERVER['REQUEST_URI'];
			}

			return substr($_SERVER['REQUEST_URI'], 0, $pos);
		}

		return $_SERVER['SCRIPT_URL'];
	}

	/**
	 * add_default_routes
	 * 添加默认路由器
	 *
	 * @return object
	 */
	public function add_default_routes() {

		$compat = new controller_route_default(isset($this->_rules['default']) ? $this->_rules['default'] : array());
		array_push($this->_routes, $compat);

		return $this;
	}

	/**
	 * remove_default_routes
	 * 删除默认路由
	 *
	 * @return void
	 */
	public function remove_default_routes() {

		$this->_use_default_routes = false;

		return $this;
	}

	/**
	 * _is_valid_rule
	 * 验证是否为一个有效的路由规则
	 *
	 * @param  array $rule
	 * @return void
	 */
	protected function _is_valid_rule($rule) {

		$path = array_shift($rule);
		if (!is_string($path)) {
			return false;
		}

		$defaults = array_shift($rule);
		if (!is_array($defaults) || !$defaults) {
			return false;
		}

		return true;
	}

	/**
	 * add_route
	 * 添加一个路由器
	 *
	 * @param string $name
	 * @param object $route
	 * @return object
	 */
	public function add_route($name, controller_route_route $route) {

		$this->_routes[$name] = $route;
		return $this;
	}

	/**
	 * add_routes
	 * 添加一个路由器
	 *
	 * @param  array $routes
	 * @return object
	 */
	public function add_routes($routes) {

		foreach ($routes as $name => $route) {
			$this->add_route($name, $route);
		}

		return $this;
	}

}

<?php
/**
 * controller_front
 *
 * $Author$
 * $Id$
 */

class controller_front {

	/**
	 *  _instance
	 *  当前类的实例
	 *
	 *  @var object
	 */
	protected static $_instance = null;

	/**
	 * _params
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * _method
	 * 当前方法
	 *
	 * @var string
	 */
	protected $_method;

	/**
	 * _route
	 * 路由
	 *
	 * @var object
	 */
	protected $_route;

	/**
	 * _request
	 *
	 * @var object
	 */
	protected $_request;

	/**
	 * _response
	 *
	 * @var object
	 */
	protected $_response;

	/**
	 * get_instance 获取一个实例
	 *
	 * @return void
	 */
	public static function get_instance() {

		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * __construct
	 * 构造方法
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_request = controller_request::get_instance();
		$this->_response = controller_response::get_instance();
	}

	/**
	 * __clone
	 * 防止克隆出对象
	 *
	 * @return void
	 */
	protected function __clone() {
	}

	/**
	 * handle_request 执行
	 *
	 * @return void
	 */
	public function handle_request() {

		/** route */
		$route = startup::factory()->get_option('route');
		if (!$route) {
			$route = startup_env::get('cfg_name').'.route';
		}

		if (!is_array($route)) {
			$rules = config::get($route.'.rules');
		} else {
			$rules = $route['rules'];
		}

		if (!$rules) {
			throw new controller_exception("Route rules can not be loaded in config : $route");
		}

		$this->_route = new controller_route($rules);

		$controller_name = $this->_route->get_controller();
		$action_name = $this->_route->get_action();
		$module_name = $this->_route->get_module();
		$params = $this->_route->get_request_params();

		$controller_class = $this->assemble_controller_class($controller_name, $action_name, $module_name);
		if (!class_exists($controller_class)) {
			throw new controller_exception("controller '".$controller_class."' is not found");
		}

		$this->set_method($action_name);

		/** leaving params */
		$this->_params = array_merge($this->_params, $params);
		$this->_request->set_params($this->_params);

		/** excute */
		$controller = new $controller_class();
		$controller->perform();

		/** profiler */
		$startup =& startup::factory();
		$option = $startup->get_option('profiler');
		if ($option) {
			$profiler = new profiler($option);
			$output = $profiler->run();
			$this->_response->append_body($output, 'profiler');
		}

		/** response */
		$this->_response->send_response();
	}

	/**
	 * getParams
	 *
	 * @return array
	 */
	public function get_params() {

		return $this->_params;
	}

	/**
	 * setMethod
	 * 设置方法
	 *
	 * @param  string $method
	 * @return void
	 */
	public function set_method($method) {

		$this->_method = $method;
	}

	/**
	 * getMethod
	 * 获得方法
	 *
	 * @return string
	 */
	public function get_method() {
		return $this->_method;
	}

	/**
	 * getRoute
	 * 获得route对象
	 *
	 * @return object
	 */
	public function get_route() {

		return $this->_route;
	}

	/**
	 * assemble_controller_class
	 * 获得类名
	 *
	 * @param  string $controller
	 * @param  string $module
	 * @return string
	 */
	public function assemble_controller_class($controller, $action, $module = null) {

		/** 允许以"module/controller"形式传入 */
		if (strpos($controller, '/') !== false) {
			list($module, $controller) = explode('/', $controller);
		}

		$app_name = basename(APP_PATH);

		$startup = &startup::factory();
		$childapp = $startup->get_option('childapp');

		if (!empty($childapp)) {
			$controller_class = $app_name.'_'.$this->_camelize($module).'_c_'.$this->_camelize($controller);
		} elseif ($module) {
			$controller_class = $app_name.'_c_'.$this->_camelize($module).'_'.$this->_camelize($controller);
		} else {
			$controller_class = $app_name.'_c_'.$this->_camelize($controller);
		}

		if ($action) {
			$controller_class .= '_'.$action;
		}

		return $controller_class;
	}

	/**
	 * getRequest
	 *
	 * @return void
	 */
	public function get_request() {

		return $this->_request;
	}

	/**
	 * getResponse
	 *
	 * @return void
	 */
	public function get_response() {

		return $this->_response;
	}

	/**
	 * _camelize
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function _camelize($name) {

		$name_arr = explode('_', $name);
		return join('', $name_arr);
	}

}

<?php
/**
 * controller
 *
 * $Author$
 * $Id$
 */

class controller {

	/**
	 * view
	 *
	 * @var object
	 */
	protected $view;

	/**
	 * request
	 *
	 * @var object
	 */
	protected $request;

	/**
	 * response
	 *
	 * @var object
	 */
	protected $response;

	/**
	 * session
	 * session对象
	 *
	 * @var object
	 */
	protected $session;

	/**
	 * route
	 * 路由对象
	 *
	 * @var object
	 */
	protected $route;

	/**
	 * controller_name
	 * 当前的 conroller 名称
	 *
	 * @var string
	 */
	protected $controller_name;

	/**
	 * action_name
	 * 当前 action 名称
	 *
	 * @var string
	 */
	protected $action_name;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		$front = controller_front::get_instance();

		$this->request = $front->get_request();
		$this->response = $front->get_response();
		$this->route = $front->get_route();

		$this->view = new view();
		/** 语言默认配置 */
		language::set_lang(config::get(startup_env::get('cfg_name').'.language'));
		language::load_lang('core');

		$startup =& startup::factory();
		if ($startup->get_option('session')) {
			$this->session = $this->_start_session();
		}
	}

	/**
	 * _start_session
	 * 启动session支持
	 *
	 * @return object
	 */
	protected function _start_session() {

		$startup =& startup::factory();
		$session = $startup->get_option('session');

		if (is_string($session)) {
			$prefix = $session;
		} else if ($session) {
			$app_name = startup_env::get('app_name');
			$prefix = $app_name.'.session';
		} else {
			return false;
		}

		$domain = config::get($prefix.'.domain');
		$expired = config::get($prefix.'.expired');
		$public_key = config::get($prefix.'.public_key');

		$session =& session::get_instance($domain, $expired, $public_key);
		ob_start(array($session, 'send'));

		return $session;
	}

	/**
	 * perform
	 *
	 * @return void
	 */
	public function perform() {

		$front = controller_front::get_instance();

		$action_name = $front->get_method();
		$this->action_name = $action_name;
		$this->controller_name = get_class($this);
		$this->controller_name = substr($this->controller_name, 0, strrpos($this->controller_name, '_'));

		if (method_exists($this, 'execute')) {
			if ($this->_before_action($action_name)) {
				$this->execute();
			}

			$this->_after_action($action_name);
		} else {
			$this->_missing_method($action_name);
		}

		return true;
	}

	/**
	 * _before_action
	 * 在所有Action之前执行
	 * 此方法必须返回true，否则其他action不会执行
	 *
	 * @param  string $action
	 * @return boolean
	 */
	protected function _before_action($action) {

		return true;
	}

	/**
	 * _after_action
	 * 在所有Action之后执行
	 *
	 * @param  string $action
	 * @return boolean
	 */
	protected function _after_action($action) {

		return true;
	}

	/**
	 * _missing_method
	 * 默认执行的方法
	 *
	 * @param  string $action
	 * @return void
	 */
	protected function _missing_method($action) {

		throw new controller_exception("Missing method");
	}

	/**
	 * index_action
	 *
	 * @return void
	 */
	public function index_action() {

		echo "Hello World!";
	}

	/**
	 * redirect
	 *
	 * @param  stirng $controller
	 * @param  string $action
	 * @param  array  $params
	 * @return void
	 */
	public function redirect($controller, $action = null, $params = array()) {

		if ($action == null) {
			$url = $controller;
		} else {
			$params = http_build_query($params);
			$url = '/'.$controller.'/'.$action;
			if ($params) {
				$url .= '?'.$params;
			}
		}

		return $this->response->set_redirect($url);
	}

	/**
	 * forward
	 *
	 * @param string $controller
	 * @param string $action
	 * @param array  $params
	 * @return void
	 */
	public function forward($controller_name, $action, $params = array()) {

		if ($params && is_array($params)) {
			$_GET = $_GET + $params;
		}

		$front = controller_front::get_instance();
		$route = $front->get_route();
		$controller_class = $front->assemble_controller_class($controller_name, $action, $route->get_module());

		if (!class_exists($controller_class)) {
			throw new controller_exception("controller '".$controller_class."' is not found");
		}

		$controller = new $controller_class;
		if (method_exists($controller, 'execute')) {
			if ($controller->_before_action($action)) {
				$controller->execute();
			}

			$controller->_after_action($action);
		} else {
			$controller->_missing_method($action);
		}

		return false;
	}

}

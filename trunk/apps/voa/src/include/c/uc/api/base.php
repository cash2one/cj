<?php
/**
 * voa_c_uc_api_base
 * uc的api接口基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_base extends voa_c_uc_base {

	/**
	 * 输出的错误代码
	 * @var number
	 */
	public $errcode = 0;

	/**
	 * 输出的错误消息
	 * @var string
	 */
	public $errmsg = 'OK';

	/**
	 * 输出的结果集
	 * @var array
	 */
	public $result = array();

	/**
	 * 当前执行的模块名
	 * @var string
	*/
	protected $_uc_module = '';

	/**
	 * 当前 HTTP 请求的方法 GET|POST|PUT|DELETE
	 * @var string
	 */
	protected $_uc_method = '';

	/**
	 * 当前请求的动作
	 * @var string
	 */
	protected $_uc_action = '';

	/**
	 * 当前动作请求的参数（严格按照当前动作的请求方法来获取）
	 * @var array
	 */
	protected $_params = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 初始化接口
		$this->_uc_init();

		// 获取动作参数
		$this->_get_params();

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);

		// 输出结果
		$this->_output();
		return true;
	}

	/**
	 * 初始化接口
	 * @return void
	 */
	protected function _uc_init() {

		// 当前执行的模块名
		$this->_uc_module = $this->route->_params['controller'];

		// 当前http请求的方法 和 请求的动作
		// $this->action_name 后加“_”是为了避免$this->action_name意外没“_”的情况
		list($this->_uc_method, $this->_uc_action) = explode('_', $this->action_name.'_');
	}

	/**
	 * 根据路由方法名来确定提交的请求方式，并获取相应提交的参数和数据
	 * @return void
	 */
	protected function _get_params() {

		switch (rstrtolower($this->_uc_method)) {
			case 'get':
				$params = array();
				foreach ($_GET as $key => $value) {
					$params[$key] = $value;
				}
				$this->_params = $params;
				unset($params);
				break;
			case 'post':
				$params = array();
				foreach ($_POST as $key => $value) {
					$params[$key] = $value;
				}
				$this->_params = $params;
				unset($params);
				break;
			case 'put':
			case 'delete':
				$params = array();
				$input = file_get_contents('php://input');
				parse_str($input, $params);
				$this->_params = $params;
				unset($params, $input);
				break;
			default:
				$this->_set_errcode(voa_errcode_api_system::API_UNKNOWN);
				$this->_output();
				break;
		}

		return;
	}

	/**
	 * 赋值 错误代码 和 错误消息
	 * @param string $str errcode::CY_OK
	 * @param mixed $params1 ... 变量值
	 * @uses _set_errcode(errcode:CY_TEST, 'aa', 'bb', 'cc');
	 * @return void
	 */
	protected function _set_errcode($str) {

		call_user_func_array("voa_h_func::set_errmsg", func_get_args());

		$this->errcode = voa_h_func::$errcode;
		$this->errmsg = voa_h_func::$errmsg;

		return $this->errcode ? false : true;
	}

	/**
	 * 输出结果
	 * @param number $errcode
	 * @param string $errmsg
	 * @param array $result
	 */
	protected function _output($errcode = 0, $errmsg = '', $result = array()) {

		// 输出 json 类型
		$this->response->set_header('Content-type', 'application/json;charset=utf-8');

		// 输出结果
		$result = array(
			'errcode' => $this->errcode,
			'errmsg' => $this->errmsg,
			'result' => $this->result
		);

		$this->response->append_body(rjson_encode($result));
		return $this->response->stop();
	}

	/**
	 * 基本变量检查和过滤方法
	 * @param array $fields = array(
	 * 	'变量名' => array(
	 * 		'type' => '',// 变量值类型
	 * 		'required' => boolean,// 是否为必须参数
	 * 	)
	 * 	... ...
	 * )
	 * 或者
	 * array(
	 * 	'变量名' => 变量值类型,
	 *	... ...
	 * )
	 * @return boolean
	 */
	protected function _check_params($fields) {
		foreach ($fields as $key => $rule) {
			if (!is_array($rule)) {
				$rule['type'] = $rule;
			}
			if (!isset($this->_params[$key])) {
				if (!isset($rule['required']) || $rule['required']) {
					$this->_set_errcode(voa_errcode_uc_system::UC_PARAM_LOSE, $key);
					$this->_output();
					break;
				} else {
					$this->_params[$key] = '';
				}
			}
			switch (rstrtolower($rule['type'])) {
				case 'int':
					$this->_params[$key] = rintval($this->_params[$key], false);
					break;
				case 'number':
					if (!is_numeric($this->_params[$key])) {
						$this->_params[$key] = 0;
					}
					break;
				case 'array':
					$this->_params[$key] = (array)$this->_params[$key];
					break;
				case 'string_trim':
					$this->_params[$key] = trim((string)$this->_params[$key]);
					break;
				default:
					$this->_params[$key] = (string)$this->_params[$key];
					break;
			}
		}

		return true;
	}

}

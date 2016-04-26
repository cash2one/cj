<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Api;

use Think\Controller\RestController;
use Com\Cookie;

abstract class AbstractController extends RestController {

	// 是否 A/R 方法调用
	protected $_is_a_r = false;
	// 返回结果
	protected $_result = array();

	// 前置操作
	public function before_action($action = '') {

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		$this->_response();
		return true;
	}

	// 生成 formhash
	protected function _generate_formhash() {

		return '';
	}

	/**
	 * 设置错误信息
	 * @param mixed $message 错误信息
	 * @param int $code 错误号
	 */
	protected function _set_error($message, $code = 0) {

		\Com\Error::instance()->set_error($message, $code);
		return true;
	}

	/**
	 * 重写输出方法
	 * @param mixed $data 输出数据
	 * @param string $type 输出类型
	 * @param int $code 返回状态
	 * @see \Think\Controller\RestController::_response()
	 */
	protected function _response($data = null, $type = 'json', $code = 200) {

		// 如果是 A/R 方法调用, 则不输出.
		if ($this->_is_a_r) {
			return true;
		}

		// 如果需要返回的是异常
		if ($data instanceof \Think\Exception) {
			// 如果是显示给用户的错误
			if ($data->is_show() || APP_DEBUG) {
				\Com\Error::instance()->set_error($data);
			} else { // 如果是系统错误, 则显示默认错误
				$this->_set_error('_ERR_DEFAULT');
			}

			$data = null;
		} elseif ($data instanceof \Exception) { // 系统报错
			if (APP_DEBUG) { // 如果是 debug 状态
				\Com\Error::instance()->set_error($data);
			} else {
				$this->_set_error('_ERR_DEFAULT');
			}

			$data = null;
		}

		// 输出结果
		$result = array(
			'errcode' => \Com\Error::instance()->get_errcode(),
			'errmsg' => \Com\Error::instance()->get_errmsg(),
			'timestamp' => NOW_TIME,
			'result' => null == $data ? $this->_result : $data
		);
		parent::_response($result, $type, $code);
	}

	// 检查参数
	protected function _check_signature($action = '') {

		return true;
	}

	/**
	 * 魔术方法 有不存在的操作的时候执行(重写)
	 *
	 * @access public
	 * @param string $method 方法名
	 * @param array $args 参数
	 * @return mixed
	 */
	public function __call($method, $args) {

		try {
			if (0 === strcasecmp($method, ACTION_NAME . C('ACTION_SUFFIX'))) {
				if (method_exists($this, $method . '_' . $this->_method . '_' . $this->_type)) { // RESTFul方法支持
					$fun = $method . '_' . $this->_method . '_' . $this->_type;
					if (!$this->_check_signature($method)) {
						E('_ERR_SIGNATURE_ERROR');
						return false;
					}

					$this->$fun();
				} elseif (method_exists($this, $method . '_' . $this->_method)) {
					$fun = $method . '_' . $this->_method;
					if (!$this->_check_signature($method)) {
						E('_ERR_SIGNATURE_ERROR');
						return false;
					}

					$this->$fun();
				} elseif (method_exists($this, '_empty')) {
					if ($this->_build_action()) {
						// 报生成成功信息
						E(__CLASS__ . ':' . $method . '_' . $this->_method . L('METHOD_CREATED'));
					}

					// 如果定义了_empty操作 则调用
					$this->_empty($method, $args);
				} else {
					E(L('_ERROR_ACTION_') . ':' . ACTION_NAME);
				}
			}
		} catch (\Think\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage());
			// 返回错误
			$this->_response($e);
		} catch (\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage());
			// 返回错误
			$this->_response($e);
		}
	}

	// 空方法, 在未找到处理方法时调用
	protected function _empty() {

		E('_ERROR_ACTION_');
		return true;
	}

}

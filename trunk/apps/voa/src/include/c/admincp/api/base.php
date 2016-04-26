<?php
/**
 * base.php
 * 后台API接口基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_api_base extends voa_c_admincp_base {

	protected $_result = array();

	protected function _before_action($action) {

		// 定义当前模式为API
		$this->_is_api = true;

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

	/**
	 * 输出api结果集
	 * @param array $result 结果集
	 * @param string $url 链接
	 */
	protected function _output_result($result, $url = '') {
		return $this->_admincp_success_message('OK', $url, $result);
	}

}

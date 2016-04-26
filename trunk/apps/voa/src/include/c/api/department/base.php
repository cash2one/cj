<?php
/**
 * 部门api基类
 * voa_c_api_department_base
 */

class voa_c_api_department_base extends voa_c_api_base {


	/**
	 * 部门集合
	 * @var null
	 */
	protected $_departments = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		if (empty($this->_departments)) {
			$this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
		}
		return true;
	}

	protected function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}

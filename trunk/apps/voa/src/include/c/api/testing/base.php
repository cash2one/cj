<?php
/**
 * voa_c_api_testing_base.php
 * 接口/$测试$/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_testing_base extends voa_c_api_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

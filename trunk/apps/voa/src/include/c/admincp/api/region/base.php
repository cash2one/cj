<?php
/**
 * base.php
 * 后台api/区域相关接口基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_api_region_base extends voa_c_admincp_api_base {

	protected function _before_action($action) {

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

}

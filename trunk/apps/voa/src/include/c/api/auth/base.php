<?php
/**
 * voa_c_api_auth_base
 * 接口/认证/基本控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_auth_base extends voa_c_api_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

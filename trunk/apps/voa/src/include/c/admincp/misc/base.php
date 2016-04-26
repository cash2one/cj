<?php
/**
 * voa_c_admincp_misc_base
 * 企业后台/公共/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_misc_base extends voa_c_admincp_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

}

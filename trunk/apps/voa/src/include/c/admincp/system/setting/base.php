<?php
/**
 * voa_c_admincp_system_setting_base
 * 企业后台 - 系统设置 - 全局系统环境设置 - 基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_system_setting_base extends voa_c_admincp_setting_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

}

<?php
/**
 * voa_c_admincp_setting_servicetype_base
 * 企业后台 - 系统设置 - 服务类型设置 - 基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_servicetype_base extends voa_c_admincp_setting_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

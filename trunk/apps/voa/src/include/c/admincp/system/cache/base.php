<?php
/**
 * voa_c_admincp_system_cache_base
 * 企业后台/系统设置/缓存更新/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_cache_base extends voa_c_admincp_system_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		return false;
	}

}

<?php
/**
 * voa_c_admincp_manage_department_base
 * 企业后台/企业管理/部门管理/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_department_base extends voa_c_admincp_manage_base {

	/** 部门表实例 */
	protected $_serv = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->serv = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
		return true;
	}

}

<?php
/**
 * base.php
 * 后台/系统/系统状态/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */  
class voa_c_admincp_system_status_base extends voa_c_admincp_system_base {

	protected $_qystates = array(
		1 => '已付费',
		2 => '已付费-即将到期',
		3 => '已付费-已到期',
		5 => '试用期-即将到期',
		6 => '试用期-已到期',
		7 => '试用期'
	); 

	protected function _before_action($action) {
		if (! parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {
		if (! parent::_after_action($action)) {
			return false;
		}

		return true;
	}
}

<?php
/**
 * voa_c_admincp_office_minutes_base
 * 企业后台/微办公管理/会议记录/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_minutes_base extends voa_c_admincp_office_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

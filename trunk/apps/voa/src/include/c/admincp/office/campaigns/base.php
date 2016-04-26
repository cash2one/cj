<?php
/**
 * voa_c_admincp_office_showroom_base
 * 企业后台/微办公管理/活动推广/基本控制器
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_campaigns_base extends voa_c_admincp_office_base {
	protected $_p_sets = array();
	protected function _before_action($action) {
		if (! parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.campaigns.setting', 'oa');
		return true;
	}

	protected function _after_action($action) {

		if (! parent::_after_action($action)) {
			return false;
		}

		return true;
	}
}

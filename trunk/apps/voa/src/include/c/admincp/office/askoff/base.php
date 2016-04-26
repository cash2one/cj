<?php
/**
 * voa_c_admincp_office_askoff_base
 * 企业后台/微办公管理/请假/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askoff_base extends voa_c_admincp_office_base {

	protected $_p_sets = array();
	protected $_uda_base = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_uda_base = &uda::factory('voa_uda_frontend_askoff_base');
		//FIXME ！！！涉及指定应用更新问题
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.askoff.setting', 'oa');

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

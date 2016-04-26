<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_event_base extends voa_c_admincp_api_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.event.setting', 'oa');
		return true;
	}

	protected function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}

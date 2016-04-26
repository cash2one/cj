<?php
/**
 * voa_c_admincp_tool_secret_base
 * 企业后台/应用宝/秘密/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_base extends voa_c_admincp_tool_base {

	protected $_sets = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_sets = voa_h_cache::get_instance()->get('plugin.askoff.setting', 'oa');
		$this->view->set('module_plugin_set', $this->_sets);
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}

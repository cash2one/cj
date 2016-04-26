<?php
/**
 * base.php
 * 红包api基类
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_base extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		startup_env::set('agentid', $this->_p_sets['agentid']);
		return true;
	}

	protected function _after_action($action) {

		if (! parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}

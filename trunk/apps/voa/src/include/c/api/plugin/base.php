<?php
/**
 * 插件操作相关
 * $Author$
 * $Id$
 */

class voa_c_api_plugin_base extends voa_c_api_base {
	protected $_plugins;
	protected $_set = array();

	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$this->_set = voa_h_cache::get_instance()->get('setting', 'oa');
		return true;
	}
}

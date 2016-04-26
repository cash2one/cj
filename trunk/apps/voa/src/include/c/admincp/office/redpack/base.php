<?php
/**
 * voa_c_admincp_office_redpack_base
 * 红包基类
 * Date: 15/3/9
 * Time: 上午10:43
 */

class voa_c_admincp_office_redpack_base extends voa_c_admincp_office_base {
	// 页码
	protected $_page = 1;
	// 红包配置
	protected $_redpack_settings;
	// 当前 URL
	protected $_self_url = '';

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}

		$this->_self_url = $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id);
		if (empty($this->_redpack_settings)) {
			$this->_redpack_settings = voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa');
		}

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

}

<?php
/**
 * voa_c_api_sale_base
 * 销售管理基础控制器
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_base extends voa_c_api_base {

	/** 插件id */
	protected $_pluginid = 0;


	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}
		
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.sale.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

		return true;
	}


	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}



}

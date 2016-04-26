<?php
/**
 * voa_c_api_common_post_upplugin
 * Create By ppker
 * $Author$
 * $Id$
 */

class voa_c_api_common_post_updateplugin extends voa_c_api_common_abstract {

	protected function _before_action($action) {
		$this->_require_login = false;
		if (! parent::_before_action($action)) {
			return false;
		}
		return true;
	}
	
	public function execute() {
		// 需要的参数 cp_pluginid m_uid
		$fields = array(
			'cp_pluginid' => array('type' => 'int', 'required' => true)
			// 'm_uid' => array('type' => 'int', 'required' => true)
		);
		// 测试数据
		//$this->_params['cp_pluginid'] = 17;

		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		// 获取参数
		$conds = array(
			'cp_pluginid' => $this->_params['cp_pluginid'],
			'm_uid' => startup_env::get('wbs_uid')
		);
		// 更新的数据
		$up_data = array(
			'cpd_lastusetime' => startup_env::get('timestamp')
		);
		$reslut = array();
		$uda_display = &uda::factory('voa_uda_frontend_common_plugin_display');
		$uda_display->do_update($conds, $up_data, $reslut);
		$this->_result = $reslut;
		return true;
		
	}

}

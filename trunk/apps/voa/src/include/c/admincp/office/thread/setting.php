<?php

/**
 * voa_c_admincp_setting_thread_modify
 * 企业后台/系统设置/微社区/设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_thread_setting extends voa_c_admincp_office_thread_base
{

	public function execute()
	{
		$p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa'); // 读同事社区配置缓存
		if ($this->_is_post()) {
			$setting = array();
			$post = $this->request->getx();
			$post['offical_img'] = $this->request->get('at_id');
			$uda_add = &uda::factory('voa_uda_frontend_thread_setting_update');
			if (! $uda_add->execute($post, $setting)) {
				$this->_error_message($uda_add->errmsg);
				return false;
			}
			/**
			 * 强制更新
			 */
			if ($this->_module_plugin_id) {
				voa_h_cache::get_instance()->get('plugin.' . $this->_module_plugin['cp_identifier'] . '.setting', 'oa', true);
			} else {
				voa_h_cache::get_instance()->get('setting', 'oa', true);
			}
			$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '社区设置更新完毕', null, null, false, $this->cpurl($this->_module, $this->_operation, 'setting', $this->_module_plugin_id));
		}
		$this->view->set('p_sets', $p_sets);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/thread/setting');
	}
}

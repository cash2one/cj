<?php

/**
 * voa_c_admincp_setting_express_setting
 * 企业后台/系统设置/快递助手/设置
 * Create By Gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_express_setting extends voa_c_admincp_office_express_base
{

	public function execute()
	{
		// 读快递助手配置缓存
		$p_sets = voa_h_cache::get_instance()->get('plugin.express.setting', 'oa');

		//获取form表单提交参数
		if ($this->_is_post()) {
			$setting = array();
			$post = array();
			$post['m_uids'] = implode(',', (array)$this->request->post('m_uids'));//用户id
			$post['cd_ids'] = implode(',', (array)$this->request->post('cd_ids'));//部门id

			//更新快递助手设置表
			$uda_update = &uda::factory('voa_uda_frontend_express_setting_update');
			if (!$uda_update->execute($post, $setting)) {
				$this->_error_message($uda_update->errmsg);
				return false;
			}

			//强制更新
			if ($this->_module_plugin_id) {
				voa_h_cache::get_instance()->get('plugin.' . $this->_module_plugin['cp_identifier'] . '.setting', 'oa', true);
			} else {
				voa_h_cache::get_instance()->get('setting', 'oa', true);
			}
			$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '快递助手设置更新完毕', null, null, false, $this->cpurl($this->_module, $this->_operation, 'setting', $this->_module_plugin_id));
		}

		$default = array();
		//设置快递接收人有用户时，获取用户姓名
		if(!empty($p_sets['m_uids'])) {
			$p_sets['m_uids'] = explode(',', $p_sets['m_uids']);//用户id
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($p_sets['m_uids']);
			foreach ($p_sets['m_uids'] as $_k => $_v) {
				//获取人员
				if ($_v != 0) {
					$default[] = array(
						'm_uid' => $_v,
						'm_username' => $users[$_v]['m_username'],
						'selected' => (bool)true,
					);
				}
			}
		}

		//设置快递接收人有用户时，获取用户所在部门
		$default_dp = array();
		if(!empty($p_sets['cd_ids'])) {
			$p_sets['cd_ids'] = explode(',', $p_sets['cd_ids']);//用户id
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key($p_sets['cd_ids']);
			foreach ($p_sets['cd_ids'] as $_k => $_v) {
				//获取部门
				if ($_v != 0) {
					$default_dp[] = array(
						'id' => $_v,
						'name' => $depms[$_v]['cd_name'],
						'isChecked' => (bool)true,
					);
				}
			}
		}


		$this->view->set('default_data_uids',json_encode(array_values($default)));
		$this->view->set('default_data_cdids',json_encode(array_values($default_dp)));
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/express/setting');
	}
}

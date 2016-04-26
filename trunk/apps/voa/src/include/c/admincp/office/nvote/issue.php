<?php

/**
 * voa_c_admincp_office_nvote_issue
 * 企业后台/微办公管理/投票权限/用户H5发布权限
 * 迭代开发者：muzhitao
 * Date：2015-10-22
 * Email：muzhitao@vchangyi.com
 */
class voa_c_admincp_office_nvote_issue extends voa_c_admincp_office_nvote_base {

	public function execute() {

		if ($this->_is_post()) {
			try {
				$uda = &uda::factory('voa_uda_frontend_nvote_issue');

				$uda->add($_POST);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}

			$this->message('success', '前台发布权限成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		}

		$p_setting = voa_h_cache::get_instance()->get('plugin.nvote.setting', 'oa');
		$all = isset($p_setting['all']) ? (int)$p_setting['all'] : 0; //all发布权限   0=>非全部 1=>全部

		// 部门列表
		$data_cd_ids = array();
		// 成员列表
		$data_m_uids = array();

		if (isset($p_setting['cd_ids']) || isset($p_setting['m_uids'])) {

			// 判断是否存在部门列表 如果存在 清除空数组
			if (!empty($p_setting['cd_ids'])) {
				$p_setting['cd_ids'] = array_filter($p_setting['cd_ids']);
			}

			// 判断是否存在成员列表 如果存在 清除空数组
			if (!empty($p_setting['m_uids'])) {
				$p_setting['m_uids'] = array_filter($p_setting['m_uids']);
			}

			$data_cd_ids = array();
			$data_m_uids = array();
			if ($p_setting['cd_ids']) {
				$cd_ids = $p_setting['cd_ids'];
				$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
				$depms = $serv_d->fetch_all_by_key($cd_ids);
				foreach ($p_setting['cd_ids'] as $right) {
					$data_cd_ids[] = array(
						'id' => $right,
						'cd_name' => isset($depms[$right]['cd_name']) ? $depms[$right]['cd_name'] : '',
						'isChecked' => (bool)true,
					);
				}
			}
			if ($p_setting['m_uids']) {
				$m_uids = $p_setting['m_uids'];
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				foreach ($p_setting['m_uids'] as $right) {
					$data_m_uids[] = array(
						'm_uid' => $right,
						'm_username' => isset($users[$right]['m_username']) ? $users[$right]['m_username'] : '',
						'selected' => (bool)true,
					);
				}
			}
		}

		$this->view->set('url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		// 成员数据列表
		$this->view->set('default_member', rjson_encode(array_values($data_m_uids)));
		// 部门数据列表
		$this->view->set('default_department', rjson_encode(array_values($data_cd_ids)));
		// 当前是否全部可操作
		$this->view->set('default_all', $all);

		// 输出模版
		$this->output('office/nvote/issue');
	}
}

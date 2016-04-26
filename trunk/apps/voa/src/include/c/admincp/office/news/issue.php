<?php

/**
 * voa_c_admincp_office_news_issue
 * 企业后台/微办公管理/新闻公告/用户H5发布权限
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */
class voa_c_admincp_office_news_issue extends voa_c_admincp_office_news_base {
	public function execute() {
		// 输出模板
		if ($this->_is_post()) {
			$result = '';
			try {
				$uda = &uda::factory('voa_uda_frontend_news_issue');
				$uda->add($_POST);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
			//die;
			$this->message('success', '前台发布权限成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		}

		$p_setting = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa');
		$data_cd_ids = array();
		$data_m_uids = array();
		if (!empty($p_setting['cd_ids'])) {
			$p_setting['cd_ids'] = array_filter($p_setting['cd_ids']);
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
		}
		if (!empty($p_setting['m_uids'])) {
			$p_setting['m_uids'] = array_filter($p_setting['m_uids']);
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
		$this->view->set('cd_ids', json_encode(array_values($data_cd_ids)));
		$this->view->set('m_uids', json_encode(array_values($data_m_uids)));
		$this->output('office/news/issue');
	}
}
